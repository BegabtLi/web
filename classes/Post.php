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
        $newstring = "<div class=\"text-primary lead\">";

        foreach ($text as $word) {
            if (substr($word, 0, 1) == "@") {
                $newstring .= "<a href='profile.php?username=" . substr($word, 1) . "'>" . htmlspecialchars($word) . "</a> ";
            } else if (substr($word, 0, 1) == "#") {
                $newstring .= "<a href='topics.php?topic=" . substr($word, 1) . "'>" . htmlspecialchars($word) . "</a> ";
            } else {
                $newstring .= htmlspecialchars($word) . " ";
            }
        }

        return $newstring . "</div>";
    }

 public static function displaySearchPosts($dbposts,$userid, $username, $loggedInUserId,$isAdmin){

                $posts = "";

                foreach($dbposts as $p) {

                        if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid'=>$p['id'], ':userid'=>$loggedInUserId))) {
                                if(empty($p['postimg'])){
                                      $posts .=  self::link_add($p['body'])."<form class=\"form-group\" action='profile.php?username=$username&postid=".$p['id']."' method='post'>
                                        <input class=\"btn btn-danger\" type='submit' name='like' value='Like'>
                                        <span class=\"text-danger\">".$p['likes']." likes</span>
                                ";
                                }
                                else{

                                        $posts .= "<img src='".$p['postimg']."'class=\"img-rounded\" width=\"128\" height=\"128\">".self::link_add($p['body'])."
                                <form class=\"form-group\" action='profile.php?username=$username&postid=".$p['id']."' method='post'>
                                        <input class=\"btn btn-danger\" type='submit' name='like' value='Like'>
                                        <span class=\"text-danger\">".$p['likes']." likes</span>
                                ";

                                }

                                if ($userid == $loggedInUserId || $isAdmin) {
                                        $posts .= "<input class=\"btn btn-danger\"  type='submit' name='deletepost' value='x' />";
                                }
                        } else {
                                $posts .= "<img src='".$p['postimg']."'>".self::link_add($p['body'])."
                                <form action='profile.php?username=$username&postid=".$p['id']."' method='post'>
                                <input class=\"btn btn-danger\"  type='submit' name='unlike' value='Unlike'>
                                <span class=\"text-danger\" >".$p['likes']." likes</span>
                                ";
                                if ($userid == $loggedInUserId) {
                                        $posts .= "<input class=\"btn btn-danger\"  type='submit' name='deletepost' value='x' />";
                                }
                        }

                        if ($p['comment'] == 1) {
                            $posts .= "</hr /><input class=\"btn btn-primary\"  type='submit' name='allow' value='Allow comment by others' />";
                        } else {
                            $posts .= "</hr /><input class=\"btn btn-danger\"  type='submit' name='disable' value='Disable comments' />";
                        }
                        $posts .= "</form><hr /></br />";

                }

                return $posts;



         }

        public static function displayPosts($userid, $username, $loggedInUserId,$isAdmin) {
                $dbposts = DB::query('SELECT * FROM posts WHERE user_id=:userid ORDER BY id DESC', array(':userid'=>$userid));


                return self::displaySearchPosts($dbposts,$userid, $username, $loggedInUserId,$isAdmin);

        }

    public static function display($posts, $userid)
    {

        foreach ($posts as $post) {
            $profileLink = 'profile.php?username=' . $post['username'] . '';
            echo "<div class=\"lead text-primary\">" . $post['body'] . " 
                          <p>Posted BY <a href=" . $profileLink . ">" . $post['username'] . "</a></p></div>";
            echo "<form action='index.php?postid=" . $post['id'] . "' class=\"form-group\" method='post'>";

            if ($userid) {
                if (!DB::query('SELECT post_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid' => $post['id'], ':userid' => $userid))) {
                    echo "<input type='submit' class=\"btn btn-danger\" name='change_like' value='Like'>";
                } else {
                    echo "<input type='submit' class=\"btn btn-danger\" name='change_like' value='Unlike'>";
                }
                echo "<span class=\"text-danger\">" . $post['likes'] . " likes</span>";

                echo "<hr/>";
                echo Comment::displayComments($post['id']);
                echo "</form>";
                if ($post['comment'] == 0) {
                    echo "
                     <form action='index.php?postid=" . $post['id'] . "' class=\"form-group\"  method='post'  enctype=\"multipart/form-data\">
                     <div>
                         <textarea  class=\"form-control\" name='commentbody' rows='3' cols='50'></textarea>
                     </div>
                     <div class=\"form-group\">Upload image or video:</div>
                     <div class=\"form-group\">
                         <input type=\"file\" class=\"btn btn-info\" name=\"commentimg\"/>
                     </div>
                     <div class=\"form-group\" >  
                         <input type='submit' name='comment' class=\"btn btn-success\" value='Comment'>
                     </div>
                    </form>
                    ";
                } else {
                    echo "
                     <form action='index.php?postid=" . $post['id'] . "' class=\"form-group\"  method='post'  enctype=\"multipart/form-data\">
                     <div class=\"form-group\" >  
                         <input type='submit' name='comment' class=\"btn btn-success\" value='Request Comment'>
                     </div>
                    </form>
                    ";
                }
                echo "<hr /></br />";
            } else {
                echo Comment::displayComments($post['id']);
                echo "</form>";
            }
//            echo $post['comment'];
        }
    }

    public static function likePost($postId, $likerId)
    {

        if (!DB::query('SELECT user_id FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid' => $postId, ':userid' => $likerId))) {
            DB::query('UPDATE posts SET likes=likes+1 WHERE id=:postid', array(':postid' => $postId));
            DB::query('INSERT INTO post_likes VALUES (\'\', :postid, :userid)', array(':postid' => $postId, ':userid' => $likerId));
            Self::createNotify("", $postId);
        } else {
            DB::query('UPDATE posts SET likes=likes-1 WHERE id=:postid', array(':postid' => $postId));
            DB::query('DELETE FROM post_likes WHERE post_id=:postid AND user_id=:userid', array(':postid' => $postId, ':userid' => $likerId));
        }

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
            DB::query('INSERT INTO notifications VALUES (\'\', :type, :receiver, :sender, :extra)', array(':type' => 2, ':receiver' => $r, ':sender' => $s, ':extra' => ""));
        }

        return $notify;
    }


}

?>