<?php 
require 'db_connection.php';
require 'jwt.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenIssuer = new TokenIssuer();

    // Получение данных из запроса
    $data = json_decode(file_get_contents("php://input"));
    if (isset($data->refreshToken) && isset($data->isAdmin)) {
        $refresh_token = mysqli_real_escape_string($db_conn, trim($data->refreshToken));
        $is_admin = ($data->isAdmin) ? 1 : 0;

        // Валидация refresh токена
        $userId = $tokenIssuer->validateRefreshToken($refresh_token);
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid refresh token']);
            return;
        }

        $tokens = $tokenIssuer->issueTokens($userId, $is_admin);
        $accessToken = $tokens['accessToken'];
        $refreshToken = $tokens['refreshToken'];
        $tokenIssuer->addRefreshTokenToResponse($refreshToken);
        http_response_code(200);
        echo json_encode(array('message' => 'Tokens successfully created.', 'accessToken' => $accessToken));
    }
    else {
        http_response_code(400);
        echo json_encode('Not all data received');
        return;
    }
}
?>