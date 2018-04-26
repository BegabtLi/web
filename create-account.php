<!--<h1>Register</h1>-->
<!--<form action="create-account.php" method="post">-->
<!--<input type="text" name="username" value="" placeholder="Username ..."><p />-->
<!--<input type="password" name="password" value="" placeholder="Password ..."><p />-->
<!--<input type="email" name="email" value="" placeholder="someone@somesite.com"><p />-->
<!--<input type="submit" name="createaccount" value="Create Account">-->
<!--</form>-->

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
    <form method="post">
        <h2 class="sr-only">Create Account</h2>
        <div class="illustration"><i class="icon ion-android-create"></i></div>
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
            <button class="btn btn-primary btn-block" id="ca" type="button" data-bs-hover-animate="shake">
                Create Account
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

    $('#ca').click(function () {

        $.ajax({

            type: "POST",
            url: "api/users",
            processData: false,
            contentType: "application/json",
            data: '{ "username": "' + $("#username").val() + '", "email": "' + $("#email").val() + '", "password": "' + $("#password").val() + '" }',
            success: function (r) {
                if (r == "SUCESS")
                    window.location = "index.php";
            },
            error: function (r) {
                console.log(r)
                console.log("999");
                setTimeout(function () {
                    $('[data-bs-hover-animate]').removeClass('animated ' + $('[data-bs-hover-animate]').attr('data-bs-hover-animate'));
                }, 2000)
                $('[data-bs-hover-animate]').addClass('animated ' + $('[data-bs-hover-animate]').attr('data-bs-hover-animate'))

            }

        });

    });
</script>
</body>

</html>

