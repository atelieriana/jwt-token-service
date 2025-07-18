<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Template\Response;
use App\Http\Template\TokenManagement;
use App\Models\Client;
use Carbon\Carbon;;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GenerateController extends Controller
{
    protected TokenManagement $tokenManagement;
    protected Client $client;
    protected Response $response;
    protected Carbon $carbon;
    protected $privateKey;

    public function __construct()
    {
        $this->tokenManagement = new TokenManagement();
        $this->client = new Client();
        $this->response = new Response();
        $this->carbon = new Carbon();
        $this->privateKey = file_get_contents(env('JWT_PRIVATE_KEY'));
    }

    public function generateToken(Request $request)
    {
        $account = $this->attemptLogin($request);

        if (!$account) return $this->response->invalidCredentialsResponse();

        // get detail access module dari user login
        $client = Client::with('detail_modules.module', 'detail_modules.access')
            ->where('username', $request->username)
            ->first();

        $accessModule = array();
        foreach($client->detail_modules as $item){
            $accessModule[] = $item->module->module . '.' . $item->access->desc;
        }

        $accessToken = $this->tokenManagement->generateAccessToken($account, $accessModule);
        $refreshToken = $this->tokenManagement->generateRefreshToken($account);

        $dataToken = (object)array(
            'type'          => 'bearer',
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'expired_in'    => $this->carbon->addMinutes((int)config('api.access_token_duration'))->format('Y-m-d H:i:s'),
        );

        return $this->response->grantedTokenResponse($dataToken);
    }

    /**
     * @param Request $request
     * @return false|JsonResponse|mixed
     */
    private function attemptLogin(Request $request)
    {
        // Validate
        $validator = Validator::make($request->all(), (new LoginRequest())->rules());
        if ($validator->fails())
            return $this->response->badRequestResponse($validator->errors())
                ->send();

        $username = $request->post('username');
        $password = $request->post('password');

        $hashPassword = sha1(md5($password));
        $client = $this->client->getClient($username, $hashPassword);

        if (empty($client)) return false;

        return $client[0];
    }
}
