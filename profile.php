<?php
include('./classes/DB.php');
include('./classes/Login.php');
include('./classes/Post.php');
include('./classes/Image.php');
include('./classes/Notify.php');
include('./classes/Request.php');

$username = "";
$verified = False;
$isFollowing = False;
$post = False;
$search = False;
$isAdmin = False;
$privacy = 0;
$need = 0;

if(!Login::isLoggedIn()){
    header("location: login.php");
}

if (isset($_GET['username'])) {
    if (DB::query('SELECT username FROM users WHERE username=:username', array(':username' => $_GET['username']))) {

        $username = DB::query('SELECT username FROM users WHERE username=:username', array(':username' => $_GET['username']))[0]['username'];
        $userid = DB::query('SELECT id FROM users WHERE username=:username', array(':username' => $_GET['username']))[0]['id'];
        $userimg =  DB::query('SELECT profileimg FROM users WHERE username=:username', array(':username' => $_GET['username']))[0]['profileimg'];
        $verified = DB::query('SELECT verified FROM users WHERE username=:username', array(':username' => $_GET['username']))[0]['verified'];
        $followerid = Login::isLoggedIn();
        $followername = DB::query('SELECT username FROM users WHERE id = :userid', array(':userid' => $followerid))[0]['username'];

        if (DB::query('SELECT username FROM admins WHERE username=:username', array(':username' => $followername))) $isAdmin = True;

        if (isset($_POST['follow'])) {

            if ($userid != $followerid) {

                Request::friendRequest($userid, $followerid);

                if (!DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid' => $userid, ':followerid' => $followerid))) {
                    if ($followerid == 6) {
                        DB::query('UPDATE users SET verified=1 WHERE id=:userid', array(':userid' => $userid));
                    }
                    DB::query('INSERT INTO followers VALUES (NULL, :userid, :followerid)', array(':userid' => $userid, ':followerid' => $followerid));
                } else {
                    echo 'Already following!';
                }
                $isFollowing = True;
            }
        }
        if (isset($_POST['unfollow'])) {

            if ($userid != $followerid) {

                if (DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid' => $userid, ':followerid' => $followerid))) {
                    if ($followerid == 6) {
                        DB::query('UPDATE users SET verified=0 WHERE id=:userid', array(':userid' => $userid));
                    }
                    DB::query('DELETE FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid' => $userid, ':followerid' => $followerid));
                }
                $isFollowing = False;
            }
        }
        if (DB::query('SELECT follower_id FROM followers WHERE user_id=:userid AND follower_id=:followerid', array(':userid' => $userid, ':followerid' => $followerid))) {
            //echo 'Already following!';
            $isFollowing = True;
        }

        if (isset($_POST['deletepost'])) {
            // echo "<script type='text/javascript'>alert('111111');</script>";
            if (DB::query('SELECT id FROM posts WHERE id=:postid AND user_id=:userid', array(':postid' => $_GET['postid'], ':userid' => $followerid))) {
                DB::query('DELETE FROM comments WHERE post_id=:postid', array(':postid' => $_GET['postid']));
                DB::query('DELETE FROM posts WHERE id=:postid and user_id=:userid', array(':postid' => $_GET['postid'], ':userid' => $followerid));
                DB::query('DELETE FROM post_likes WHERE post_id=:postid', array(':postid' => $_GET['postid']));
            }
        }
        if (isset($_POST['allow'])) {
            DB::query('UPDATE posts SET comment=0 WHERE id=:postid and user_id=:userid', array(':postid' => $_GET['postid'], ':userid' => $followerid));
        }

        if (isset($_POST['disable'])) {
            DB::query('UPDATE posts SET comment=1 WHERE id=:postid and user_id=:userid', array(':postid' => $_GET['postid'], ':userid' => $followerid));
        }

        if (isset($_POST['post'])) {

            $setting = $_POST['setting'];
            switch ($setting) {

                case 'private':
                    $privacy = 1;
                    break;
                case 'friends':
                    $privacy = 2;
                    break;

                default:
                    $privacy = 0;
                    break;
            }

            if ($_POST['need_approval']) $need = 1;

            if ($_FILES['postimg']['size'] == 0) {
                $postid = Post::createPost($_POST['postbody'], Login::isLoggedIn(), $userid, $isAdmin, $privacy, $need);
            } else {
                $postid = Post::createImgPost($_POST['postbody'], Login::isLoggedIn(), $userid, $isAdmin, $privacy, $need);
                Image::uploadImage('postimg', "UPDATE posts SET postimg=:postimg WHERE id=:postid", array(':postid' => $postid));
            }
            header("Location: {$_SERVER["HTTP_REFERER"]}");

        }

        if (isset($_GET['postid']) && !isset($_POST['deletepost']) && (isset($_POST['like'])||isset($_POST['unlike']))) {
            Post::likePost($_GET['postid'], $followerid);
            header("Location:profile.php?username=".$username);
        }

        if ($followerid == $userid) {
            $post = True;
        }

        // if (!$post) {
            $posts = Post::displayPosts($userid, $username, $followerid, $isAdmin);
        // }

        if (isset($_POST['searchbox'])) {
            $search = True;
            $tosearch = explode(" ", $_POST['searchbox']);
            if (count($tosearch) == 1) {
                $tosearch = str_split($tosearch[0], 2);
            }

            $whereclause = "";
            $paramsarray = array(':body' => '%' . $_POST['searchbox'] . '%');
            for ($i = 0; $i < count($tosearch); $i++) {
                if ($i % 2) {
                    $whereclause .= " OR body LIKE :p$i ";
                    $paramsarray[":p$i"] = $tosearch[$i];
                }
            }
            $paramsarray[":userid"] = $userid;
            $posts = DB::query('SELECT posts.id, posts.body, posts.likes, users.username, posts.posted_at, posts.postimg
                        FROM posts,users 
                        WHERE posts.user_id = users.id AND
                        users.id = :userid AND 
                        posts.body LIKE :body ' . $whereclause . 'ORDER BY id DESC', $paramsarray);
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
    <title>Profile</title>
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
    <?php include dirname(__FILE__).'/header.php' ?>
</div>

<div>
    <div class="container mod">
        <div class="row">
            <div class="col-md-3" style="margin-left: 10px; margin-right: -20px;">
                <ul class="list-group">
                    <li class="list-group-item" style="padding:20px;">
                        <p></p>
                        <img src=<?php echo $userimg ?> class="ui rounded image" alt="Avatar""> 
                        <span><strong>About Me</strong></span>

                        <p>Welcome <?php echo $username; ?>'s Profile<?php if ($verified) {
                                echo ' - Verified';
                            } ?></p>
                        <form action="profile.php?username=<?php echo $username; ?>" method="post">
                            <?php
                            if ($userid != $followerid) {
                                if ($isFollowing) {
                                    echo '<input type="submit" class="btn btn-danger" name="unfollow" value="Remove from Friends">';
                                } else {
                                    echo '<input type="submit" class="btn btn-primary" name="follow" value="Be Friend">';
                                }
                            }
                            ?>
                        </form>
                    </li>
                </ul>
            </div>
            <div class="col-md-9">
                <div class="ui main text container segment">
                <ul class="list-group">
                    <?php 
                        if (!$search) {
                            // echo $posts[0][4];

                            // Post::displayProfilePosts($posts);
                            echo Post::displayProfilePosts($posts, $userid, $username, $followerid, $isAdmin);

                        }
                        else {echo Post::displaySearchPosts($posts,$userid, $username, $followerid, $isAdmin);}
                         ?>
                </ul>
                </div>
            </div>
            <!-- <div class="col-md-3">
                <form action="profile.php?username=<?php echo $username; ?>" method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <textarea name="postbody" class="form-control" rows="12"></textarea>
                    </div>

                    <div class="form-group">
                        <br/>Upload an image:
                    </div>

                    <div class="form-group">
                        <input type="file" class="btn btn-info" name="postimg">
                    </div>

                    <div class="radio">
                        <label><input type="radio" name="setting" value="public" checked> Public</label>
                    </div>
                    <div class="radio">
                        <label><input type="radio" name="setting" value="private"> Private</label>
                    </div>
                    <div class="radio ">
                        <label><input type="radio" name="setting"><input type="radio" name="setting" value="friends"> Friends Only</label>
                    </div>

                    <div class="form-group">
                        <input type="submit" class="btn btn-danger" name="post" value="NEW POST">
                    </div>

                </form>

            </div> -->
        </div>
    </div>
</div>
<?php include dirname(__FILE__).'/footer.php' ?>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/bs-animation.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
<script type="text/javascript"></script>
</body>
</html>