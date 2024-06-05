<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\Client;
use Carbon\Carbon;

use Illuminate\Http\JsonResponse;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\Algorithm\RS256;
use Jose\Component\Signature\Algorithm\RS512;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\Serializer\CompactSerializer;
use JsonException;

class GenerateController extends Controller
{
    protected $login;
    protected Response $response;
    protected GenerateToken $generateToken;
    protected $currentTime;

    public function __construct(LoginController $login,
                                GenerateToken $generateToken,
                                Response $response)
    {
        $this->login            = $login;
        $this->response         = $response;
        $this->currentTime      = Carbon::now();
        $this->generateToken    = $generateToken;
    }

    /**
     * Digunakan sebagai endpoint generate token
     *
     * @param LoginRequest $request
     * @return JsonResponse
     */
    public function generateToken(LoginRequest $request)
    {
        $account = $this->login->attemptLogin($request);

        if (!$account) return $this->response->invalidCredentialsResponse();

        // get detail access module dari user login
        $client = Client::with('detail_modules.module', 'detail_modules.access')
            ->where('username', $request->username)
            ->first();

        $accessModule = array();
        foreach($client->detail_modules as $item){
            $accessModule[] = $item->module->module . '.' . $item->access->desc;
        }

        $accessToken = $this->generateToken->generateAccessToken($account, $accessModule);
        $refreshToken = $this->generateToken->generateRefreshToken($account);

        $dataToken = (object)array(
            'type'          => 'bearer',
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expired_in'    => $this->currentTime->addMinutes((int)config('api.access_token_duration'))->format('Y-m-d H:i:s'),
        );

        return $this->response->grantedTokenResponse($dataToken);
    }
}
