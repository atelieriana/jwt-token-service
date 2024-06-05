<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\JWK;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\RS512;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use JsonException;

class GenerateToken
{
    protected $currentTime;

    public function __construct()
    {
        $this->currentTime = Carbon::now();
    }

    /**
     * Digunakan untuk melakukan generate access_token
     * @param $account
     * @param $accessModule
     * @return string
     * @throws JsonException
     */
    public function generateAccessToken($account, $accessModule)
    {
        // Penentuan Algorithm Enkripsi
        $algorithmManager = new AlgorithmManager([
            new RS512()
        ]);

        $keyEncryption = JWKFactory::createFromKeyFile(
            config('api.jwt_private_key')
        );

        $jwkAccessToken = JWK::createFromJson(json_encode($keyEncryption));
        $jwsBuilder = new JWSBuilder($algorithmManager);

        $payload = json_encode([
            'iss'           => env('APP_NAME'),
            'sub'           => $account->application,
            'aud'           => $accessModule,
            'iat'           => $this->currentTime->unix(),
            'exp'           => $this->currentTime->addMinutes((int)config('api.access_token_duration'))->unix(),
            'client_id'     => sha1(md5($account->id))
        ]);

        $signedAccessToken = $jwsBuilder
            ->create()                               // We want to create a new JWS
            ->withPayload($payload)                  // We set the payload
            ->addSignature($jwkAccessToken, [
                'alg'   => 'RS512',
                'typ'   => 'JWT',
                'kty'   => 'RSA'
            ]) // We add a signature with a simple protected header
            ->build();

        $serializer = new CompactSerializer();

        // We serialize the signature at index 0 (we only have one signature).
        $accessToken = $serializer->serialize($signedAccessToken, 0);

        return $accessToken;
    }

    /**
     * Digunakan untuk melakukan generate refresh_token
     *
     * @param $account
     * @return string
     */
    public function generateRefreshToken($account)
    {
        $algorithmManager = new AlgorithmManager([
            new HS256()
        ]);

        $jwsBuilder = new JWSBuilder($algorithmManager);
        $payload = json_encode(array(
            'sub'       => $account->application,
            'iat'       => $this->currentTime->unix(),
            'exp'       => $this->currentTime->addDays((int)config('api.refresh_token_duration'))->unix(),
            'client_id' => sha1(md5($account->id))
        ));

        $generateEncryption = JWKFactory::createFromSecret($account->secret,array(
            'alg'   => 'HS256',
            'use'   => 'sig'
        ));

        $signedRefreshToken = $jwsBuilder
            ->create()
            ->withPayload($payload)
            ->addSignature($generateEncryption, ['alg' => $generateEncryption->get('alg')])
            ->build();

        $serializer = new CompactSerializer();
        $refreshToken = $serializer->serialize($signedRefreshToken, 0);

        return $refreshToken;
    }
}
