<?php

include_once('Comment.php');


class Post
{

    public static function createPost($postbody, $loggedInUserId, $profileUserId, $isAdmin, $privacy, $need)
    {

        if (strlen($postbody) > 160 || strlen($postbody) < 1) {
            die('Incorrect length!');
        }


        $topics = self::getTopics($postbody);

        if ($loggedInUserId == $profileUserId || $isAdmin) {

            if (count(Notify::createNotify($postbody)) != 0) {
                foreach (Notify::createNotify($postbody) as $key => $n) {
                    $s = $loggedInUserId;
                    $r = DB::query('SELECT id FROM users WHERE username=:username', array(':username' => $key))[0]['id'];
                    if ($r != 0) {
                        DB::query('INSERT INTO notifications VALUES (\'\', :type, :receiver, :sender, :extra)', array(':type' => $n["type"], ':receiver' => $r, ':sender' => $s, ':extra' => $n["extra"]));
                    }
                }
            }

            DB::query('INSERT INTO posts VALUES (\'\', :postbody, NOW(), :userid, :privacy, :need, 0, \'\', :topics)', array(':postbody' => $postbody, ':userid' => $profileUserId, ':privacy' => $privacy, ':need' => $need, ':topics' => $topics));

        } else {
            die('Incorrect user!');
        }
    }

    public static function createImgPost($postbody, $loggedInUserId, $profileUserId, $isAdmin, $privacy, $need)
    {

        if (strlen($postbody) > 160) {
            die('Incorrect length!');
        }

        $topics = self::getTopics($postbody);

        if ($loggedInUserId == $profileUserId || $isAdmin) {

            if (count(Notify::createNotify($postbody)) != 0) {
                foreach (Notify::createNotify($postbody) as $key => $n) {
                    $s = $loggedInUserId;
                    $r = DB::query('SELECT id FROM users WHERE username=:username', array(':username' => $key))[0]['id'];
                    if ($r != 0) {
                        DB::query('INSERT INTO notifications VALUES (\'\', :type, :receiver, :sender, :extra)', array(':type' => $n["type"], ':receiver' => $r, ':sender' => $s, ':extra' => $n["extra"]));
                    }
                }
            }

            DB::query('INSERT INTO posts VALUES (\'\', :postbody, NOW(), :userid, :privacy, :need, 0, \'\', :topics)', array(':postbody' => $postbody, ':userid' => $profileUserId, ':privacy' => $privacy, ':need' => $need, ':topics' => $topics));
            $postid = DB::query('SELECT id FROM posts WHERE user_id=:userid ORDER BY ID DESC LIMIT 1;', array(':userid' => $loggedInUserId))[0]['id'];
            return $postid;
        } else {
            die('Incorrect user!');
        }
    }

