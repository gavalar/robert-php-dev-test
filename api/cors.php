<?php
// Add CORS headers at the top of the file to allow cross-origin requests
header("Access-Control-Allow-Origin: http://localhost:3000"); // Allow React app running on localhost:3000
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS"); // Allowed HTTP methods
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allowed headers

// If it's an OPTIONS request (pre-flight check), send a response and exit
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    // Respond with 200 OK for pre-flight requests
    header("HTTP/1.1 200 OK");
    exit();
}
