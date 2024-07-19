<?php
require 'db_connection.php';
require 'jwt.php';

$headers = getallheaders();
$token = $headers['Authorization'];

if(isset($token)) {
    $tokenIssuer = new TokenIssuer();
    $tokenData = $tokenIssuer->validateAccessToken($token);
    $user_id = $tokenData->id;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"));
        if (isset($data->name) && isset($data->key) && !empty(trim($data->name)) && !empty(trim($data->key))) {
            $group_name = mysqli_real_escape_string($db_conn, trim($data->name));
            $group_key = mysqli_real_escape_string($db_conn, trim($data->key));
    
            // Создание группы
            $create_group = mysqli_query($db_conn, "INSERT INTO groups (name_group, key_group) VALUES ('$group_name', '$group_key')");
            if ($create_group) {
                $group_id = mysqli_insert_id($db_conn);
    
                // Добавление пользователя в группу как администратора
                $is_admin = 1;
                $add_user_group = mysqli_query($db_conn, "INSERT INTO user_groups (user_id, group_id, is_admin) VALUES ('$user_id', '$group_id', '$is_admin')");
                if ($add_user_group) {
                    // Обновление токенов с новым значением isAdmin
                    $tokens = $tokenIssuer->issueTokens($user_id, $is_admin);
                    $accessToken = $tokens['accessToken'];
                    $refreshToken = $tokens['refreshToken'];
                    $tokenIssuer->addRefreshTokenToResponse($refreshToken);
                    
                    http_response_code(200);
                    echo json_encode(array('message' => 'Group successfully created.'));
                } else {
                    http_response_code(500);
                    echo json_encode(array('message' => 'Failed to create group.'));
                }
            } else {
                http_response_code(500);
                echo json_encode(array('message' => 'Failed to create group.'));
            }
        } else {
            http_response_code(400);
            echo json_encode(array('message' => 'Please provide group name and key.'));
        }
    }
}
?>