    public static function likePost($postId, $likerId)
    {

        if (!DB::query('SELECT user_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid' => $postId, ':userid' => $likerId))) {
            DB::query('UPDATE posts SET likes=likes+1 WHERE id=:postid', array(':postid' => $postId));
            DB::query('INSERT INTO post_likes VALUES (NULL, :postid, :userid)', array(':postid' => $postId, ':userid' => $likerId));
            Self::createNotify("", $postId);
        } else {
            DB::query('UPDATE posts SET likes=likes-1 WHERE id=:postid', array(':postid' => $postId));
            DB::query('DELETE FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid' => $postId, ':userid' => $likerId));
        }

    }

    public static function getTopics($text)
    {

        $text = explode(" ", $text);

        $topics = "";

        foreach ($text as $word) {
            if (substr($word, 0, 1) == "#") {
                $topics .= substr($word, 1) . ",";
            }
        }

        return $topics;
    }

    public static function link_add($text)
    {
        $text = explode(" ", $text);
        $newstring = "";

        foreach ($text as $word) {
            if (substr($word, 0, 1) == "@") {
                $newstring .= "<a href='profile.php?username=" . substr($word, 1) . "'>" . htmlspecialchars($word) . "</a> ";
            } else if (substr($word, 0, 1) == "#") {
                $newstring .= "<a href='topics.php?topic=" . substr($word, 1) . "'>" . htmlspecialchars($word) . "</a> ";
            } else {
                $newstring .= htmlspecialchars($word) . " ";
            }
        }

        return $newstring;
    }

    public static function displaySearchPosts($dbposts, $userid, $username, $loggedInUserId, $isAdmin)
    {

        $posts = "";

        foreach (array_reverse($dbposts) as $p) {
            if ($p['postimg']) {
                $posts .= "<img src='" . $p['postimg'] . "'class=\"imgprofile img-rounded\">";
            }
            $posts .= "<p class='post postbody'>" . self::link_add($p['body']) . "</p>";
            $posts .= "<p class='post posttime' style='margin-left:0px'>Posted at " . $p['posted_at'] . "</p>";
            $posts .= "<form class=\"form-group\" action='profile.php?username=$username&postid=" . $p['id'] . "' method='post'>";
            $posts .= "<p style:'text-align:right;' class='post posttime'>Posted at " . $p['posted_at'] . "</p>";
            if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid' => $p['id'], ':userid' => $loggedInUserId))) {
                // $posts .= "<input class=\"btn btn-danger\" type='submit' name='like' value='Like'>";
                $posts .= '<div class="ui labeled button" tabindex="0">';
                $posts .= '<button type="submit" class="small ui button" name="like">
                                            <i class="heart icon"></i> Like
                                            </button>';
                // $posts .= "<span class=\"text-danger\">".$p['likes']." likes</span>";
                $posts .= '<a class="ui basic label">' . $p['likes'] . '</a></div>';

            } else {
                // $posts .= "<input class=\"btn btn-danger\" type='submit' name='like' value='Like'>";
                $posts .= '<div class="ui labeled button" tabindex="0">';
                $posts .= '<button type="submit" class="small ui red button" name="unlike">
                                            <i class="heart icon"></i> Unlike
                                            </button>';
                // $posts .= "<span class=\"text-danger\">".$p['likes']." likes</span>";
                $posts .= '<a class="ui basic red left pointing label">' . $p['likes'] . '</a></div>';
            }
            if ($userid == $loggedInUserId) {
                $posts .= "<button class='tiny ui basic button'  type='submit' name='deletepost'><span class='glyphicon glyphicon-trash'></span> Delete</button>";
            }
            $posts .= "</form><hr /></br />";
        }
        return $posts;


    }

    public static function displayProfilePosts($posts, $userid, $username, $loggedInUserId, $isAdmin)
    {
        foreach (array_reverse($posts) as $post) {
            echo "<div class=\"lead text-primary\">";
            echo "<img src='" . $post['postimg'] . "' class=\"ui rounded image imgprofile\" >";
            echo "<p class='post postbody'>" . self::link_add($post['body']) . "</p></div>";
            echo "<p class='post posttime' style='margin-left:0px'>Posted at " . $post['posted_at'] . "</p>";
            echo "<form action='profile.php?username=$username&postid=" . $post['id'] . "' class=\"form-group\" method='post'>";

            if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid' => $post['id'], ':userid' => $loggedInUserId))) {

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
            if ($userid == $loggedInUserId || $isAdmin) {
                echo "<button class='tiny ui basic button'  type='submit' name='deletepost'><span class='glyphicon glyphicon-trash'></span> Delete</button>";

                if ($post['comment'] == 1) {
                    echo "</hr /><input class=\"ui basic green button\"  type='submit' name='allow' value='Allow comment by others' />";
                } else {
                    echo "</hr /><input class=\"ui basic red button\"  type='submit' name='disable' value='Disable comments' />";
                }
            }

            echo "</form>";
            echo "<form action='profile.php?username=$username&postid=" . $post['id'] . "' class=\"form-group\"  method='post'  enctype=\"multipart/form-data\">";
            echo "<input  class=\"autocomplete form-control\" name='commentbody' rows='3' cols='50'></input>";
            echo "<div class=\"post form-group\"><p style='margin-bottom:-10px;'>Press enter to post</p><span>Upload image or video:</span>";
            echo "<span class=\"form-group\">";
            echo "<input type=\"file\" class=\"ui inverted button\" name=\"commentimg\"></span></div>";
            echo "<div class=\"form-group hidden\" ><input type='submit' name='comment' class=\"btn btn-success\" value='Comment'></div></form>";
            echo Comment::displayComments($post['id']);
            echo "<p></p><hr>";

        }

    }

    public static function getPosts($userid, $loggedInUserId, $isAdmin)
    {
        if ($isAdmin) $loggedInUserId = $userid;
        $profilePosts = DB::query('SELECT posts.id, posts.body, posts.posted_at, posts.likes, users.username, posts.postimg, users.profileimg, posts.privacy, posts.comment
        FROM users JOIN posts ON users.id = posts.user_id
        JOIN followers
        WHERE posts.user_id = :userid
        AND (
        posts.privacy = 2
        AND (
        (followers.follower_id = posts.user_id AND followers.user_id = :loguserid)
        OR (posts.user_id = :loguserid)
        )
        )
        UNION
        SELECT posts.id, posts.body, posts.posted_at, posts.likes, users.username, posts.postimg, users.profileimg, posts.privacy, posts.comment
        FROM users JOIN posts ON users.id = posts.user_id
        WHERE posts.user_id = :userid
        AND (
        (posts.privacy = 1 AND posts.user_id = :loguserid)
        OR posts.privacy = 0
        )
        ORDER BY posted_at', array(':loguserid' => $loggedInUserId, ':userid' => $userid));

        return $profilePosts;

    }

    public static function createNotify($text = "", $postid = 0)
    {
        $text = explode(" ", $text);
        $notify = array();

        foreach ($text as $word) {
            if (substr($word, 0, 1) == "@") {
                $notify[substr($word, 1)] = array("type" => 1, "extra" => ' { "postbody": "' . htmlentities(implode($text, " ")) . '" } ');
            }
        }

        if (count($text) == 1 && $postid != 0) {
            $temp = DB::query('SELECT posts.user_id AS receiver, post_likes.user_id AS sender FROM posts, post_likes WHERE posts.id = post_likes.post_id AND posts.id=:postid', array(':postid' => $postid));
            $r = $temp[0]["receiver"];
            $s = $temp[0]["sender"];
            DB::query('INSERT INTO notifications VALUES (NULL, :type, :receiver, :sender, :extra)', array(':type' => 2, ':receiver' => $r, ':sender' => $s, ':extra' => ""));
        }

        return $notify;
    }


}

?>