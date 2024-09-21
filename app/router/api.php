<?php

use App\Router\Router;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$router = new Router();

$router->add('POST', '/test', 'AuthController@test');
$router->add('POST', '/sign-up', 'AuthController@signup');
$router->add('POST', '/sign-in', 'AuthController@signin');
$router->add('POST', '/sign-out', 'AuthController@signout');
$router->add('POST', '/refresh-token', 'AuthController@refreshToken');

$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$router->dispatch($method, $uri);
