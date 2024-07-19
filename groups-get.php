<?php
require 'db_connection.php';
require 'jwt.php';

$headers = getallheaders();
$token = $headers['Authorization'];

if(isset($token)) {
    $tokenIssuer = new TokenIssuer();
    $tokenData = $tokenIssuer->validateToken($token);
    $user_id = $tokenData->id;

    if($user_id) {
        $sql = "SELECT g.name_group FROM groups g 
                JOIN user_groups ug ON g.id = ug.group_id 
                WHERE ug.user_id = $user_id";
        $result = mysqli_query($db_conn, $sql);
        if(mysqli_num_rows($result) > 0) {
            $groups = array();

            while($row = mysqli_fetch_assoc($result)) {
                $group = array(
                    'name' => $row['name_group'],
                );
                $groups[] = $group;
            }
            echo json_encode($groups);
        }
        else {
            echo json_encode(array('message' => 'No groups found for this user.'));
        }
    }
    else {
        echo json_encode ('Invalid token');
    }
}
else {
    echo json_encode('Token is missing');
}
?>