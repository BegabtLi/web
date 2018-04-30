<?php
include_once('./classes/DB.php');
include('./classes/Login.php');
include('./classes/Post.php');
include('./classes/Request.php');
include_once('./classes/Comment.php');
include_once('./classes/Image.php');
include('./classes/Notify.php');


$showTimeline = False;
$indexPosts = null;
$search = False;
$isAdmin = False;
if (Login::isLoggedIn()) {
    $userid = Login::isLoggedIn();
    $username = DB::query('SELECT username FROM users WHERE id = :userid', array(':userid' => $userid))[0]['username'];
    if (DB::query('SELECT username FROM admins WHERE username=:username', array(':username' => $username))) $isAdmin = True;
    $indexPosts = DB::query('SELECT posts.id, posts.body, posts.posted_at, posts.likes, users.username, posts.postimg, users.profileimg, posts.privacy, posts.comment
        FROM users JOIN posts ON users.id = posts.user_id
        JOIN followers
        WHERE posts.privacy = 2
        AND (
        (followers.follower_id = posts.user_id AND followers.user_id = :userid)
        OR (posts.user_id = :userid)
        )
        UNION
        SELECT posts.id, posts.body, posts.posted_at, posts.likes, users.username, posts.postimg, users.profileimg, posts.privacy, posts.comment
        FROM users JOIN posts ON users.id = posts.user_id
        WHERE (posts.privacy = 1 AND posts.user_id = :userid)
        OR posts.privacy = 0
        ORDER BY posted_at', array(':userid' => $userid));

    $showTimeline = True;
} else {
    $indexPosts = DB::query('SELECT posts.id, posts.body, posts.posted_at, posts.likes, users.username, posts.postimg, users.profileimg, posts.privacy
        FROM users JOIN posts ON users.id = posts.user_id
        WHERE posts.privacy = 0');
    $showTimeline = True;
}

if (isset($_GET['postid']) && (isset($_POST['like']) || isset($_POST['unlike']))) {
    Post::likePost($_GET['postid'], $userid);
    header("Location:index.php");
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
}

if (isset($_POST['searchbox'])) {
    $search = True;
    $tosearch = explode(" ", $_POST['searchbox']);
    if (count($tosearch) == 1) {
        $tosearch = str_split($tosearch[0], 2);
    }
    $whereclause = "";
    $paramsarray = array(':username' => '%' . $_POST['searchbox'] . '%');
    for ($i = 0; $i < count($tosearch); $i++) {
        $whereclause .= " OR username LIKE :u$i ";
        $paramsarray[":u$i"] = $tosearch[$i];
    }


    $searchedPosts = DB::query('SELECT posts.id, posts.body, posts.likes, users.profileimg, users.username, posts.comment, posts.postimg, posts.posted_at
        FROM posts,users 
        WHERE posts.user_id = users.id AND
        users.username LIKE :username ' . $whereclause .
        'ORDER BY id DESC', $paramsarray);
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

    $need = 0;
    if ($_POST['need_approval']) $need = 1;

    if ($_FILES['postimg']['size'] == 0) {
        $postid = Post::createPost($_POST['postbody'], Login::isLoggedIn(), $userid, $isAdmin, $privacy, $need);
    } else {
        $postid = Post::createImgPost($_POST['postbody'], Login::isLoggedIn(), $userid, $isAdmin, $privacy, $need);
        Image::uploadImage('postimg', "UPDATE posts SET postimg=:postimg WHERE id=:postid", array(':postid' => $postid));
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
    <nav class="navbar navbar-default navigation-clean">
        <div class="container">
            <div class="navbar-header">
                <a class="navbar-brand navbar-link"
                    <?php if (Login::isLoggedIn()) {
                        $redi = "profile.php?username=" . $username;
                    } else {
                        $redi = "login.php";
                    }
                    ?>
                   href=<?php echo $redi; ?>>
                    <i class="icon ion-ios-people"></i>
                </a>
            </div>
            <div class="collapse navbar-collapse" id="navcol-1">
                <form class="navbar-form navbar-left hidden-xs hidden-sm" action="index.php" method="post">
                    <div class="searchbox"><i class="glyphicon glyphicon-search"></i>
                        <input class="form-control sbox" name="searchbox" type="text">
                        <ul class="list-group autocomplete" style="position:absolute;width:100%; z-index:100">
                        </ul>
                    </div>
                </form>
                <ul class="nav navbar-nav navbar-right">
                    <li role="presentation"><a href="index.php">Timeline</a></li>
                    <?php
                    if (Login::isLoggedIn()) {
                        echo "<li role=\"presentation\"><a href=\"my-messages.php\">Messages</a></li>";
                        echo "<li role=\"presentation\"><a href=\"notify.php\">Notifications</a></li>";
                        if ($isAdmin) {
                            echo "<li class=\"dropdown\"><a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-expanded=\"false\" href=\"#\">Admin<span class=\"caret\"></span></a>";
                        } else {
                            echo "<li class=\"dropdown\"><a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-expanded=\"false\" href=\"#\">User<span class=\"caret\"></span></a>";
                        }
                    } else {
                        echo "<li role=\"presentation\"><a href=\"create-account.php\">Register</a></li>";
                        echo "<li role=\"presentation\"><a href=\"login.php\">Login </a></li>";
                    }
                    ?>
                    <ul class="dropdown-menu dropdown-menu-right" role="menu">
                        <?php if (Login::isLoggedIn()) {
                            if ($isAdmin) echo "<li role=\"presentation\"><a href=\"userlist.php\">UserList</a></li>";
                            echo "<li role=\"presentation\"><a href=\"logout.php\">Logout </a></li>";
                        } else {
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
    <p></p>

    <?php
    if (Login::isLoggedIn()) {
        $userimg = DB::query('SELECT users.profileimg FROM users
        WHERE id=:user_id', array(':user_id' => $userid));
        ?>
        <div class="ui main text container segment">
            <form action="index.php?username=<?php echo $username; ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <a href="profile.php?username=<?php echo $username; ?>"><img class="postavatar ui rounded image"
                                                                                 src=<?php echo $userimg[0][0] ?>></a>
                    <textarea name="postbody" class="post postarea form-control" rows="2"
                              placeholder="What's on your mind?"></textarea>
                </div>

                <span class="post">Post with image:
            <input type="file" class="ui inverted button" name="postimg">
            <label><input type="checkbox" name="need_approval" value="need"> Need approval for comment </label>
        </span>
                <span style="float:right">
            <select class="small ui dropdown privateselect" style="background-color:white;height:34px;width:75px;"
                    name="setting">
                <option class="item">Public</option>
                <option class="item" value="private">Private</option>
                <option class="item" value="friends">Friends Only</option>
            </select>
            <input type="submit" class=" ui basic button" style="margin-left: 10px;" name="post" value="Post">
        </span>
            </form>
        </div>
        <hr>
    <?php } ?>
    <div class="ui main text container segment">
        <div class="timelineposts">
            <?php
            if (Login::isLoggedIn()) {
                $posts = $search ? $searchedPosts : $indexPosts;
                foreach (array_reverse($posts) as $post) {
                    if ($post['postimg']) {
                        $w = " a photo";
                    } else {
                        $w = ' ';
                    }
                    $profileLink = 'profile.php?username=' . $post['username'] . '';
                    echo "<div class=\"lead text-primary\">";
                    echo "<img class='smallavatar ui rounded image' src='" . $post['profileimg'] . "'>";
                    echo "<span class='post postwho'><a href=" . $profileLink . ">" . $post['username'] . "</a> posted" . $w . "<p class='post posttime'>" . $post['posted_at'] . "</p></span>";
                    echo "<img src='" . $post['postimg'] . "' class=\"ui rounded image\" >";
                    echo "<p class='post postbody'>" . Post::link_add($post['body']) . "</p></div>";
                    echo "<form action='index.php?postid=" . $post['id'] . "' class=\"form-group\" method='post'>";

                    if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid' => $post['id'], ':userid' => $userid))) {

                        echo '<div class="ui labeled button" tabindex="0">';
                        echo '<button type="submit" class="small ui button" name="like">
                            <i class="heart icon"></i> Like
                            </button>';
                        echo '<a class="ui basic label">' . $post['likes'] . '</a></div>';
                    } else {
                        echo '<div class="ui labeled button" tabindex="0">';
                        echo '<button type="submit" class="small ui red button" name="unlike">
                            <i class="heart icon"></i> unLike
                            </button>';
                        echo '<a class="ui basic red left pointing label">' . $post['likes'] . '</a></div>';
                    }
                    echo "</form>";
                    if ($post['comment'] == 0) {
                        echo "<form action='index.php?postid=" . $post['id'] . "' class=\"form-group\"  method='post'  enctype=\"multipart/form-data\">";
                        echo "<input  class=\"post form-control\" style='font-size:16px;' placeholder='Write as comment...?' name='commentbody' rows='3' cols='50'></input>";
                        echo "<div class=\"post form-group\"><p style='margin-bottom:-10px;'>Press enter to post</p><span>Upload image or video:</span>";
                        echo "<span class=\"form-group\">";
                        echo "<input type=\"file\" class=\"ui inverted button\" name=\"commentimg\"></span></div>";
                        echo "<div class=\"form-group\" ><input type='submit' name='comment' class=\"ui basic green button\" value='Comment'></div></form>";
                    } else {
                        echo "<form action='index.php?postid=" . $post['id'] . "' class=\"form-group\"  method='post'  enctype=\"multipart/form-data\">";
                        echo "<div class=\"form-group\" ><input type='submit' name='comment' class=\"ui basic yellow button\" value='Request Comment'></div></form>";
                    }

                    echo Comment::displayComments($post['id']);
                    echo "<p></p><hr>";

                }
            } else {
                $posts = $search ? $searchedPosts : $indexPosts;
                foreach (array_reverse($posts) as $post) {
                    if ($post['postimg']) {
                        $w = " a photo";
                    } else {
                        $w = ' ';
                    }
                    $profileLink = 'profile.php?username=' . $post['username'] . '';
                    echo "<div class=\"lead text-primary\">";
                    echo "<img class='smallavatar ui rounded image' src='" . $post['profileimg'] . "'>";
                    echo "<span class='post postwho'><a href=" . $profileLink . ">" . $post['username'] . "</a> posted" . $w . "<p class='post posttime'>" . $post['posted_at'] . "</p></span>";
                    echo "<img src='" . $post['postimg'] . "' class=\"ui rounded image\" >";
                    echo "<p class='post postbody'>" . Post::link_add($post['body']) . "</p></div>";
                    echo "<form action='index.php?postid=" . $post['id'] . "' class=\"form-group\" method='post'>";
                    echo "<hr/>";
                    echo "</form>";
                }
            }

            ?>

        </div>
    </div>
</div>

<?php include dirname(__FILE__) . '/footer.php' ?>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/bs-animation.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
<script type="text/javascript"></script>
</body>
</html>

