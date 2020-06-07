<?php

namespace CodexShaper\OAuth2\Server\Http\Controllers;

use CodexShaper\OAuth2\Server\Http\Requests\ServerRequest;
use CodexShaper\OAuth2\Server\Http\Responses\ServerResponse;
use CodexShaper\OAuth2\Server\Manager;

class AccessTokenController
{
    /**
     * The server manager.
     *
     * @var \CodexShaper\OAuth2\Server\Manager
     */
    protected $manager;

    /**
     * The authorization server.
     *
     * @var \League\OAuth2\Server\AuthorizationServer
     */
    protected $server;

    /**
     * The psr7 server request.
     *
     * @var \CodexShaper\OAuth2\Server\Http\Requests\ServerRequest
     */
    protected $request;

    /**
     * The psr7 server response.
     *
     * @var \CodexShaper\OAuth2\Server\Http\Responses\ServerResponse
     */
    protected $response;

    /**
     * Create a new access token controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->manager = new Manager();
        $this->server = $this->manager->makeAuthorizationServer();
        $this->request = ServerRequest::getPsrServerRequest();
        $this->response = ServerResponse::getPsrServerResponse();
    }

    /**
     * Make access token.
     *
     * @return \League\OAuth2\Server\ResponseTypes\ResponseTypeInterface
     */
    public function issueAccessToken()
    {
        try {

            // Try to respond to the request
            return $this->server->respondToAccessTokenRequest($this->request, $this->response)->getBody();
        } catch (OAuthServerException $exception) {

            // All instances of OAuthServerException can be formatted into a HTTP response
            return $exception->generateHttpResponse($this->response);
        }
    }
}
