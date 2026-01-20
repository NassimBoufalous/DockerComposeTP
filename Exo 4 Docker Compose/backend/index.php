<?php


header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Max-Age: 86400");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
$method = $_SERVER["REQUEST_METHOD"];

$dbName = getenv("DB_NAME");
$dbUser = getenv("DB_USER");
$dbHost = getenv("DB_HOST");
$dbPort = getenv("DB_PORT");
$dbPass = getenv("DB_PASSWORD");


if ($dbPass === false || $dbPass === "") {
    http_response_code(500);
    exit;
}

try {
    $dsn = "pgsql:host={$dbHost};port={$dbPort};dbname={$dbName}";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username TEXT NOT NULL,
            password TEXT NOT NULL
        )
    ");
} catch (Exception $e) {
    http_response_code(500);
    header("Content-Type: application/json");
    echo json_encode(["error" => "DB connection/init failed", "details" => $e->getMessage()]);
    exit;
}

function readJsonBody(): array {
    $raw = file_get_contents("php://input");
    if ($raw === false || $raw === "") return [];
    $d = json_decode($raw, true);
    return is_array($d) ? $d : [];
}

function jsonOut($data, int $code = 200): void {
    http_response_code($code);
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($data);
    exit;
}


if ($path === "/api/users" && $method === "GET") {
    $st = $pdo->query("SELECT id, username, password FROM users ORDER BY id DESC");
    jsonOut($st->fetchAll(PDO::FETCH_ASSOC));
}

if (preg_match("#^/api/users/(\\d+)$#", $path, $m) && $method === "GET") {
    $id = (int)$m[1];
    $st = $pdo->prepare("SELECT id, username, password FROM users WHERE id = ?");
    $st->execute([$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if (!$row) jsonOut(["error" => "Not Found"], 404);
    jsonOut($row);
}

if ($path === "/api/users" && $method === "POST") {
    $b = readJsonBody();
    $u = $b["username"] ?? "";
    $p = $b["password"] ?? "";
    $st = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?) RETURNING id");
    $st->execute([$u, $p]);
    $id = (int)$st->fetchColumn();
    jsonOut(["id" => $id, "username" => $u, "password" => $p], 201);
}

if (preg_match("#^/api/users/(\\d+)$#", $path, $m) && $method === "PUT") {
    $id = (int)$m[1];
    $b = readJsonBody();
    $u = $b["username"] ?? "";
    $p = $b["password"] ?? "";
    $st = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
    $st->execute([$u, $p, $id]);
    if ($st->rowCount() === 0) jsonOut(["error" => "Not Found"], 404);
    jsonOut(["id" => $id, "username" => $u, "password" => $p]);
}

if (preg_match("#^/api/users/(\\d+)$#", $path, $m) && $method === "DELETE") {
    $id = (int)$m[1];
    $st = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $st->execute([$id]);
    if ($st->rowCount() === 0) jsonOut(["error" => "Not Found"], 404);
    jsonOut(["ok" => true]);
}

jsonOut(["error" => "Not Found"], 404);
