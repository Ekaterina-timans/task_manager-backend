<?php
require 'db_connection.php';
require 'jwt.php';

$headers = getallheaders();
$token = $headers['Authorization'];

if (isset($token)) {
    $tokenIssuer = new TokenIssuer();
    $tokenData = $tokenIssuer->validateAccessToken($token);
    $user_id = $tokenData->id;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $data = json_decode(file_get_contents("php://input"));

        if (isset($data->name) && isset($data->key) && !empty(trim($data->name)) && !empty(trim($data->key))) {
            $group_name = mysqli_real_escape_string($db_conn, trim($data->name));
            $group_key = mysqli_real_escape_string($db_conn, trim($data->key));

            // Проверка существования группы
            $group_query = "SELECT id FROM groups WHERE name_group = '$group_name' AND key_group = '$group_key'";
            $group_result = mysqli_query($db_conn, $group_query);

            if (mysqli_num_rows($group_result) > 0) {
                $group = mysqli_fetch_assoc($group_result);
                $group_id = $group['id'];

                // Проверка, состоит ли пользователь уже в этой группе
                $user_group_query = "SELECT is_admin FROM user_groups WHERE user_id = '$user_id' AND group_id = '$group_id'";
                $user_group_result = mysqli_query($db_conn, $user_group_query);

                if (mysqli_num_rows($user_group_result) == 0) {
                    // Пользователь не в группе, добавляем его
                    $is_admin = 0; // По умолчанию не администратор
                    $add_user_group = mysqli_query($db_conn, "INSERT INTO user_groups (user_id, group_id, is_admin) VALUES ('$user_id', '$group_id', '$is_admin')");

                    http_response_code(200);
                    echo json_encode(array('message' => 'You have successfully joined the group, and you are not an admin.'));
                }
                else {
                    // Если пользователь уже состоит в группе, проверяем его статус
                    $user_group = mysqli_fetch_assoc($user_group_result);
                    $is_admin = $user_group['is_admin'];
                    
                    // Обновляем токены с правильным значением isAdmin
                    $tokens = $tokenIssuer->issueTokens($user_id, $is_admin);
                    $accessToken = $tokens['accessToken'];
                    $refreshToken = $tokens['refreshToken'];
                    $tokenIssuer->addRefreshTokenToResponse($refreshToken);

                    http_response_code(200);
                    echo json_encode(array('message' => 'You are already a member of this group.', 'isAdmin' => $is_admin));
                }
            }
            else {
                http_response_code(404);
                echo json_encode(array('message' => 'Group not found or incorrect key.'));
            }
        }
        else {
            http_response_code(400);
            echo json_encode(array('message' => 'Please provide group name and key.'));
        }
    }
}
else {
    http_response_code(401);
    echo json_encode(array('message' => 'Token is missing.'));
}
?>