<?php

namespace App\Http\Controllers;

use App\Http\Template\Response;
use App\Http\Template\TokenManagement;
use Illuminate\Http\JsonResponse;

class ValidateController extends Controller
{
    protected TokenManagement $tokenManagement;
    protected Response $response;
    protected $publicKey;

    public function __construct()
    {
        $this->tokenManagement = new TokenManagement();
        $this->response = new Response();
        $this->publicKey = file_get_contents(env('JWT_PUBLIC_KEY'));
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
        // Validate Token Parameters
        if (empty($token))
            return $this->response->invalidCredentialsResponse();

        $payload = $this->tokenManagement->validateAccessToken($token);

        $statusToken = (object)array(
            'status_token'      => true,
            'sub'               => $payload->sub,
            'aud'               => $payload->aud,
            'expired_in'        => $payload->exp
        );

        return $this->response->successResponse($statusToken);
    }
}
