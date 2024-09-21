<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Token;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TokenRepository
{
    private $db;
    private $token;
    private $secretKey;
    private $hash_algo;
    private $is_expiry = true;
    private $token_decode;

    public function __construct($db)
    {
        $this->db = $db;
        $this->token = new Token($db);
        $this->secretKey = $_ENV['SECRET_KEY'];
        $this->hash_algo = $_ENV['HASH_ALGO'];
        $token = $this->getJWTFromHeader();
        $this->validateJWT($token);
    }

    public function createToken($data)
    {
        $this->token->userId = $data['userId'];
        $this->token->refreshToken = $data['refreshToken'];
        $this->token->expiresIn = $data['expiresIn'];
        return $this->token->create();
    }

    public function getTokenByUserId($userId)
    {
        $this->token->userId = $userId;
        return $this->token->getTokenByUserId();
    }

    public function saveRefreshToken($user)
    {
        $data = [];
        $refreshToken = false;
        if ($user['id']) {
            $payload = [
                'sub' => $user['id'],
                'exp' => time() + 3600,
                'email' => $user['email']
            ];
            // Generate JWT token
            $token = JWT::encode($payload, $this->secretKey, $this->hash_algo);
            $user_token = $this->getTokenByUserId($user['id']);
            $this->token->refreshToken = bin2hex(random_bytes(32));
            $this->token->userId = $user['id'];
            if (empty($user_token['id'])) {
                $refreshToken = $this->token->addToken();
            } else {
                $refreshToken = $this->token->saveRefreshToken();
            }
            // Respond with user data and tokens
            if ($refreshToken) {
                $data = [
                    'user' => [
                        'firstName' => $user['firstName'],
                        'lastName' => $user['lastName'],
                        'email' => $user['email'],
                        'displayName' => $user['firstName'] . ' ' . $user['lastName']
                    ],
                    'token_type' => 'Bearer',
                    'token' => $token,
                    'refreshToken' => $this->token->refreshToken
                ];
            }
        }
        return $data;
    }

    function decodeJWT($jwt)
    {
        try {
            // Decode the JWT token
            $decoded = JWT::decode($jwt, new Key($this->secretKey, $this->hash_algo));
            return (array) $decoded;
        } catch (Exception $e) {
            // Handle errors
            // error_log('JWT decode error: ' . $e->getMessage());
            return null;
        }
    }

    function getJWTFromHeader()
    {
        // Get all headers
        $headers = getallheaders();

        // Check if the Authorization header is set
        if (isset($headers['Authorization'])) {
            // Extract the JWT from the Bearer token
            $authHeader = $headers['Authorization'];
            if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
                return $matches[1];
            }
        }

        // Return null if no JWT is found
        return null;
    }

    function validateJWT($jwt)
    {
        if (empty($jwt)) {
            return [];
        }
        try {
            // Decode the JWT token
            $decoded = JWT::decode($jwt, new Key($this->secretKey, $this->hash_algo));
            $decoded = (array) $decoded;
            $timestamp = time();
            $this->token->userId = $decoded['sub'];
            $token = $this->token->getTokenByUserId($decoded['sub']);
            if ($timestamp < $decoded['exp'] && !empty($token)) {
                $this->token_decode = $decoded;
                $this->is_expiry = false;
            } else {
                $this->is_expiry = true;
            }
            return $decoded;
        } catch (ExpiredException $e) {
            // Handle expired token
            // error_log('JWT expired: ' . $e->getMessage());
            return ['error' => 'Token has expired'];
        } catch (Exception $e) {
            // Handle other errors
            // error_log('JWT decode error: ' . $e->getMessage());
            return ['error' => 'Invalid token'];
        }
    }

    function removalToken()
    {
        if (!$this->is_expiry) {
            if ($this->token->removalToken()) {
                return true;
            }
        }
        return false;
    }

    function refreshToken($refreshToken)
    {
        $this->token->refreshToken = $refreshToken;
        $token = $this->token->getTokenByUserRefreshToken();
        if (!empty($token)) {
            $userRepository = new UserRepository($this->db);
            $user = $userRepository->getUserById($token['userId']);
            $data = $this->saveRefreshToken($user);
            if (!empty($data)) {
                return [
                    'token' => $data['token'],
                    'refreshToken' => $data['refreshToken']
                ];
            }
        }
        return [];
    }

    function getDecodeToken()
    {
        return $this->token_decode;
    }
}
