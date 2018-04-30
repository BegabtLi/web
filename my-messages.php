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

if (isset($_GET['mid'])) {
    $message = DB::query('SELECT * FROM messages WHERE id=:mid AND receiver=:receiver OR sender=:sender', array(':mid' => $_GET['mid'], ':receiver' => $userid, ':sender' => $userid))[0];
    echo '<h1>View Message</h1>';
    echo htmlspecialchars($message['body']);
    echo '<hr />';

    if ($message['sender'] == $userid) {
        $id = $message['receiver'];
    } else {
        $id = $message['sender'];
    }
    DB::query('UPDATE messages SET `read`=1 WHERE id=:mid', array(':mid' => $_GET['mid']));
    ?>
    <form action="send-message.php?receiver=<?php echo $id; ?>" method="post">
        <textarea name="body" rows="8" cols="80"></textarea>
        <input type="submit" name="send" value="Send Message">
    </form>
    <?php
} else {

?>

<!DOCTYPE html>
<html>
<head>
    <title>Messages</title>
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
    <div class="ui huge header">Messages</div>
    <hr>
    <br>
    <div class="ui main text container segment">
        <?php

        $senders = DB::query('SELECT DISTINCT sender FROM messages WHERE receiver = :receiver', array(':receiver' => $userid));
        foreach ($senders as $sender) {
            $senderid = $sender['sender'];
            $messages = DB::query('SELECT messages.*, users.username FROM messages, users WHERE receiver=:receiver AND sender=:sender AND users.id = :sender', array(':receiver' => $userid, ':sender' => $senderid));
            foreach ($messages as $message) {

                if (strlen($message['body']) > 20) {
                    $m = substr($message['body'], 0, 20) . " ...";
                } else {
                    $m = $message['body'];
                }

                if ($message['read'] == 0) {
                    echo "<p><a href='my-messages.php?mid=" . $message['id'] . "'><strong>" . $m . "</strong></a> sent by " . $message['username'] . '</p><hr />';
                } else {
                    echo "<p><a href='my-messages.php?mid=" . $message['id'] . "'><strong>" . $m . "</strong></a> sent by " . $message['username'] . '</p><hr />';
                }

            }

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

