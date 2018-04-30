<?php
include('./classes/DB.php');
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.5.2/animate.min.css">
    <link rel="stylesheet" href="assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link class="jsbin" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/base/jquery-ui.css"
          rel="stylesheet" type="text/css"/>
    <script class="jsbin" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
    <script class="jsbin" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.0/jquery-ui.min.js"></script>
</head>

<body>
<script>
    function readURL(input) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                $('#avatar').attr('src', e.target.result);
                // alert($('#avatar').attr('src'));

            };
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>

<div class="login-clean">
    <form method="post">
        <h2 class="sr-only">Create Account</h2>

        <div class="form-group photo">
            <label for="file-input">
                <img id="avatar" src="http://s3.amazonaws.com/37assets/svn/765-default-avatar.png"/>
            </label>
            <input id="file-input" type="file" name="avatar" onchange="readURL(this);"/>
        </div>
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
            <button class="btn btn-primary btn-block" id="ca" type="button" name="createaccount"
                    data-bs-hover-animate="shake">Create Account
            </button>
        </div>
        <a href="login.php" class="forgot">Already got an account? Click here!</a></hr>
        <a href="index.php" class="forgot">View posts without Log in</a>
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


    function emailValidate() {
        $("span").remove("#span-email");
        if ($("#email").val().length < 1)
            $("#email").after("<span id='span-email' class='error'>Email is Mandatory</span>");
        else if (!(/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/.test($("#email").val())))
            $("#email").after("<span id='span-email' class='info'>Please input valid email address!</span>");
        else
            $("#email").after("<span id='span-email' class='info'>OK</span>");
    }

    $("#email").focus(function () {
        emailValidate();
    });
    $("#email").change(function () {
        emailValidate();
    });
    $("#email").keyup(function () {
        emailValidate();
    });

    $('#ca').click(function (event) {
        $.ajax({

            type: "POST",
            url: "api/users",
            processData: false,
            contentType: "application/json",
            data: '{ "username": "' + $("#username").val() + '", "email": "' + $("#email").val() + '", "password": "' + $("#password").val() + '", "profileimg": "' + $('#avatar').attr('src').substring($('#avatar').attr('src').indexOf('base64') + 7) + '" }',
            success: function (r) {

                if (r == "SUCESS") {
                    window.location = "index.php";
                }

            },
            error: function (r) {
                alert(r.responseText)
                setTimeout(function () {
                    $('[data-bs-hover-animate]').removeClass('animated ' + $('[data-bs-hover-animate]').attr('data-bs-hover-animate'));
                }, 2000)
                $('[data-bs-hover-animate]').addClass('animated ' + $('[data-bs-hover-animate]').attr('data-bs-hover-animate'))
                console.log(r)
            }

        });

    });
</script>
</body>

</html>
