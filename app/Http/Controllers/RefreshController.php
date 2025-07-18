<?php

namespace App\Http\Controllers;

use App\Http\Template\Response;
use App\Http\Template\TokenManagement;
use App\Models\Client;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\RS512;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;

class RefreshController extends Controller
{
    protected Response $response;
    protected Client $client;
    protected TokenManagement $tokenManagement;
    protected $currentTime;

    public function __construct()
    {
        $this->client           = new Client();
        $this->tokenManagement  = new TokenManagement();
        $this->response         = new Response();
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
        $resultValidateAccessToken = $this->tokenManagement->validateAccessToken($accessToken);

        // Client
        $dataClient = $this->client->getSecretByHashID($resultValidateAccessToken->client_id);

        $resultValidateRefreshToken = $this->tokenManagement->validateRefreshToken($refreshToken, $dataClient[0]->secret);

        if (!$resultValidateAccessToken->client_id)
            return $this->response->badRequestResponse($resultValidateAccessToken);
        if (!$resultValidateRefreshToken->client_id)
            return $this->response->badRequestResponse($resultValidateRefreshToken);

        // get detail access module dari user login
        $client = Client::with('detail_modules.module', 'detail_modules.access')
            ->where('id', $dataClient[0]->id)
            ->first();

        $accessModule = array();
        foreach($client->detail_modules as $item){
            $accessModule[] = $item->module->module . '.' . $item->access->desc;
        }

        $accessToken = $this->tokenManagement->generateAccessToken($dataClient[0], $accessModule);
        $dataToken = (object)array(
            'type'          => 'bearer',
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expired_in'    => $this->currentTime->addMinutes((int)config('api.access_token_duration'))->format('Y-m-d H:i:s'),
        );

        return $this->response->grantedTokenResponse($dataToken);
    }
}
