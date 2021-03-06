<?php

namespace App\Http\Controllers\Api\V1\Auth;

use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Exception\ClientException;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use CloudCreativity\LaravelJsonApi\Document\Error;
use CloudCreativity\LaravelJsonApi\Http\Controllers\JsonApiController;

class LoginController extends JsonApiController
{
    /**
     * Handle the incoming request.
     *
     * @param LoginRequest $request
     * @return mixed
     */
    public function __invoke(LoginRequest $request)
    {
        try {
            // TODO: This should be removed in production since it is a security measure
            // Without this it crashes on local development
            $http = new Client(['verify' => false]);

            $response = $http->post(route('passport.token'), [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => env('CLIENT_ID'),
                    'client_secret' => env('CLIENT_SECRET'),
                    'username' => $request->email,
                    'password' => $request->password,
                    'scope' => '',
                ],
            ]);

            return json_decode((string) $response->getBody(), true);
        } catch (ClientException $e) {
            $error = json_decode($e->getResponse()->getBody()->getContents());

            return $this->reply()->errors(Error::create([
                'title' => 'Bad Request',
                'detail' => $error->message,
                'status' => '400',
            ]));
        }
    }
}
