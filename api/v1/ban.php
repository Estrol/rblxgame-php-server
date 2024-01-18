<?php
$headers = apache_request_headers();

include "../../util/commons.php";
initialSetup(null, $headers);

$method = $_SERVER["REQUEST_METHOD"];

header("Content-Type: application/json");
header("Cache-Control: no-cache, must-revalidate");

if ($method == "GET") {
    $roblox_id = $_GET["roblox_id"];

    $user = get_user($roblox_id);
    if (!isset($user)) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid roblox_id."
        );

        echo json_encode($data);
        die();
    }

    $data = array(
        "status" => true,
        "is_banned" => $user["is_banned"] == 1,
        "ban_reason" => $user["ban_reason"]
    );

    echo json_encode($data);
    die();
}

if ($method == "POST") {
    validateContentType("application/json", $headers);
    $body = file_get_contents("php://input");

    if (!isset($body)) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid body."
        );

        echo json_encode($data);
        die();
    }

    $body = json_decode($body, true);

    // check json error
    if (json_last_error() != JSON_ERROR_NONE) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid body."
        );

        echo json_encode($data);
        die();
    }

    $roblox_id = $body["roblox_id"];

    if (!isset($roblox_id)) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid roblox_id."
        );

        echo json_encode($data);
        die();
    }

    $user = get_user($roblox_id);
    if (!isset($user)) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid roblox_id."
        );

        echo json_encode($data);
        die();
    }

    $ban_reason = $body["ban_reason"];

    if (!isset($ban_reason)) {
        $ban_reason = "User is banned from the game.";
    }

    $stmt = $db->prepare("UPDATE users SET is_banned = 1, ban_reason = ? WHERE roblox_id = ?");
    $stmt->bind_param("si", $ban_reason, $roblox_id);

    $stmt->execute();
    $stmt->close();

    $data = array(
        "status" => true,
        "message" => "User has been banned."
    );

    echo json_encode($data);
    die();
}

if ($method == "DELETE") {
    $body = file_get_contents("php://input");
    $_DELETE = array();

    if (!isset($body)) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid body."
        );

        echo json_encode($data);
        die();
    }

    parse_str($body, $_DELETE);

    $roblox_id = $_DELETE["roblox_id"];

    if (!isset($roblox_id)) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid body."
        );

        echo json_encode($data);
        die();
    }

    $user = get_user($roblox_id);
    if (!isset($user)) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid roblox_id."
        );

        echo json_encode($data);
        die();
    }

    $stmt = $db->prepare("UPDATE users SET is_banned = 0, ban_reason = NULL WHERE roblox_id = ?");
    $stmt->bind_param("i", $roblox_id);

    $stmt->execute();
    $stmt->close();

    $data = array(
        "status" => true,
        "message" => "User has been unbanned."
    );

    echo json_encode($data);
    die();
}

?>