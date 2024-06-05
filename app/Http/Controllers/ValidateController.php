<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\RS512;

class ValidateController extends Controller
{
    protected Response $response;

    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Digunakan untuk memvalidasi token yang diberikan dalam header authorization
     *  token akan di validasi berdasarkan algoritma RS512 dan waktu expired
     *
     * @param $token
     * @return JsonResponse
     */
    public function validateToken($token)
    {
        if (!$token) return $this->response->invalidCredentialsResponse();

        $serializer = new CompactSerializer();
        try
        {
            $jws = $serializer->unserialize($token);
        }
        catch (\Exception $exception)
        {
            return $this->response->badRequestResponse('Bearer token is invalid');
        }

        // Verify signature
        $key = JWKFactory::createFromKeyFile(
            env('JWT_PRIVATE_KEY'),
            null
        );

        $jwsVerifier = new JWSVerifier(new AlgorithmManager([new RS512()]));
        try {
            if (!$jwsVerifier->verifyWithKey($jws, $key, 0))
                return $this->response->badRequestResponse('Token invalid');
        } catch (\Exception $e) {
            return $this->response->badRequestResponse($e->getMessage());
        }

        $payload = json_decode($jws->getPayload(), true);

        // Validate expiration time
        $currentTime = Carbon::now()->unix();

        if ($payload['exp'] < $currentTime) return $this->response->badRequestResponse('Token expired');

        $statusToken = (object)array(
            'status_token'      => true,
            'sub'               => $payload['sub'],
            'aud'               => $payload['aud'],
            'expired_in'        => $payload['exp']
        );

        return $this->response->successResponse($statusToken);
    }
}
