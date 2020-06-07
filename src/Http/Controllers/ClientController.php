<?php

namespace CodexShaper\OAuth2\Server\Http\Controllers;

use CodexShaper\OAuth2\Server\Manager;
use CodexShaper\OAuth2\Server\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ClientController
{
    /**
     * Get all clients.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return json
     */
    public function all(Request $request)
    {
        $clients = Model::instance('clientModel')
                   ->whereUserId($request->user_id)
                   ->orderBy('updated_at', 'DESC')
                   ->get()
                   ->makeVisible('secret');

        return json_encode(['clients' => $clients]);
    }

    /**
     * Create a client.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return json
     */
    public function store(Request $request)
    {
        try {
            $client = Model::instance('clientModel');

            $client->user_id = $request->user_id;
            $client->name = $request->name;
            $client->redirect = !empty($request->redirect) ? $request->redirect : 'http://localhost';
            $client->secret = Str::random(40);
            $client->personal_access_client = $request->type == 'personal_access' ? 1 : 0;
            $client->password_client = $request->type == 'password' ? 1 : 0;
            $client->authorization_code_client = $request->type == 'authorization_code' ? 1 : 0;
            $client->revoked = 0;

            $client->save();
        } catch (\Exception $ex) {
            return json_encode(['errors' => [$ex->getMessage()]], 404);
        }

        return $this->all($request);
    }

    /**
     * Modify a client.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return json
     */
    public function update(Request $request)
    {
        try {
            $client = Model::instance('clientModel')->find($request->id);

            $client->user_id = $request->user_id;
            $client->name = $request->name;
            $client->redirect = !empty($request->redirect) ? $request->redirect : 'http://localhost';
            // $client->secret = Str::random(40);
            $client->personal_access_client = $request->type == 'personal_access' ? 1 : 0;
            $client->password_client = $request->type == 'password' ? 1 : 0;
            $client->authorization_code_client = $request->type == 'authorization_code' ? 1 : 0;
            $client->revoked = 0;

            $client->save();
        } catch (\Exception $ex) {
            return json_encode(['errors' => [$ex->getMessage()]], 404);
        }

        return $this->all($request);
    }

    /**
     * Destroy a client.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return json
     */
    public function destroy(Request $request)
    {
        $client = Model::instance('clientModel')->whereId($request->id)->whereUserId($request->user_id)->first();

        if ($client) {
            $client->tokens()->delete();
            $client->delete();

            return $this->all($request);
        }

        return json_encode(['errors' => ['There is no client.']], 404);
    }
}
