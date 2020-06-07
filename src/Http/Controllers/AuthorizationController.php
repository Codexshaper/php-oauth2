<?php

namespace CodexShaper\OAuth2\Server\Http\Controllers;

use CodexShaper\OAuth2\Server\Entities\User as UserEntity;
use CodexShaper\OAuth2\Server\Http\Requests\ServerRequest;
use CodexShaper\OAuth2\Server\Http\Responses\ServerResponse;
use CodexShaper\OAuth2\Server\Manager;
use CodexShaper\OAuth2\Server\Model;
use CodexShaper\OAuth2\Server\Models\User;
use Illuminate\Http\Request;
use League\OAuth2\Server\Exception\OAuthServerException;

class AuthorizationController
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
     * Create a new authorization controller instance.
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
     * Make authorization.
     *
     * @param \CodexShaper\OAuth2\Server\Models\User $user
     *
     * @return \League\OAuth2\Server\ResponseTypes\ResponseTypeInterface|void
     */
    public function authorize($user)
    {
        try {

            // Validate the HTTP request and return an AuthorizationRequest object.
            $authRequest = $this->server->validateAuthorizationRequest($this->request);

            // Get all validate scopes from psr request
            $scopes = $this->filterScopes($authRequest);

            // Get token for current user and request client id
            $token = Model::findToken('clientModel', $authRequest, $user);

            if (($token) || Model::instance('clientModel')->isSkipsAuthorization()) {
                return $this->approve($authRequest, $user);
            }

            return  $authRequest;
        } catch (OAuthServerException $exception) {

            // All instances of OAuthServerException can be formatted into a HTTP response
            return $exception->generateHttpResponse($this->response);
        }
    }

    /**
     * Approve the authorization.
     *
     * @param \League\OAuth2\Server\RequestTypes\AuthorizationRequest $authRequest
     * @param \CodexShaper\OAuth2\Server\Models\User                  $user
     *
     * @return \League\OAuth2\Server\ResponseTypes\ResponseTypeInterface
     */
    public function approve($authRequest, $user)
    {
        // Once the user has logged in set the user on the AuthorizationRequest
        $authRequest->setUser(new UserEntity($user->getKey())); // an instance of UserEntityInterface

        // Once the user has approved or denied the client update the status
        // (true = approved, false = denied)
        $authRequest->setAuthorizationApproved(true);

        // Return the HTTP redirect response
        return $this->server->completeAuthorizationRequest($authRequest, $this->response);
    }

    /**
     * Deny the authorization request.
     *
     * @return void
     */
    public function deny()
    {
    }

    /**
     * Filter all scopes.
     *
     * @param \League\OAuth2\Server\RequestTypes\AuthorizationRequest $authRequest
     *
     * @return array
     */
    public function filterScopes($authRequest)
    {
        return array_filter($authRequest->getScopes(), function ($scope) {
            if (Manager::isValidateScope($scope->getIdentifier())) {
                return $scope->getIdentifier();
            }
        });
    }
}
