<?php

namespace App\Http\Template;

use App\Models\Client;
use Carbon\Carbon;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\JsonResponse;
use stdClass;

class TokenManagement
{
    protected Client $client;
    protected Response $response;
    protected Carbon $carbon;
    protected $publicKey;
    protected $privateKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->response = new Response();

        $this->publicKey = file_get_contents(env('JWT_PUBLIC_KEY'));
        $this->privateKey = file_get_contents(env('JWT_PRIVATE_KEY'));
    }

    /**
     * @param $account
     * @param $accessModule
     * @return string
     */
    public function generateAccessToken($account, $accessModule)
    {
        $payload = [
            'iss'           => env('APP_NAME'),
            'sub'           => $account->application,
            'aud'           => $accessModule,
            'iat'           => Carbon::now()->unix(),
            'exp'           => Carbon::now()->addMinutes((int)config('api.access_token_duration'))->unix(),
            'client_id'     => sha1(md5($account->id))
        ];

        return JWT::encode($payload, $this->privateKey,'RS512');
    }

    /**
     * @param $account
     * @return string
     */
    public function generateRefreshToken($account)
    {
        $payload = [
            'sub'       => $account->application,
            'iat'       => Carbon::now()->unix(),
            'exp'       => Carbon::now()->addDays((int)config('api.refresh_token_duration'))->unix(),
            'client_id' => sha1(md5($account->id))
        ];

        return Jwt::encode($payload, $account->secret,'HS256');
    }

    /**
     * @param $accessToken
     * @return JsonResponse|stdClass
     */
    public function validateAccessToken($accessToken)
    {
        try
        {
            $payload = JWT::decode($accessToken, new Key($this->publicKey, 'RS512'));
            return $payload;
        }
        catch (Exception $exception)
        {
            return $this->response->badRequestResponse($exception->getMessage())->send();
        }
    }

    /**
     * @param $refreshToken
     * @param $client
     * @return JsonResponse|stdClass
     */
    public function validateRefreshToken($refreshToken, $secret)
    {
        try
        {
            $payload = JWT::decode($refreshToken, new Key($secret, 'HS256'));
            return $payload;
        }
        catch (Exception $exception)
        {
            return $this->response->badRequestResponse($exception->getMessage())->send();
        }
    }
}
