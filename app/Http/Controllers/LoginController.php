<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Digunakan untuk mengecek login user
     *
     * @return bool|array
     */
    public function attemptLogin(LoginRequest $request)
    {
        $username = $request->post('username');
        $password = $request->post('password');

        $hashPassword = sha1(md5($password));
        $client = $this->client->getClient($username, $hashPassword);

        if (empty($client)) return false;

        return $client[0];
    }
}
