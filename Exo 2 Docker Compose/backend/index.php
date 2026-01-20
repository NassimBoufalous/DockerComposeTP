<?php

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$dbPath = getenv("DB_PATH") ?: __DIR__ . "/app.db";

try {
    $pdo = new PDO("sqlite:" . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL,
            password TEXT NOT NULL
        )
    ");
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "DB init failed", "details" => $e->getMessage()]);
    exit;
}

$method = $_SERVER["REQUEST_METHOD"];
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

function readJsonBody(): array {
    $raw = file_get_contents("php://input");
    if ($raw === false || $raw === "") return [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

function jsonOut($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data);
    exit;
}



if ($path === "/api/users" && $method === "GET") {
    $stmt = $pdo->query("SELECT id, username, password FROM users ORDER BY id DESC");
    jsonOut($stmt->fetchAll(PDO::FETCH_ASSOC));
}

if (preg_match("#^/api/users/(\d+)$#", $path, $m) && $method === "GET") {
    $id = (int)$m[1];
    $stmt = $pdo->prepare("SELECT id, username, password FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) jsonOut(["error" => "Not Found"], 404);
    jsonOut($row);
}

if ($path === "/api/users" && $method === "POST") {
    $b = readJsonBody();
    $username = $b["username"] ?? "";
    $password = $b["password"] ?? "";

    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $password]);

    jsonOut(["id" => (int)$pdo->lastInsertId(), "username" => $username, "password" => $password], 201);
}

if (preg_match("#^/api/users/(\d+)$#", $path, $m) && $method === "PUT") {
    $id = (int)$m[1];
    $b = readJsonBody();
    $username = $b["username"] ?? "";
    $password = $b["password"] ?? "";

    $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE id = ?");
    $stmt->execute([$username, $password, $id]);

    if ($stmt->rowCount() === 0) jsonOut(["error" => "Not Found"], 404);
    jsonOut(["id" => $id, "username" => $username, "password" => $password]);
}

if (preg_match("#^/api/users/(\d+)$#", $path, $m) && $method === "DELETE") {
    $id = (int)$m[1];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    if ($stmt->rowCount() === 0) jsonOut(["error" => "Not Found"], 404);
    jsonOut(["ok" => true]);
}

jsonOut(["error" => "Not Found"], 404);
