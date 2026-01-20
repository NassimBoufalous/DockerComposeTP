<?php

$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET' && $uri === '/api/hello') {
    echo "Hello World";
    exit;
}

http_response_code(404);
echo "Not Found";
