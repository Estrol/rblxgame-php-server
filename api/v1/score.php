<?php
$headers = apache_request_headers();

include "../../util/commons.php";
initialSetup(null, $headers);

$method = $_SERVER["REQUEST_METHOD"];

header("Content-Type: application/json");
header("Cache-Control: no-cache, must-revalidate");

if ($method == "GET") {
    if (!isset($_GET["roblox_id"]) || !isset($_GET["md5hash"])) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid parameters."
        );

        echo json_encode($data);
        die();
    }

    $roblox_id = $_GET["roblox_id"];
    $md5hash = $_GET["md5hash"];

    $user_id = find_user_id($roblox_id);

    if (!isset($user_id)) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid roblox_id."
        );

        echo json_encode($data);
        die();
    }

    $score = get_score($user_id, $md5hash);
    if (!isset($score)) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Missing score."
        );

        echo json_encode($data);
        die();
    }

    $data = array(
        "status" => true,
        "score" => $score
    );

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
    $md5hash = $body["md5hash"];

    if (!isset($roblox_id) || !isset($md5hash)) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid body."
        );

        echo json_encode($data);
        die();
    }

    $user_id = find_user_id($roblox_id);
    if (!isset($user_id)) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid roblox_id."
        );

        echo json_encode($data);
        die();
    }

    if (
        !is_array($body) ||
        !isset($body["score"]) || !isset($body["marvelous"]) ||
        !isset($body["perfect"]) || !isset($body["great"]) ||
        !isset($body["good"]) || !isset($body["bad"]) ||
        !isset($body["miss"]) || !isset($body["maxCombo"]) ||
        !isset($body["gameplayGains"])
    ) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid body."
        );

        echo json_encode($data);
        die();
    }

    $score = get_score($user_id, $md5hash);
    $score_totalScore = $body["score"];
    $score_totalMaverlous = $body["marvelous"];
    $score_totalPerfect = $body["perfect"];
    $score_totalGreat = $body["great"];
    $score_totalGood = $body["good"];
    $score_totalBad = $body["bad"];
    $score_totalMiss = $body["miss"];
    $score_maxCombo = $body["maxCombo"];
    $score_gameplayGains = $body["gameplayGains"];

    if (!isset($score)) {
        put_score(
            $user_id,
            $md5hash,
            $score_totalScore,
            $score_totalMaverlous,
            $score_totalPerfect,
            $score_totalGreat,
            $score_totalGood,
            $score_totalBad,
            $score_totalMiss,
            $score_maxCombo,
            $score_gameplayGains
        );
    } else {
        update_score(
            $user_id,
            $md5hash,
            $score_totalScore,
            $score_totalMaverlous,
            $score_totalPerfect,
            $score_totalGreat,
            $score_totalGood,
            $score_totalBad,
            $score_totalMiss,
            $score_maxCombo,
            $score_gameplayGains
        );
    }

    calculate_gg($user_id);

    $user = get_user($roblox_id);
    $user_gg = $user["total_gg"];

    $data = array(
        "status" => true,
        "message" => "Score updated.",
        "total_gg" => $user_gg
    );

    echo json_encode($data);
    die();
}

if ($method == "DELETE") {
    validateContentType("application/json", $headers);
    $body = file_get_contents("php://input");
    $_DELETE = json_decode($body, true);

    if (json_last_error() != JSON_ERROR_NONE) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid body."
        );

        echo json_encode($data);
        die();
    }

    if (!isset($_DELETE["roblox_id"]) || !isset($_DELETE["md5hash"])) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid body."
        );

        echo json_encode($data);
        die();
    }

    $roblox_id = $_DELETE["roblox_id"];
    $md5hash = $_DELETE["md5hash"];

    $user_id = find_user_id($roblox_id);
    if (!isset($user_id)) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Invalid roblox_id."
        );

        echo json_encode($data);
        die();
    }

    $score = get_score($user_id, $md5hash);
    if (!isset($score)) {
        http_response_code(400);

        $data = array(
            "status" => false,
            "message" => "Missing score."
        );

        echo json_encode($data);
        die();
    }

    delete_score($user_id, $md5hash);
    calculate_gg($user_id);

    $user = get_user($roblox_id);
    $user_gg = $user["total_gg"];

    $data = array(
        "status" => true,
        "message" => "Score deleted.",
        "total_gg" => $user_gg
    );

    echo json_encode($data);
    die();
}

?>