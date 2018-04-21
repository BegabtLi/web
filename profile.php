<?php
include('./classes/DB.php');
include('./classes/Login.php');
include('./classes/Post.php');
include('./classes/Image.php');
include('./classes/Notify.php');

$username = "";
$verified = False;
$isFollowing = False;
$post = False;
$search = False;
$isAdmin = False;
$_FILES['postimg']['size'] = "";

if (isset($_GET['username'])) {
        if (DB::query('SELECT username FROM users WHERE username=:username', array(':username'=>$_GET['username']))) {

                $username = DB::query('SELECT username FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['username'];
                $userid = DB::query('SELECT id FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['id'];
                $verified = DB::query('SELECT verified FROM users WHERE username=:username', array(':username'=>$_GET['username']))[0]['verified'];
                $followerid = Login::isLoggedIn();
                $followername = DB::query('SELECT username FROM users WHERE id = :userid', array(':userid'=>$followerid))[0]['username'];

                if (DB::query('SELECT username FROM admins WHERE username=:username', array(':username'=>$followername))) $isAdmin = True;

                if (isset($_POST['follow'])) {

                        if ($userid != $followerid) {

                                if (!DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid'=>$userid, ':followerid'=>$followerid))) {
                                        if ($followerid == 6) {
                                                DB::query('UPDATE users SET verified=1 WHERE id=:userid', array(':userid'=>$userid));
                                        }
                                        DB::query('INSERT INTO followers VALUES (\'\', :userid, :followerid)', array(':userid'=>$userid, ':followerid'=>$followerid));
                                } else {
                                        echo 'Already following!';
                                }
                                $isFollowing = True;
                        }
                }
                if (isset($_POST['unfollow'])) {

                        if ($userid != $followerid) {

                                if (DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid'=>$userid, ':followerid'=>$followerid))) {
                                        if ($followerid == 6) {
                                                DB::query('UPDATE users SET verified=0 WHERE id=:userid', array(':userid'=>$userid));
                                        }
                                        DB::query('DELETE FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid'=>$userid, ':followerid'=>$followerid));
                                }
                                $isFollowing = False;
                        }
                }
                if (DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid'=>$userid, ':followerid'=>$followerid))) {
                        //echo 'Already following!';
                        $isFollowing = True;
                }

                if (isset($_POST['deletepost'])) {
                        if (DB::query('SELECT id FROM posts WHERE id=:postid AND user_id=:userid', array(':postid'=>$_GET['postid'], ':userid'=>$followerid))) {
                                DB::query('DELETE FROM posts WHERE id=:postid and user_id=:userid', array(':postid'=>$_GET['postid'], ':userid'=>$followerid));
                                DB::query('DELETE FROM post_likes WHERE post_id=:postid', array(':postid'=>$_GET['postid']));
                        }
                }


                if (isset($_POST['post'])) {
                        if ($_FILES['postimg']['size'] == 0) {
                                Post::createPost($_POST['postbody'], Login::isLoggedIn(), $userid,$isAdmin);
                        } else {
                                $postid = Post::createImgPost($_POST['postbody'], Login::isLoggedIn(), $userid,$isAdmin);
                                Image::uploadImage('postimg', "UPDATE posts SET postimg=:postimg WHERE id=:postid", array(':postid'=>$postid));
                        }
                }

                if (isset($_GET['postid']) && !isset($_POST['deletepost'])) {
                        Post::likePost($_GET['postid'], $followerid);
                }

                $settings = DB::query('SELECT * FROM private_settings WHERE user_id = :userid', array(':userid'=>$userid))[0];

                if ($settings['public']){
                        $post = True;
                }

                else if ($settings['friends']) {
                        if($isFollowing){
                                $post = True;
                        }
                }

                if($followerid == $userid) {
                        $post = True;
                }

                if ($post) {
                      $posts = Post::displayPosts($userid, $username, $followerid, $isAdmin);
                }

                if (isset($_POST['searchbox'])) {
                    $search = True;
                    $tosearch = explode(" ", $_POST['searchbox']);
                    if (count($tosearch) == 1) {
                            $tosearch = str_split($tosearch[0], 2);
                    }
                    // $whereclause = "";
                    // $paramsarray = array(':username'=>'%'.$_POST['searchbox'].'%');
                    // for ($i = 0; $i < count($tosearch); $i++) {
                    //         $whereclause .= " OR username LIKE :u$i ";
                    //         $paramsarray[":u$i"] = $tosearch[$i];
                    // }
                    // $users = DB::query('SELECT users.username FROM users WHERE users.username LIKE :username '.$whereclause.'', $paramsarray);
                    //print_r($users);

                    $whereclause = "";
                    $paramsarray = array(':body'=>'%'.$_POST['searchbox'].'%');
                    for ($i = 0; $i < count($tosearch); $i++) {
                            if ($i % 2) {
                            $whereclause .= " OR body LIKE :p$i ";
                            $paramsarray[":p$i"] = $tosearch[$i];
                            }
                    }
                    $paramsarray[":userid"] = $userid;
                    $posts = DB::query('SELECT posts.id, posts.body, posts.likes, users.username 
                        FROM posts,users 
                        WHERE posts.user_id = users.id AND
                        users.id = :userid AND 
                        posts.body LIKE :body '.$whereclause.'ORDER BY id DESC'
                        , $paramsarray);

            }

        } else {
                die('User not found!');
        }
}



?>






<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Social Network</title>
    <link rel="stylesheet" href="assets/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/fonts/ionicons.min.css">
    <link rel="stylesheet" href="assets/css/Footer-Dark.css">
    <link rel="stylesheet" href="assets/css/Highlight-Clean.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.css">
    <link rel="stylesheet" href="assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="assets/css/Navigation-Clean1.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/untitled.css">
</head>

<body>
    <header class="hidden-sm hidden-md hidden-lg">
        <div class="searchbox">
            <form>
                <h1 class="text-left">Social Network</h1>
                <div class="searchbox"><i class="glyphicon glyphicon-search"></i>
                    <input class="form-control" type="text">
                </div>
                <div class="dropdown">
                    <button class="btn btn-link dropdown-toggle" data-toggle="dropdown" aria-expanded="false" type="button">MENU <span class="caret"></span></button>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                        <li role="presentation"><a href="#">My Profile</a></li>
                        <li class="divider" role="presentation"></li>
                        <li role="presentation"><a href="#">Timeline </a></li>
                        <li role="presentation"><a href="#">Messages </a></li>
                        <li role="presentation"><a href="#">Notifications </a></li>
                        <li role="presentation"><a href="#">My Account</a></li>
                        <li role="presentation"><a href="#">Logout </a></li>
                    </ul>
                </div>
            </form>
        </div>
        <hr>
    </header>
    <div>
        <nav class="navbar navbar-default hidden-xs navigation-clean">
            <div class="container">
                <div class="navbar-header"><a class="navbar-brand navbar-link" href="#"><i class="icon ion-ios-navigate"></i></a>
                    <button class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navcol-1"><span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span></button>
                </div>
                <div class="collapse navbar-collapse" id="navcol-1">
                    <form class="navbar-form navbar-left" method="post" action="profile.php?username=<?php echo($username); ?>">
                        <div class="searchbox"><i class="glyphicon glyphicon-search"></i>
                            <input class="form-control" name="searchbox" type="text">
                        </div>
                    </form>
                    <ul class="nav navbar-nav hidden-md hidden-lg navbar-right">
                        <li class="active" role="presentation"><a href="#">Timeline</a></li>
                        <li role="presentation"><a href="my-messages.php">Messages</a></li>
                        <li role="presentation"><a href="notify.php">Notifications</a></li>
                        <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" href="#">User <span class="caret"></span></a>
                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                <li role="presentation"><a href="profile.php?username=<?php echo($followername); ?>">My Profile</a></li>
                                <li class="divider" role="presentation"></li>
                                <li role="presentation"><a href="#">Timeline </a></li>
                                <li role="presentation"><a href="send-message.php?reciever=<?php echo($userid); ?>">Send Messages </a></li>
                                <li role="presentation"><a href="notify.php">Notifications </a></li>                               
                                <?php 

                                if ($isAdmin){

                                     echo "<li role=\"presentation\"><a href='delete-account.php?userid=$userid'>Delete User Account </a></li>";
                                      echo "<li role=\"presentation\"><a href='update-account.php?userid=$userid'>Update User Account </a></li>";
                                     
                                 }                             

                                if($userid == $followerid)
                                    echo "<li role=\"presentation\"><a href=\"private_settings.php\">Private Settings </a></li>";

                                if (Login::isLoggedIn()){
                                        echo "<li role=\"presentation\"><a href=\"logout.php\">Logout </a></li>";
                                        // if ($isFollowing) echo "<li role=\"presentation\"><a href=\"send-message.php?reciever=$userid\">Send Messages </a></li>";;
                                }
                                else{
                                         echo "<li role=\"presentation\"><a href=\"login.php\">Login </a></li>";
                                }

                                 ?>
                                
                            </ul>
                        </li>
                    </ul>
                    <ul class="nav navbar-nav hidden-xs hidden-sm navbar-right">
                        <li class="active" role="presentation"><a href="#">Timeline</a></li>
                        <li role="presentation"><a href="my-messages.php">Messages</a></li>
                        <li role="presentation"><a href="notify.php">Notifications</a></li>
                        <li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false" href="#">User <span class="caret"></span></a>
                            <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                <li role="presentation"><a href="profile.php?username=<?php echo($followername); ?>">My Profile</a></li>
                                <li class="divider" role="presentation"></li>
                                <li role="presentation"><a href="#">Timeline </a></li>
                                <li role="presentation"><a href="send-message.php?reciever=<?php echo($userid); ?>">Send Messages </a></li>
                                <li role="presentation"><a href="notify.php">Notifications </a></li>                               
                                <?php 

                                if ($isAdmin){

                                     echo "<li role=\"presentation\"><a href='delete-account.php?userid=$userid'>Delete User Account </a></li>";
                                      echo "<li role=\"presentation\"><a href='update-account.php?userid=$userid'>Update User Account </a></li>";
                                     
                                 }                             

                                if($userid == $followerid)
                                    echo "<li role=\"presentation\"><a href=\"private_settings.php\">Private Settings </a></li>";


                                if (Login::isLoggedIn()){
                                        echo "<li role=\"presentation\"><a href=\"logout.php\">Logout </a></li>";
                                }
                                else{
                                         echo "<li role=\"presentation\"><a href=\"login.php\">Login </a></li>";
                                }

                                 ?>
                                
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </div>
    <div class="container">
        <h1><?php echo $username; ?>'s Profile <i class="glyphicon glyphicon-ok-sign verified" data-toggle="tooltip" title="Verified User" style="font-size:28px;color:#da052b;"></i></h1></div>
        
</form>
    <div>
        <div class="container">
            <div class="row">
                <div class="col-md-3">
                    <ul class="list-group">
                        <li class="list-group-item"><span><strong>About Me</strong></span>
                            <p>Welcome <?php echo $username; ?>'s Profile<?php if ($verified) { echo ' - Verified'; } ?></p>
                            <form action="profile.php?username=<?php echo $username; ?>" method="post">
                                <?php
                                if ($userid != $followerid) {
                                        if ($isFollowing) {
                                                echo '<input type="submit" class="btn btn-primary" name="unfollow" value="Unfollow">';
                                        } else {
                                                echo '<input type="submit" class="btn btn-danger" name="follow" value="Follow">';
                                        }
                                }
                                ?>
                        </li>
                    </ul>
                </div>
                <div class="col-md-6">
                    <ul class="list-group">
                        <?php if ($post) {
                              if (!$search) {echo $posts;}
                              else {Post::display($posts);}
                              } ?>
                    </ul>
                </div>
                <div class="col-md-3">
                        <form action="profile.php?username=<?php echo $username; ?>" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                              <textarea name="postbody" class="form-control" rows="12" ></textarea>
                        </div>    
                        
                        <div class="form-group">
                               <br />Upload an image:
                        </div>
                        
                        <div class="form-group">
                               <input type="file" class="btn btn-info" name="postimg"> 
                        </div>
                        <div class="form-group">
                               <input type="submit" class="btn btn-danger" name="post" value="NEW POST">
                        </div>
                        
                        
                        </form>
                    
                </div>
            </div>
        </div>
    </div>
    <div class="footer-dark">
        <footer>
            <div class="container">
                <p class="copyright">Social Network© 2018</p>
            </div>
        </footer>
    </div>
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/bootstrap/js/bootstrap.min.js"></script>
    <script src="assets/js/bs-animation.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
    <script type="text/javascript">
