<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\RS512;

class RefreshController extends Controller
{
    protected Response $response;
    protected Client $client;
    protected GenerateToken $generateToken;
    protected $currentTime;

    public function __construct(Client $client,
                                GenerateToken $generateToken,
                                Response $response)
    {
        $this->client           = $client;
        $this->generateToken    = $generateToken;
        $this->response         = $response;
        $this->currentTime      = Carbon::now();
    }

    /**
     * digunakan untuk merefresh token dengan algoritma RS512
     *
     * @param Request $request request yang berisi token dalam header authorization
     * @return Response|JsonResponse jika $token tidak ditemukan akan memberikan response message Unauthorized
     *                   jika token sudah expired maka akan memberikan response message Token sudah tidak berlaku
     *                   jika berhasil refresh token akan terbuat dan waktu expired akan bertambah 5 menit
     */
    public function refreshToken(Request $request)
    {
        $rawDataRequest = json_decode($request->getContent());

        // Validate raw body
        if (empty($rawDataRequest)) return $this->response->badRequestResponse(array('Request Body Kosong'));
        if (!isset($rawDataRequest->access_token) || is_null($rawDataRequest->access_token))
            return $this->response->badRequestResponse(array('Access Token Kosong'));
        if (!isset($rawDataRequest->refresh_token) || is_null($rawDataRequest->refresh_token))
            return $this->response->badRequestResponse(array('Refresh Token Kosong'));

        $accessToken = $rawDataRequest->access_token;
        $refreshToken = $rawDataRequest->refresh_token;

        // Validate Access Token and Refresh Token
        $resultValidateAccessToken = $this->validateAccessToken($accessToken);
        $resultValidateRefreshToken = $this->validateRefreshToken($refreshToken);

        if (!$resultValidateAccessToken->status)
            return $this->response->badRequestResponse($resultValidateAccessToken);
        if (!$resultValidateRefreshToken->status)
            return $this->response->badRequestResponse($resultValidateRefreshToken->data);

        $accessModule = array();
        foreach($this->client->detail_modules as $item){
            $accessModule[] = $item->module->module . '.' . $item->access->desc;
        }

        $accessToken = $this->generateToken->generateAccessToken($resultValidateRefreshToken, $accessModule);
        $dataToken = (object)array(
            'type'          => 'bearer',
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expired_in'    => $this->currentTime->addMinutes((int)config('api.access_token_duration'))->format('Y-m-d H:i:s'),
        );

        return $this->response->grantedTokenResponse($dataToken);
    }

    /**
     * Digunakan untuk melakukan validasi access_token
     *
     * @param $accessToken
     * @return object
     */
    private function validateAccessToken($accessToken)
    {
        $algorithmManager = new AlgorithmManager([
            new RS512()
        ]);

        // Verify signature
        $keyDecryption = JWKFactory::createFromKeyFile(
            env('JWT_PRIVATE_KEY')
        );

        $serializer = new CompactSerializer();
        try
        {
            $jsonWebSerializer = $serializer->unserialize($accessToken);
        }
        catch (\Exception $exception)
        {
            return $this->response->badRequestResponse('Access token is invalid');
        }

        $jwsVerifier = new JWSVerifier($algorithmManager);
        try
        {
            if(!$jwsVerifier->verifyWithKey($jsonWebSerializer, $keyDecryption,0))
                return (object)array(
                    'status'    => true,
                    'data'      => 'Token invalid'
                );
        }
        catch (\Exception $e)
        {
            return (object)array(
                'status'    => false,
                'data'      => $e->getMessage()
            );
        }

        return (object)array(
            'status'    => true,
            'data'      => 'Ok!'
        );
    }

    /**
     * Digunakan untuk melakukan validasi refresh_token
     *
     * @param $refreshToken
     * @return object
     */
    private function validateRefreshToken($refreshToken)
    {
        $algorithmManager = new AlgorithmManager([
            new HS256()
        ]);

        $serializer = new CompactSerializer();
        try
        {
            $jsonWebSerializer = $serializer->unserialize($refreshToken);
        }
        catch (\Exception $exception)
        {
            return $this->response->badRequestResponse('Refresh token is invalid');
        }

        $payloadRefreshToken = json_decode($jsonWebSerializer->getPayload());
        if (!isset($payloadRefreshToken->client_id))
            return (object)array(
                'status'    => false,
                'data'      => 'Data Client ID tidak tersedia pada token'
            );

        $hashClientId = $payloadRefreshToken->client_id;
        $clientData = $this->client->getSecretByHashID($hashClientId);
        $clientSecret = $clientData[0]->secret;
        $clientId = $clientData[0]->id;
        $clientApplication = $clientData[0]->application;

        $generateEncryption = JWKFactory::createFromSecret($clientSecret,array(
            'alg'   => 'HS256',
            'use'   => 'sig'
        ));

        $jwsVerify = new JWSVerifier($algorithmManager);
        try
        {
            if(!$jwsVerify->verifyWithKey($jsonWebSerializer,$generateEncryption,0))
                return (object)array(
                    'status'    => false,
                    'data'      => 'Token Invalid'
                );
        }
        catch (\Exception $e)
        {
            return (object)array(
                'status'    => false,
                'data'      => $e->getMessage()
            );
        }

        // Validate if refresh token still have valid date
        $currentTime = Carbon::now()->unix();
        if ($payloadRefreshToken->exp < $currentTime) return (object)array(
            'status'    => false,
            'data'      => 'Token Expired'
        );

        return (object)array(
            'status'        => true,
            'id'            => $clientId,
            'application'   => $clientApplication
        );
    }
}
