<?php
require "../../util/db.php";

function initialSetup($method, $headers)
{
    global $db;

    if ($method != null && $_SERVER["REQUEST_METHOD"] != $method) {
        http_response_code(405);
        echo "Invalid method.";
        die();
    }

    if (isset($headers["Authorization"])) {
        $auth = $headers["Authorization"];
    } elseif (isset($headers["authorization"])) {
        $auth = $headers["authorization"];
    }

    if (!isset($auth)) {
        http_response_code(401);
        echo "Invalid authorization header.";
        die();
    }

    $auth = explode(" ", $auth);
    if (count($auth) != 2) {
        http_response_code(401);
        echo "Invalid authorization header.";
        die();
    }

    $auth_code = $auth[1];
    $result = login($auth_code);

    if (!$result) {
        http_response_code(401);
        echo "Invalid authorization token.";
        die();
    }
}

function validateContentType($requiredType, $headers)
{
    if (isset($headers["Content-Type"])) {
        $contentType = $headers["Content-Type"];
    } elseif (isset($headers["content-type"])) {
        $contentType = $headers["content-type"];
    }

    if (!isset($contentType)) {
        http_response_code(500);
        echo "Missing content type in server";
        die();
    }

    // lower case
    $contentType = strtolower($contentType);
    $requiredType = strtolower($requiredType);

    // check if $contentType contain the $requiredType
    if (strpos($contentType, $requiredType) === false) {
        http_response_code(400);
        echo "Invalid content type.";
        die();
    }
}