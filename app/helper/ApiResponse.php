<?php

namespace App\Helper;

class ApiResponse
{
    private $status;
    private $message;
    private $data;
    private $httpStatusCode;

    public function __construct($status = true, $message = '', $data = [], $httpStatusCode = 200)
    {
        $this->status = $status;
        $this->message = $message;
        $this->data = $data;
        $this->httpStatusCode = $httpStatusCode;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;
    }

    public function send()
    {
        http_response_code($this->httpStatusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $this->status,
            'message' => $this->message,
            'data' => $this->data
        ]);
        exit;
    }
}

// Example usage:
// $response = new ApiResponse();
// $response->setStatus(true);
// $response->setMessage('Request successful');
// $response->setData(['key' => 'value']);
// $response->setHttpStatusCode(200);
// $response->send();
