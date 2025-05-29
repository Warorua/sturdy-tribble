user_agent TEXT,

<?php
// SQL to create the request_logs table:
/*
CREATE TABLE request_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    request_method VARCHAR(10),
    get_data TEXT,
    post_data TEXT,
    raw_input TEXT,
    client_ip VARCHAR(45),
    referer TEXT,
    request_uri TEXT,
    timestamp DATETIME
);
*/
// Include PDO connection
require_once __DIR__ . '/includes/conn.php';

// Function to get client IP address
function getClientIp() {
    $ip = '';
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

// Collect request data
$request_method = $_SERVER['REQUEST_METHOD'];
$get_data = json_encode($_GET);
$post_data = json_encode($_POST);
$raw_input = file_get_contents('php://input');
$client_ip = getClientIp();
$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$timestamp = date('Y-m-d H:i:s');

// Prepare SQL (make sure you have created the table `request_logs`)
$sql = "INSERT INTO request_logs 
    (request_method, get_data, post_data, raw_input, client_ip, user_agent, referer, request_uri, timestamp)
    VALUES 
    (:request_method, :get_data, :post_data, :raw_input, :client_ip, :user_agent, :referer, :request_uri, :timestamp)";

$stmt = $conn->prepare($sql);
$stmt->execute([
    ':request_method' => $request_method,
    ':get_data'       => $get_data,
    ':post_data'      => $post_data,
    ':raw_input'      => $raw_input,
    ':client_ip'      => $client_ip,
    ':user_agent'     => $user_agent,
    ':referer'        => $referer,
    ':request_uri'    => $request_uri,
    ':timestamp'      => $timestamp
]);

// Optional: Respond with a simple message
echo "Request logged.";
?>