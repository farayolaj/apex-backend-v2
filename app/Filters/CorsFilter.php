<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;


class CorsFilter implements FilterInterface
{
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {

    }

    public function before(RequestInterface $request, $arguments = null)
    {
        $origin = null;
        // get origins
        if (array_key_exists('HTTP_ORIGIN', $_SERVER)) {
            $origin = $_SERVER['HTTP_ORIGIN'];
        } else if (array_key_exists('HTTP_REFERER', $_SERVER)) {
            $origin = $_SERVER['HTTP_REFERER'];
        } else {
            $origin = $_SERVER['REMOTE_ADDR'];
        }
        
        // considering if this would be needed in the code for security
        // if needed, i would need to use cache/filesystem storage like approach for performance
        $allowed_domains = array(
            'http://127.0.0.1',
            'http://localhost:8080',
            'http://localhost:8083',
            'https://apex.ui.edu.ng',
        );
        // this below code work on local server localhost:8080
        if (in_array($origin, $allowed_domains)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }

        // header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-APP-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization,Content-length, Referer,Referrer,User-Agent");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Content-Type: application/json; charset=utf-8");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 3600");

        // this is for preflight request
        $method = $_SERVER['REQUEST_METHOD'];
        if($method == "OPTIONS") {
            if (in_array($origin, $allowed_domains)) {
                header('Access-Control-Allow-Origin: ' . $origin);
            }
            header("Access-Control-Allow-Headers: X-APP-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Authorization,Content-length, Referer,Referrer,User-Agent");
            header("HTTP/1.1 200 OK");
            die();
        }

    }
} 