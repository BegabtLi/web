<?php
include('./classes/DB.php');
include('./classes/Login.php');

$isAdmin = False;
if (Login::isLoggedIn()) {
    $userid = Login::isLoggedIn();
    $username = DB::query('SELECT username FROM users WHERE id = :userid', array(':userid' => $userid))[0]['username'];
    if (DB::query('SELECT username FROM admins WHERE username=:username', array(':username' => $username))) $isAdmin = True;
} else {
    die("Not logged in.");
}

if (isset($_POST['confirm'])) {

    if (isset($_POST['alldevices'])) {

        DB::query('DELETE FROM login_tokens WHERE user_id=:userid', array(':userid' => Login::isLoggedIn()));

    } else {
        if (isset($_COOKIE['SNID'])) {
            DB::query('DELETE FROM login_tokens WHERE token=:token', array(':token' => sha1($_COOKIE['SNID'])));
        }
        setcookie('SNID', '1', time() - 3600);
        setcookie('SNID_', '1', time() - 3600);
    }
    header("Location:index.php");

}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Logout</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="assets/css/Footer-Dark.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.css">
    <link rel="stylesheet" href="assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="assets/css/Navigation-Clean1.css">
    <link rel="stylesheet" href="assets/css/styles.css">
<!--    <link rel="stylesheet" href="assets/css/message.css">-->
</head>

<body>

<div>
    <?php include dirname(__FILE__).'/header.php' ?>
</div>

<div class="container">
    <h1>Logout of your Account ?</h1>
    <hr>
    <br>
    <form action="logout.php" method="Post">
        <input class="btn btn-danger" type="submit" name="confirm" value="Confirm">
    </form>
</div>

<?php include dirname(__FILE__).'/footer.php' ?>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/bs-animation.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
</body>
</html>

