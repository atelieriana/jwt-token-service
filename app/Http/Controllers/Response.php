<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class Response extends Controller
{
    public function __construct()
    {
    }

    /**
     * @param $dataToken object
     * @return JsonResponse
     */
    public function grantedTokenResponse(object $dataToken)
    {
        return response()
            ->json(array(
                'status_code'       => 200,
                'status_message'    => 'granted',
                'data'              => $dataToken
            ));
    }

    public function successResponse(object $data)
    {
        return response()
            ->json(array(
                'status_code'       => 200,
                'status_message'    => 'success',
                'data'              => $data
            ));
    }

    /**
     * Digunakan untuk melakukan kembalian invalid credential
     * @return JsonResponse
     */
    public function invalidCredentialsResponse()
    {
        return response()
            ->json(array(
                'status_code'       => 401,
                'status_message'    => 'Invalid Credentials'
            ),401);
    }

    /**
     * Digunakan untuk melakukan kembalian data kurang permission
     * @return JsonResponse
     */
    public function insufficientPermissionsResponse()
    {
        return response()
            ->json(array(
                'status_code' => 403,
                'status_message' => 'Insufficient Permissions, there`s no permissions to perform this action',
            ),403);
    }

    public function badRequestResponse($data)
    {
        return response()
            ->json(array(
                'status_code'       => 400,
                'status_message'    => 'Bad Request',
                'data'              => $data
            ),400);
    }
}
