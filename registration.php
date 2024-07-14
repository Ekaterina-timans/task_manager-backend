<?php
require 'db_connection.php';
require 'jwt.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if(isset($data->email) && isset($data->password) && !empty(trim($data->email)) && !empty(trim($data->password))) {
        $user_email = mysqli_real_escape_string($db_conn, trim($data->email));
        $user_psw = mysqli_real_escape_string($db_conn, trim($data->password));

        $sql = "SELECT id FROM users WHERE email_user='$user_email'";
        $result = $db_conn->query($sql);

        if ($result->num_rows > 0) {
            http_response_code(400);
            echo json_encode(array("message" => "Username already exists"));
        }
        else {
            $hashed_password = password_hash($user_psw, PASSWORD_DEFAULT);
            $isAdmin = 0;

            $add = mysqli_query($db_conn,"insert into users (email_user, password_user, is_admin) values('$user_email', '$hashed_password', '$isAdmin')");
            $tokenIssuer = new TokenIssuer();

            if($add) {
                $userId = mysqli_insert_id($db_conn);
            $tokens = $tokenIssuer->issueTokens($userId, $isAdmin);
            $accessToken = $tokens['accessToken'];
            $refreshToken = $tokens['refreshToken'];
            $tokenIssuer->addRefreshTokenToResponse($refreshToken);
            http_response_code(200);
            echo json_encode(array('message' => 'User created.', 'accessToken' => $accessToken));
            }
            else {
                echo json_encode('Failed to create user.');
            }
        }
    }
    else {
        http_response_code(400);
        echo json_encode('Please fill all the required fields!');
        return;
    }
}
?>