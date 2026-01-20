<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") { http_response_code(204); exit; }

$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

function tor_get_json(string $url): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 25,
        CURLOPT_PROXY => "tor:9050",
        CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5_HOSTNAME,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HTTPHEADER => ["Accept: application/json"],
    ]);
    $out = curl_exec($ch);
    if ($out === false) {
        $err = curl_error($ch);
        curl_close($ch);
        http_response_code(500);
        echo json_encode(["error" => "curl failed", "details" => $err]);
        exit;
    }
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    $data = json_decode($out, true);
    if ($code >= 400 || !is_array($data)) {
        http_response_code(502);
        echo json_encode(["error" => "bad response", "status" => $code, "raw" => $out]);
        exit;
    }
    return $data;
}

if ($path === "/api/users") {
    header("Content-Type: application/json; charset=utf-8");

    $json = tor_get_json("https://randomuser.me/api/?results=10");
    $users = [];

    foreach (($json["results"] ?? []) as $u) {
        $users[] = [
            "name" => trim(($u["name"]["first"] ?? "") . " " . ($u["name"]["last"] ?? "")),
            "photo" => $u["picture"]["large"] ?? ""
        ];
    }

    echo json_encode($users);
    exit;
}

http_response_code(404);
echo "Not Found";
