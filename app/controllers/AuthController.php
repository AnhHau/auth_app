<?php

/**
 * An class for checking files and folders against exact matches.
 *
 * Supports both whitelists and blacklists.
 *
 * @author    haubui <bahau15th@gmail.com>
 * @copyright 2006-2015 Test time Ltd (TEST 77 084 670 600)
 * @license   https://github.com/AnhHau/auth_app/blob/master/licence.txt BSD Licence
 */

namespace App\Controllers;

use App\Helper\Validator;
use App\Helper\ApiResponse;
use App\Repositories\UserRepository;
use App\Repositories\TokenRepository;
use App\Config\Database;

/** 
 * Class auth
 * 
 * @author    haubui <bahau15th@gmail.com>
 * @copyright 2006-2015 Test time Ltd (TEST 77 084 670 600)
 * @license   https://github.com/AnhHau/auth_app/blob/master/licence.txt BSD Licence
 */
class AuthController
{
    private $_userRepository;
    private $_tokenRepository;
    protected $response;

    /** 
     * Construct
     * 
     */
    public function __construct()
    {
        $database = new Database();
        $db = $database->getConnection();

        $this->_userRepository = new UserRepository($db);
        $this->_tokenRepository = new TokenRepository($db);
        $this->response = new ApiResponse();
    }

    /** 
     * A function handle action
     * 
     * @return json
     */
    public function signup()
    {
        $data = [
            'email' => isset($_POST['email']) ? $_POST['email'] : '',
            'password' => isset($_POST['password']) ? $_POST['password'] : '',
            'firstName' => isset($_POST['firstName']) ? $_POST['firstName'] : '',
            'lastName' => isset($_POST['lastName']) ? $_POST['lastName'] : '',
        ];

        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'max:20']
        ];

        $validator = new Validator();
        if ($validator->validate($data, $rules)) {
            $data = $this->_userRepository->createUser($data);
            if (empty($data)) {
                $this->response->setStatus(false);
                $this->response->setHttpStatusCode(400);
                $this->response->setMessage('Duplicate email');
            } else {
                $this->response->setHttpStatusCode(200);
                $this->response->setMessage('User created successfully');
                $this->response->setData($data);
            }
        } else {
            $this->response->setStatus(false);
            $this->response->setHttpStatusCode(400);
            $this->response->setMessage('User creation failed');
            $this->response->setData($validator->errors());
        }

        return $this->response->send();
    }

    /** 
     * A function handle action
     * 
     * @return json
     */
    public function signin()
    {
        $data = [
            'email' => isset($_POST['email']) ? $_POST['email'] : '',
            'password' => isset($_POST['password']) ? $_POST['password'] : '',
        ];

        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8', 'max:20']
        ];

        $validator = new Validator();
        if ($validator->validate($data, $rules)) {
            $user = $this->_userRepository->getUserByEmail($data['email']);
            if ($user && password_verify($data['password'], $user['hash'])) {
                $result = $this->_tokenRepository->saveRefreshToken($user);
                if (!$result) {
                    $this->response->setStatus(false);
                    $this->response->setHttpStatusCode(500);
                } else {
                    $this->response->setHttpStatusCode(200);
                    $this->response->setMessage('Refresh token successfully');
                    $this->response->setData($result);
                }
            } else {
                $this->response->setStatus(false);
                $this->response->setHttpStatusCode(400);
                $this->response->setMessage('Invalid sign-in detail');
            }
        } else {
            $this->response->setStatus(false);
            $this->response->setHttpStatusCode(400);
            $this->response->setMessage('Invalid sign-in detail');
            $this->response->setData($validator->errors());
        }
        return $this->response->send();
    }

    /** 
     * A function handle action
     * 
     * @return json
     */
    public function signout()
    {
        // Invalidate the token on the client side
        if ($this->_tokenRepository->removalToken()) {
            $this->response->setHttpStatusCode(200);
            $this->response->setMessage('Successfully logged out');
        } else {
            $this->response->setStatus(false);
            $this->response->setHttpStatusCode(500);
        }
        return $this->response->send();
    }

    /** 
     * A function handle action
     * 
     * @return json
     */
    public function refreshToken()
    {
        $refreshToken = isset($_POST['refreshToken']) ? $_POST['refreshToken'] : '';
        $rules = [
            'refreshToken' => ['required']
        ];

        $validator = new Validator();
        if ($validator->validate(['refreshToken' => $refreshToken], $rules)) {
            $result = $this->_tokenRepository->refreshToken($refreshToken);
            if (!empty($result)) {
                $this->response->setHttpStatusCode(200);
                $this->response->setData($result);
            } else {
                $this->response->setStatus(false);
                $this->response->setHttpStatusCode(404);
                $this->response->setMessage('Token not found');
            }
        } else {
            $this->response->setStatus(false);
            $this->response->setHttpStatusCode(400);
            $this->response->setMessage($validator->errors());
        }
        return $this->response->send();
    }
}
