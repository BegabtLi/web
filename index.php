<?php
include_once('./classes/DB.php');
include_once('./classes/Login.php');
include_once('./classes/Post.php');
include_once('./classes/Comment.php');
include_once('./classes/Image.php');


$showTimeline = False;
$followingposts = null;
$search = False;
$isAdmin = False;
//$_FILES['commentimg']['size'] = (isset($_FILES['commentimg']['size'])) ? $_POST['file'] :'' ;




if (Login::isLoggedIn()) {
    $userid = Login::isLoggedIn();
    $username = DB::query('SELECT username FROM users WHERE id = :userid', array(':userid'=>$userid))[0]['username'];
    if (DB::query('SELECT username FROM admins WHERE username=:username', array(':username'=>$username))) $isAdmin = True;
    $followingposts = DB::query('SELECT posts.id, posts.body, posts.posted_at, posts.likes, users.username, posts.postimg, users.profileimg
        FROM users JOIN posts ON users.id = posts.user_id
        JOIN followers
        WHERE posts.privacy = 2
        AND followers.follower_id = posts.user_id
        AND followers.user_id = :userid
        UNION
        SELECT posts.id, posts.body, posts.posted_at, posts.likes, users.username, posts.postimg, users.profileimg
        FROM users JOIN posts ON users.id = posts.user_id
        WHERE (posts.privacy = 1 AND posts.user_id = :userid)
        OR posts.privacy = 0', array(':userid' => $userid));

    $showTimeline = True;
} else {
    $followingposts = DB::query('SELECT posts.id, posts.body, posts.posted_at, posts.likes, users.username, posts.postimg, users.profileimg
        FROM users JOIN posts ON users.id = posts.user_id
        WHERE posts.privacy = 0');
    $showTimeline = True;
}

if (isset($_GET['postid'])) {
    Post::likePost($_GET['postid'], $userid);
}
if (isset($_POST['comment'])) {
    if ($_FILES['commentimg']['size'] == 0) {
        Comment::createImgComment($_POST['commentbody'], $_GET['postid'], $userid);
    } 
    else { 
        $name= $_FILES['commentimg']['name'];
        $temp= $_FILES['commentimg']['tmp_name'];
        $tp= $_FILES['commentimg']['type']; 
        if (($tp == "image/gif") || ($tp == "image/jpeg")
            || ($tp == "image/pjpeg") || ($tp == "image/png") ) {
            $commentid = Comment::createImgComment($_POST['commentbody'], $_GET['postid'], $userid);
            Image::uploadImage('commentimg', "UPDATE comments SET commentimg=:commentimg WHERE id=:commentid", array(':commentid'=>$commentid));
        }
        else{
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
    $paramsarray1 = array(':username'=>'%'.$_POST['searchbox'].'%');
    for ($i = 0; $i < count($tosearch); $i++) {
        $whereclause1 .= " OR username LIKE :u$i ";
        $paramsarray1[":u$i"] = $tosearch[$i];
    }
        // $users = DB::query('SELECT users.username FROM users WHERE users.username LIKE :username '.$whereclause.'', $paramsarray);
        //print_r($users);

    $whereclause2 = "";
    $paramsarray2 = array(':body'=>'%'.$_POST['searchbox'].'%');
    for ($i = 0; $i < count($tosearch); $i++) {
            if ($i % 2) {
            $whereclause2 .= " OR body LIKE :p$i ";
            $paramsarray2[":p$i"] = $tosearch[$i];
            }
    }
    $posts = DB::query('SELECT posts.id, posts.body, posts.likes, users.username 
        FROM posts,users 
        WHERE posts.user_id = users.id AND
        posts.body LIKE :body '.$whereclause1.
        'ORDER BY id DESC', $paramsarray1);
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
<!--     <div class="ui menu">
        <div class="item">
            <div class="header item">Brand</div>
            <a class="active item">Link</a>
            <a class="item">Link</a>
            <a class="item">Link</a>

        </div>
        <div class="item middle menu">
            <div class="ui action left icon input">
                <i class="search icon"></i>
                <input type="text" placeholder="Search">
                <button class="ui button">Submit</button>
            </div>
             
        </div>
        <div class="right menu">
            <div class="ui dropdown item">
            Dropdown
                <i class="dropdown icon"></i>
                <div class="menu">
                    <div class="item">Action</div>
                    <div class="item">Another Action</div>
                    <div class="item">Something else here</div>
                    <div class="divider"></div>
                    <div class="item">Separated Link</div>
                    <div class="divider"></div>
                    <div class="item">One more separated link</div>
                </div>
            </div>
           
        </div> -->
    <!-- </div> -->
<div>
    <nav class="navbar navbar-default navigation-clean">
        <div class="container">
            <div class="navbar-header"><a class="navbar-brand navbar-link"
                                          href="profile.php?username=<?php echo $username ?>"><i
                            class="icon ion-ios-people"></i></a>
                <button class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navcol-1"><span
                            class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span
                            class="icon-bar"></span><span class="icon-bar"></span></button>
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
    <!-- <h1>Timeline </h1> -->
    <p></p>
    <div class="ui huge header">Timeline</div>
    <hr>
    <br>
    <div class="ui main text container segment">
        <div class="timelineposts">
        <?php
        if (Login::isLoggedIn() && !$search) {
            foreach (array_reverse($followingposts) as $post) {
                if($post['postimg']){
                    $w = " a photo";
                }
                else{
                    $w = ' ';
                }
                $profileLink = 'profile.php?username=' . $post['username'] . '';
                echo "<div class=\"lead text-primary\">";
                echo "<img class='smallavatar' src='".$post['profileimg']."'>";
                echo "<span class='post postwho'><a href=" . $profileLink . ">" . $post['username'] . "</a> posted".$w."<p class='post posttime'>".$post['posted_at']."</p></span>";
                echo "<img src='".$post['postimg']."' class=\"ui rounded image\" >";
                echo "<p class='post postbody'>". $post['body'] . "</p></div>";
                echo "<form action='index.php?postid=" . $post['id'] . "' class=\"form-group\" method='post'>";

                if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid' => $post['id'], ':userid' => $userid))) {

                    // echo "<input type='submit' class=\"btn btn-danger\" name='like' value='Like'>";
                    echo '<div class="ui labeled button" tabindex="0">';
                    echo '<button type="submit" class="ui red button" name="like">
                            <i class="heart icon"></i> Like
                            </button>';
                    echo '<a class="ui basic red left pointing label">'.$post['likes'].'</a></div>';
                } else {
                    // echo "<input type='submit' class=\"btn btn-danger\" name='unlike' value='Unlike'>";
                    echo '<div class="ui labeled button" tabindex="0">';
                    echo '<button type="submit" class="ui button" name="unlike">
                            <i class="heart icon"></i> Like
                            </button>';
                    echo '<a class="ui basic label">'.$post['likes'].'</a></div>';
                }
                // echo "<span class=\"text-danger\">" . $post['likes'] . " likes</span>";
                echo "<hr/>";
                echo "</form>";
                echo "<form action='index.php?postid=" . $post['id'] . "' class=\"form-group\"  method='post'  enctype=\"multipart/form-data\">";
                echo "<input  class=\"autocomplete form-control\" name='commentbody' rows='3' cols='50'></input>";
                echo "<div class=\"post form-group\"><span>Upload image or video:</span>";
                echo "<span class=\"form-group\">";
                echo "<input type=\"file\" class=\"ui inverted button\" name=\"commentimg\"></span></div>";
                echo "<div class=\"form-group hidden\" ><input type='submit' name='comment' class=\"btn btn-success\" value='Comment'></div></form>";
                echo Comment::displayComments($post['id']);
                echo "<p></p><hr>";


            }
        } else if (!Login::isLoggedIn() && !$search) {
            $publicposts = DB::query('SELECT posts.id, posts.body, posts.posted_at, posts.likes, posts.privacy, posts.postimg, users.username, users.profileimg
            FROM users JOIN posts ON users.id = posts.user_id
            WHERE posts.privacy = 0');
            // Post::display($publicposts);
            displayposts($publicposts);
        } else {
            Post::display($posts);
        }


        function displayposts($posts)
        {
            foreach (array_reverse($posts) as $post) {
                if($post['postimg']){
                    $w = " a photo";
                }
                else{
                    $w = ' ';
                }
                $profileLink = 'profile.php?username=' . $post['username'] . '';
                echo "<div class=\"lead text-primary\">";
                echo "<img class='smallavatar' src='".$post['profileimg']."'>";
                echo "<span class='post postwho'><a href=" . $profileLink . ">" . $post['username'] . "</a> posted".$w."<p class='post posttime'>".$post['posted_at']."</p></span>";
                echo "<img src='".$post['postimg']."' class=\"ui rounded image\" >";
                echo "<p class='post postbody'>". $post['body'] . "</p></div>";
                echo "<form action='index.php?postid=" . $post['id'] . "' class=\"form-group\" method='post'>";
                // echo "<span class=\"text-danger\">" . $post['likes'] . " likes</span>";
                echo "<hr/>";
                echo "</form>";
                echo "<form action='index.php?postid=" . $post['id'] . "' class=\"form-group\"  method='post'  enctype=\"multipart/form-data\">";
                echo "<input  class=\"autocomplete form-control\" name='commentbody' rows='3' cols='50'></input>";
                echo "<div class=\"post form-group\"><span>Upload image or video:</span>";
                echo "<span class=\"form-group\">";
                echo "<input type=\"file\" class=\"ui inverted button\" name=\"commentimg\"></span></div>";
                echo "<div class=\"form-group hidden\" ><input type='submit' name='comment' class=\"btn btn-success\" value='Comment'></div></form>";
                echo Comment::displayComments($post['id']);
                echo "<p></p><hr>";


            }
        }

        ?>

    </div>
    </div>
</div>

<div class="footer-dark navbar-fixed-bottom" style="position: relative">
    <footer>
        <div class="container">
            <p class="copyright">Social Network</p>
        </div>
    </footer>
</div>
<script src="assets/js/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/js/bs-animation.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/aos/2.1.1/aos.js"></script>
<script type="text/javascript"></script>
</body>
</html>

