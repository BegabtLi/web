<?php
include('./classes/DB.php');
include('./classes/Login.php');

$receiver = htmlspecialchars($_GET['receiver']);

$isAdmin = False;
if (Login::isLoggedIn()) {
    $userid = Login::isLoggedIn();
    $username = DB::query('SELECT username FROM users WHERE id = :userid', array(':userid' => $userid))[0]['username'];
    if (DB::query('SELECT username FROM admins WHERE username=:username', array(':username' => $username))) $isAdmin = True;
} else {
    die('Not logged in');
}

if (isset($_POST['send'])) {

    if (DB::query('SELECT id FROM users WHERE id=:receiver', array(':receiver' => $receiver))) {
//        echo $_GET['receiver'];

        DB::query("INSERT INTO messages VALUES ('', :body, :sender, :receiver, 0)", array(':body' => $_POST['body'], ':sender' => $userid, ':receiver' => $receiver));
        echo "Message Sent!";
    } else {
        die('Invalid ID!');
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Send Message</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="assets/css/Footer-Dark.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.css">
    <link rel="stylesheet" href="assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="assets/css/Navigation-Clean1.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>

<div>
    <?php include dirname(__FILE__) . '/header.php' ?>
</div>

<div class="container">
    <h1>Send a Message</h1>
    <form class="form-group" action="send-message.php?receiver=<?php echo($receiver); ?>"
          method="Post">
        <textarea class="form-control" name="body" rows="15" cols="80"></textarea>
        <input class="btn btn-success " type="submit" name="send" value="Send Message">
    </form>
</div>


<?php include dirname(__FILE__) . '/footer.php' ?>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/bs-animation.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>

</body>
</html>

