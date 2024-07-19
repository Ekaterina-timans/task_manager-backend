<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    http_response_code(200);
    echo json_encode(array("message" => "Logged out successfully"));
}
?>