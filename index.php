<?php
include_once('./classes/DB.php');
include('./classes/Login.php');
include('./classes/Post.php');
include('./classes/Request.php');
include_once('./classes/Comment.php');
include_once('./classes/Image.php');

$showTimeline = False;
$unsearchposts = null;
$search = False;
$isAdmin = False;
if (Login::isLoggedIn()) {
    $userid = Login::isLoggedIn();
    $username = DB::query('SELECT username FROM users WHERE id = :userid', array(':userid' => $userid))[0]['username'];
    if (DB::query('SELECT username FROM admins WHERE username=:username', array(':username' => $username))) $isAdmin = True;
    $unsearchposts = DB::query('SELECT posts.id, posts.body, posts.likes, users.username, posts.comment
            FROM users JOIN posts ON users.id = posts.user_id
            JOIN followers
            WHERE posts.privacy = 2
            AND followers.follower_id = posts.user_id
            AND followers.user_id = :userid
            UNION
            SELECT posts.id, posts.body, posts.likes, users.username, posts.comment
            FROM users JOIN posts ON users.id = posts.user_id
            WHERE (posts.privacy = 1 AND posts.user_id = :userid)
            OR posts.privacy = 0', array(':userid' => $userid));
    $showTimeline = True;
} else {
    $unsearchposts = DB::query('SELECT posts.id, posts.body, posts.likes, users.username, posts.comment
            FROM users JOIN posts ON users.id = posts.user_id
            WHERE posts.privacy = 0');
    $showTimeline = True;
}


if (isset($_GET['postid'])) {
    Post::likePost($_GET['postid'], $userid);
}
if (isset($_POST['comment'])) {
    if (!isset($_FILES['commentimg']) || $_FILES['commentimg']['size'] == 0) {
        if (!isset($_POST['commentbody'])) {
            Request::commentRequest($_GET['postid'], $userid);
        } else {
            Comment::createImgComment($_POST['commentbody'], $_GET['postid'], $userid);
        }
    } else {
        $name = $_FILES['commentimg']['name'];
        $temp = $_FILES['commentimg']['tmp_name'];
        $tp = $_FILES['commentimg']['type'];
        if (($tp == "image/gif") || ($tp == "image/jpeg")
            || ($tp == "image/pjpeg") || ($tp == "image/png")) {
            $commentid = Comment::createImgComment($_POST['commentbody'], $_GET['postid'], $userid);
            Image::uploadImage('commentimg', "UPDATE comments SET commentimg=:commentimg WHERE id=:commentid", array(':commentid' => $commentid));
        } else {
            echo " Video";
            $newloc = 'uploaded/';
            $newloc .= $name;
            move_uploaded_file($temp, $newloc);
            $commentid = Comment::createImgComment($_POST['commentbody'], $_GET['postid'], $userid);
            DB::query("UPDATE comments SET commentvideo=:commentvideo WHERE id=:commentid", array(':commentid' => $commentid, ':commentvideo' => $newloc));

        }

    }
    // Comment::createComment($_POST['commentbody'], $_GET['postid'], $userid);
}

if (isset($_POST['searchbox'])) {
    $search = True;
    $tosearch = explode(" ", $_POST['searchbox']);
    if (count($tosearch) == 1) {
        $tosearch = str_split($tosearch[0], 2);
    }
    $whereclause1 = "";
    $paramsarray1 = array(':username' => '%' . $_POST['searchbox'] . '%');
    for ($i = 0; $i < count($tosearch); $i++) {
        $whereclause1 .= " OR username LIKE :u$i ";
        $paramsarray1[":u$i"] = $tosearch[$i];
    }
    // $users = DB::query('SELECT posts.id, posts.body, posts.likes, users.username
    //      FROM posts,users
    //      WHERE posts.user_id = users.id AND
    //      users.username LIKE :username ' . $whereclause . 'ORDER BY id DESC', $paramsarray);

    $whereclause2 = "";
    $paramsarray2 = array(':body' => '%' . $_POST['searchbox'] . '%');
    for ($i = 0; $i < count($tosearch); $i++) {
        if ($i % 2) {
            $whereclause2 .= " OR body LIKE :p$i ";
            $paramsarray2[":p$i"] = $tosearch[$i];
        }
    }
//    $paramsarray = array_merge($paramsarray1,$paramsarray2);

    $posts = DB::query('SELECT posts.id, posts.body, posts.likes, users.username, posts.comment
            FROM posts,users 
            WHERE posts.user_id = users.id AND
            users.username LIKE :username ' . $whereclause1
             . 'ORDER BY id DESC', $paramsarray1);

//    $posts = array_merge($users, $posts);
//    print_r($posts);
    //$posts = array_unique($posts);

//        $posts = array_intersect($followingposts, $posts);

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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.css">
    <link rel="stylesheet" href="assets/css/Login-Form-Clean.css">
    <link rel="stylesheet" href="assets/css/Navigation-Clean1.css">
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/untitled.css">
</head>

<body>
<div>
    <?php include dirname(__FILE__).'/header.php' ?>
</div>


<div class="container">
    <h1>Timeline </h1>
    <hr/>
    </br />
    <div class="timelineposts">
        <?php
        if (Login::isLoggedIn()) {
            if ($search) {
                Post::display($posts, $userid);
            } else {
                Post::display($unsearchposts, $userid);
            }
        } else {
            if ($search) {
                Post::display($posts, null);
            } else {
                Post::display($unsearchposts, null);
            }
        }

        ?>

    </div>
</div>

<?php include dirname(__FILE__).'/footer.php' ?>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/bs-animation.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
<script type="text/javascript">
</body>
</html>

