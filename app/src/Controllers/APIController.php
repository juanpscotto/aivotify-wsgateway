<?php

namespace App\Controllers;

use Slim\Http\Response;

class APIController
{
    protected $statusCode = 200;

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function setStatusCode($status): APIController
    {
        $this->statusCode = $status;
        return $this;
    }

    public function respondResult(Response $response, $result = null, $message = 'Ok'): Response
    {
        return $this->setStatusCode(200)->respond($response, ['status' => true, 'message' => $message, 'result' => $result]);
    }

    public function respondWithError(Response $response, $error = 500, $message = 'Error', $result = null): Response
    {
        return $this->setStatusCode($error)->respond($response, [
                'status' => false,
                'message' => $message,
                'result' => $result,
            ]);
    }

    public function respond(Response $response, $data): Response
    {
        return $response->withHeader('Content-Type', 'application/json')
                        ->withStatus($this->getStatusCode())
                        ->write(json_encode($data));
    }
}
