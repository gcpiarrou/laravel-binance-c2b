<?php

namespace App\Helpers\Traits;

trait HandlesResponseErrors
{

    protected $error;

    private function handleError($response)
    {
        // Return server related errors (500 range).
        if ($response->serverError()) {
            $this->error = [
                'code'    => '500',
                'error'   => 'server_error',
                'message' => 'External server side error.'
            ];
        }

        // Return client related errors.
        elseif ($response->clientError()) {
            // If client error has a response code.
            if (isset($response['code'])) {
                $this->error = [
                    'code'    => $response['code'],
                    'error'   => 'client_error',
                    'message' => isset($response['errorMessage']) ? $response['errorMessage'] : 'An unexpected error occurred'
                ];
            } else {
                // If client error a response status.
                if ($response->status() === 403) {
                    $this->error = [
                        'code'    => $response['code'],
                        'error'   => 'forbidden',
                        'message' => "You don't have permission to access this resouce."
                    ];
                }
            }

            return $this->error;
        }
    }

    private function hostNotFoundError(\Exception $e){
        return [
            'code'    => $e->getCode(),
            'error'   => 'Host Not Found',
            'message' => 'Could not resolve host: '.$this->api_url
        ];
    }

    private function curlError(\Exception $e){
        return [
            'code'    => $e->getCode(),
            'error'   => 'cUrl Error',
            'message' => $e->getMessage()
        ];
    }
}

