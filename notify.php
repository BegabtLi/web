<?php
include('./classes/DB.php');
include('./classes/Login.php');

$isAdmin = False;
if (Login::isLoggedIn()) {
    $userid = Login::isLoggedIn();
    $username = DB::query('SELECT username FROM users WHERE id = :userid', array(':userid' => $userid))[0]['username'];
    if (DB::query('SELECT username FROM admins WHERE username=:username', array(':username' => $username))) $isAdmin = True;
} else {
    die('Not logged in');
}


?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifcations</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="assets/css/Footer-Dark.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.css">
    <link rel="stylesheet" href="assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="assets/css/Navigation-Clean1.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.1/semantic.css">
    <link rel="stylesheet" href="assets/css/untitled.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link href="https://fonts.googleapis.com/css?family=Gaegu" rel="stylesheet">
    <style>
        body {
            padding: 1em;
        }

        .ui.menu {
            margin: 3em 0em;
        }

        .ui.menu:last-child {
            margin-bottom: 110px;
        }
    </style>
</head>
<body>

<div>
    <?php include dirname(__FILE__) . '/header.php' ?>
</div>


<div class="container">
    <p></p>
    <div class="ui huge header">Notifications</div>
    <hr>
    <br>
    <div class="ui main text container segment">
        <?php
        $notifications = DB::query('SELECT * FROM notifications WHERE receiver=:userid ORDER BY id DESC', array(':userid' => $userid));
        foreach ($notifications as $n) {
            if ($n['type'] == 1) {
                $senderName = DB::query('SELECT username FROM users WHERE id=:senderid', array(':senderid' => $n['sender']))[0]['username'];
                if ($n['extra'] == "") {
                    echo "<p>You got a notification!</p><hr/>";
                } else {
                    $extra = json_decode($n['extra']);
                    echo "<p>" . $senderName . " mentioned you in a post! - " . $extra->postbody . "</p><hr/>";
                }
            } else if ($n['type'] == 2) {
                $senderName = DB::query('SELECT username FROM users WHERE id=:senderid', array(':senderid' => $n['sender']))[0]['username'];
                echo "<p>" . $senderName . " liked your post!</p><hr/>";
            } else if ($n['type'] == 3) {
                $senderName = DB::query('SELECT username FROM users WHERE id=:senderid', array(':senderid' => $n['sender']))[0]['username'];
                $postLink = 'profile.php?username=' . $username;
                echo "<p>" . $senderName . " want to comment your <a href='" . $postLink . "'>post</a>.</p><hr/>";
            } else if ($n['type'] == 4) {
                $senderName = DB::query('SELECT username FROM users WHERE id=:senderid', array(':senderid' => $n['sender']))[0]['username'];
                $profileLink = 'profile.php?username=' . $senderName;
                echo "<p><a href=" . $profileLink . ">" . $senderName . "</a> want to be your friend.</p><hr/>";

            }
        }
        ?>
    </div>
</div>

<?php include dirname(__FILE__) . '/footer.php' ?>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/bs-animation.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
</body>
</html>