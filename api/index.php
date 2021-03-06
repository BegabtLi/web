<?php
require_once("DB.php");
require_once('Image.php');

$db = new DB("localhost", "SocialNetwork", "root", "root");

if ($_SERVER['REQUEST_METHOD'] == "GET") {

    if ($_GET['url'] == "auth") {

    } else if ($_GET['url'] == "users") {

    } else if ($_GET['url'] == "posts") {

        $token = $_COOKIE['SNID'];

        $userid = $db->query('SELECT user_id FROM login_tokens WHERE token=:token', array(':token' => sha1($token)))[0]['user_id'];

        $followingposts = $db->query('SELECT posts.id, posts.body, posts.posted_at, posts.likes, users.`username` FROM users, posts, followers
                WHERE posts.user_id = followers.user_id
                AND users.id = posts.user_id
                AND follower_id = :userid
                ORDER BY posts.likes DESC;', array(':userid' => $userid));
        $response = "[";
        foreach ($followingposts as $post) {

            $response .= "{";
            $response .= '"PostId": ' . $post['id'] . ',';
            $response .= '"PostBody": "' . $post['body'] . '",';
            $response .= '"PostedBy": "' . $post['username'] . '",';
            $response .= '"PostDate": "' . $post['posted_at'] . '",';
            $response .= '"Likes": ' . $post['likes'] . '';
            $response .= "},";


        }
        $response = substr($response, 0, strlen($response) - 1);
        $response .= "]";

        http_response_code(200);
        echo $response;

    }

} else if ($_SERVER['REQUEST_METHOD'] == "POST") {

    if ($_GET['url'] == "users") {
        $postBody = file_get_contents("php://input");
        $postBody = json_decode($postBody);

        $username = $postBody->username;
        $email = $postBody->email;
        $password = $postBody->password;
        $profileimg = $postBody->profileimg;

        if (!$db->query('SELECT username FROM users WHERE username=:username', array(':username' => $username))) {

            if (strlen($username) >= 3 && strlen($username) <= 32) {

                if (preg_match('/[a-zA-Z0-9_]+/', $username)) {

                    if (strlen($password) >= 6 && strlen($password) <= 60) {

                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

                            if (!$db->query('SELECT email FROM users WHERE email=:email', array(':email' => $email))) {

                                $db->query('INSERT INTO users VALUES (NULL, :username, :password, :email, \'0\', \'\')', array(':username' => $username, ':password' => password_hash($password, PASSWORD_BCRYPT), ':email' => $email));

                                //http_response_code(200);
                                $cstrong = True;
                                $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
                                $user_id = $db->query('SELECT id FROM users WHERE username=:username', array(':username' => $username))[0]['id'];
                                $db->query('INSERT INTO login_tokens VALUES (NULL, :token, :user_id)', array(':token' => sha1($token), ':user_id' => $user_id));
                                setcookie("SNID", $token, time() + 60 * 60 * 24 * 7, '/', NULL, NULL, TRUE);
                                setcookie("SNID_", '1', time() + 60 * 60 * 24 * 3, '/', NULL, NULL, TRUE);
                                if (strlen($profileimg) > 70) {
                                    $newImg = Image::uploadAvatar($profileimg);
                                } else {
                                    $newImg = 'http://s3.amazonaws.com/37assets/svn/765-default-avatar.png';
                                }
                                $db->query("UPDATE users SET profileimg=:profileimg WHERE id=:id", array(':id' => $user_id, ':profileimg' => $newImg));
                                echo 'SUCESS';
                                // exit();
                            } else {
                                echo '{ "Error": "Email in use!" }';
                                http_response_code(409);
                            }
                        } else {
                            echo '{ "Error": "Invalid Email!" }';
                            http_response_code(409);
                        }
                    } else {
                        echo '{ "Error": "Invalid Password!" }';
                        http_response_code(409);
                    }
                } else {
                    echo '{ "Error": "Invalid Username!" }';
                    http_response_code(409);
                }
            } else {
                echo '{ "Error": "Invalid Username!" }';
                http_response_code(409);
            }

        } else {
            echo '{ "Error": "User exists!" }';
            http_response_code(409);
        }


    }

    if ($_GET['url'] == "auth") {

        $postBody = file_get_contents("php://input");
        $postBody = json_decode($postBody);

        $username = $postBody->username;
        $password = $postBody->password;

        if ($db->query('SELECT username FROM users WHERE username=:username', array(':username' => $username))) {
            if (password_verify($password, $db->query('SELECT password FROM users WHERE username=:username', array(':username' => $username))[0]['password'])) {
                $cstrong = True;
                $token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
                $user_id = $db->query('SELECT id FROM users WHERE username=:username', array(':username' => $username))[0]['id'];
                $db->query('INSERT INTO login_tokens VALUES (NULL, :token, :user_id)', array(':token' => sha1($token), ':user_id' => $user_id));
                echo $username;


            } else {
                echo 'Error';
                http_response_code(401);
            }
        } else {
            echo 'Error';
            http_response_code(401);
        }


    }

} else if ($_SERVER['REQUEST_METHOD'] == "DELETE") {

    if ($_GET['url'] == "auth") {
        if (isset($_GET['token'])) {
            if ($db->query("SELECT token FROM login_tokens WHERE token=:token", array(':token' => sha1($_GET['token'])))) {
                $db->query('DELETE FROM login_tokens WHERE token=:token', array(':token' => sha1($_GET['token'])));
                echo '{ "Status": "Success" }';
                http_response_code(200);
            } else {
                echo '{ "Error": "Invalid token" }';
                http_response_code(400);
            }
        } else {
            echo '{ "Error": "Malformed request" }';
            http_response_code(400);
        }
    }
} else {

    http_response_code(405);
}
?>
