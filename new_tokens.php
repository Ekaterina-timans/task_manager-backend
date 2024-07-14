<?php 
require 'db_connection.php';
require 'jwt.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenIssuer = new TokenIssuer();

    $userId = $tokenIssuer->validateToken($_COOKIE['refresh_token']);
    if (!$userId) {
        echo json_encode('Invalid refresh token');
    }

    $user_email = mysqli_real_escape_string($db_conn, trim($data->email));
    $user_psw = mysqli_real_escape_string($db_conn, trim($data->password));
  
    $SQL = "SELECT * FROM users WHERE email_user = '$user_email'";
    $result = mysqli_query($db_conn, $SQL);
    $user = mysqli_fetch_assoc($result);

    $tokens = $tokenIssuer->issueTokens($user['id'], $user['is_admin']);
    $accessToken = $tokens['accessToken'];
    $refreshToken = $tokens['refreshToken'];
    $tokenIssuer->addRefreshTokenToResponse($refreshToken);
    http_response_code(200);
    echo json_encode(array('message' => 'Tokens successfully created.', 'accessToken' => $accessToken));
}
?>