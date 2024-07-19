<?php
require 'db_connection.php';
require 'jwt.php';

$data = json_decode(file_get_contents("php://input"));
if(isset($data->email) && isset($data->password) && !empty(trim($data->email)) && !empty(trim($data->password))) {
    $user_email = mysqli_real_escape_string($db_conn, trim($data->email));
    $user_psw = mysqli_real_escape_string($db_conn, trim($data->password));

    $SQL = "SELECT * FROM users WHERE email_user = '$user_email'";
    $result = mysqli_query($db_conn, $SQL);
    $user = mysqli_fetch_assoc($result);
    $tokenIssuer = new TokenIssuer();

    if ($user && password_verify($user_psw, $user['password_user'])) {
        $isAdmin = 0;
        $tokens = $tokenIssuer->issueTokens($user['id'], $isAdmin);
        $accessToken = $tokens['accessToken'];
        $refreshToken = $tokens['refreshToken'];
        $tokenIssuer->addRefreshTokenToResponse($refreshToken);
        echo json_encode(array('message' => 'User authenticated.', 'accessToken' => $accessToken));
    } else {
        echo json_encode('Authentication failed. Incorrect email or password.');
    }
} else {
    echo json_encode('Please provide email and password.');
    return;
}
?>