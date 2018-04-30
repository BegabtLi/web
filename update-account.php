<?php

include('classes/DB.php');

$userid = $_GET['userid'];

if (isset($_POST['updateaccount'])) {

    if (DB::query('SELECT username FROM users WHERE id = :userid', array(':userid' => $userid))) {

        $username = $_POST['username'];
        $password = $_POST['password'];
        $email = $_POST['email'];

        if (strlen($username) >= 3 && strlen($username) <= 32) {

            if (preg_match('/[a-zA-Z0-9_]+/', $username)) {

                if (strlen($password) >= 6 && strlen($password) <= 60) {

                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

                        if (!DB::query('SELECT email FROM users WHERE email=:email', array(':email' => $email))) {

                            DB::query('UPDATE users SET username = :username, password = :password,email = :email WHERE users.id = :userid', array(':username' => $username, ':password' => password_hash($password, PASSWORD_BCRYPT), ':email' => $email, ':userid' => $userid));
                            echo "Update User Account Successful!";
                        } else {
                            echo 'Email in use!';
                        }
                    } else {
                        echo 'Invalid email!';
                    }
                } else {
                    echo 'Invalid password!';
                }
            } else {
                echo 'Invalid username';
            }
        } else {
            echo 'Invalid username';
        }

    } else {
        echo 'User dost not exist!';
    }


}


?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SocialNetwork</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
<div class="login-clean">
    <form method="post" action="update-account.php?userid=<?php echo($userid); ?>">
        <h2 class="sr-only">Update User Account</h2>
        <div class="illustration"><i class="icon ion-android-refresh"></i></div>
        <div class="form-group">
            <input class="form-control" id="username" type="text" name="username" placeholder="Username">
        </div>
        <div class="form-group">
            <input class="form-control" id="email" type="email" name="email" placeholder="Email">
        </div>
        <div class="form-group">
            <input class="form-control" id="password" type="password" name="password" placeholder="Password">
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary btn-block" name="updateaccount" value="Update Account">
        </div>
    </form>
</div>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript">

    function validateUsername() {
        var username = $("#username").val();
        var intRegex = /^[A-Za-z0-9]+$/;
        $("span").remove("#span_username");
        if (username.length < 1) ;
        else if (!intRegex.test(username))
            $("#username").after("<span id='span_username' class='info'>username should be only numbers or albhabetics.</span>");
        else
            $("#username").after("<span id='span_username' class='info'>OK</span>");
    }

    function validatePassword() {
        var passwd = $("#password").val();
        $("span").remove("#span_password");
        if (passwd.length < 1) ;

        else if (passwd.length < 8)
            $("#password").after("<span id='span_password' class='info'>Password should be at least 8 characters.</span>");
        else
            $("#password").after("<span id='span_password' class='info'>OK</span>");
    }

    $("#username").focusout(function () {
        var intRegex = /^[A-Za-z0-9]+$/;
        var username = $("#username").val();
        $("span").remove("#span_username");
        if (username.length < 1) ;

        else if (!(intRegex.test(username))) {
            $("#username").after("<span id='span_username' class='error'>Error</span>");
        }
        else {
            $("#username").after("<span id='span_username' class='ok'>OK</span>");
        }
    });

    $("#username").focusin(validateUsername);
    $("#username").change(validateUsername);
    $("#username").keyup(validateUsername);

    $("#password").focusout(function () {
        var passwd = $("#password").val();
        $("span").remove("#span_password");
        if (passwd.length < 1) ;

        else if (passwd.length < 8)
            $("#password").after("<span id='span_password' class='error'>Error</span>");
        else
            $("#password").after("<span id='span_password' class='ok'>OK</span>");
    });


    $("#password").focusin(validatePassword);
    $("#password").change(validatePassword);
    $("#password").keyup(validatePassword);
</script>
</body>

</html>





