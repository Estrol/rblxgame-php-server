<?php
$mysql_db = "";
$mysql_host = "localhost";
$mysql_user = "root";
$mysql_password = "";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$db = new mysqli($mysql_host, $mysql_user, $mysql_password, $mysql_db);

$db->query("CREATE DATABASE IF NOT EXISTS roblox");
$db->query("USE roblox");

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

$db->query(
    "CREATE TABLE IF NOT EXISTS users(
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        is_banned BOOLEAN DEFAULT FALSE,
        ban_reason VARCHAR(255) DEFAULT NULL,
        roblox_id BIGINT UNSIGNED NOT NULL,
        total_gg BIGINT UNSIGNED NOT NULL,
        reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )"
);

$db->query(
    "CREATE TABLE IF NOT EXISTS auth(
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        auth_code VARCHAR(255) NOT NULL,
        reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )"
);

$db->query(
    "CREATE TABLE IF NOT EXISTS scores(
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        md5hash VARCHAR(255) NOT NULL,

        user_id INT(6) UNSIGNED NOT NULL,
        score BIGINT UNSIGNED NOT NULL,
        marvelous INT(6) UNSIGNED NOT NULL,
        perfect INT(6) UNSIGNED NOT NULL,
        great INT(6) UNSIGNED NOT NULL,
        good INT(6) UNSIGNED NOT NULL,
        bad INT(6) UNSIGNED NOT NULL,
        miss INT(6) UNSIGNED NOT NULL,

        max_combo BIGINT UNSIGNED NOT NULL,
        gameplaygains BIGINT UNSIGNED NOT NULL,

        reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP   
    )"
);

$db->query(
    "CREATE TABLE IF NOT EXISTS leaderboard(
        roblox_id BIGINT UNSIGNED NOT NULL, 
        score BIGINT UNSIGNED NOT NULL,
        gameplaygains BIGINT UNSIGNED NOT NULL
    )"
);

function login($authorization)
{
    global $db;

    $stmt = $db->prepare("SELECT * FROM auth WHERE auth_code = ?");
    $stmt->bind_param("s", $authorization);
    $stmt->execute();

    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows == 0) {
        return false;
    }

    return true;
}

function get_user($roblox_id)
{
    global $db;

    $stmt = $db->prepare("SELECT * FROM users WHERE roblox_id = ?");
    $stmt->bind_param("i", $roblox_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows == 0) {
        return null;
    }

    $user = $result->fetch_assoc();
    return $user;
}

function find_user_id($roblox_id)
{
    global $db;

    $user = get_user($roblox_id);
    if (!isset($user)) {
        return null;
    }

    return $user["id"];
}

function get_score($user_id, $md5hash)
{
    global $db;

    $stmt = $db->prepare("SELECT * FROM scores WHERE user_id = ? AND md5hash = ?");
    $stmt->bind_param("is", $user_id, $md5hash);

    $stmt->execute();

    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows == 0) {
        return null;
    }

    $score = $result->fetch_assoc();
    return $score;
}

function put_score($user_id, $md5hash, $score, $marvelous, $perfect, $great, $good, $bad, $miss, $max_combo, $gameplaygains)
{
    global $db;

    $stmt = $db->prepare("INSERT INTO scores (md5hash, user_id, score, marvelous, perfect, great, good, bad, miss, max_combo, gameplaygains) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sisiiiiiiii", $md5hash, $user_id, $score, $marvelous, $perfect, $great, $good, $bad, $miss, $max_combo, $gameplaygains);
    $stmt->execute();

    $stmt->close();
}

function update_score($user_id, $md5hash, $score, $marvelous, $perfect, $great, $good, $bad, $miss, $max_combo, $gameplaygains)
{
    global $db;

    $stmt = $db->prepare("UPDATE scores SET score = ?, marvelous = ?, perfect = ?, great = ?, good = ?, bad = ?, miss = ?, max_combo = ?, gameplaygains = ? WHERE user_id = ? AND md5hash = ?");
    $stmt->bind_param("iiiiiiiiiis", $score, $marvelous, $perfect, $great, $good, $bad, $miss, $max_combo, $gameplaygains, $user_id, $md5hash);
    $stmt->execute();

    $stmt->close();
}

function delete_score($user_id, $md5hash)
{
    global $db;

    $stmt = $db->prepare("DELETE FROM scores WHERE user_id = ? AND md5hash = ?");
    $stmt->bind_param("is", $user_id, $md5hash);
    $stmt->execute();

    $stmt->close();
}

function calculate_gg($user_id)
{
    global $db;

    $stmt = $db->prepare("SELECT * FROM scores WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $stmt->close();

    $total_gg = 0;
    $weight = 1.0;
    while ($score = $result->fetch_assoc()) {
        $total_gg += $score["gameplaygains"] * $weight;
        $weight *= 0.95;
    }

    $stmt = $db->prepare("UPDATE users SET total_gg = ? WHERE id = ?");
    $stmt->bind_param("ii", $total_gg, $user_id);
    $stmt->execute();

    $stmt->close();
}

?>