<?php
$headers = apache_request_headers();

include "../../util/commons.php";
initialSetup("GET", $headers);

if (!isset($_GET["roblox_id"])) {
    http_response_code(400);
    echo "Missing roblox_id parameters.";
    die();
}

$roblox_id = $_GET["roblox_id"];

header("Content-Type: application/json");
header("Cache-Control: no-cache, must-revalidate");

$user = get_user($roblox_id);
if (!isset($user)) {
    $stmt_create = $db->prepare("INSERT INTO users (roblox_id, total_gg) VALUES (?, 0)");
    $stmt_create->bind_param("i", $roblox_id);
    $stmt_create->execute();

    $stmt_create->close();

    $user = get_user($roblox_id);
}

http_response_code(200);
echo json_encode($user);
?>