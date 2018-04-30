<?php

class Comment
{
    public static function createComment($commentBody, $postId, $userId)
    {

        if (strlen($commentBody) > 160 || strlen($commentBody) < 1) {
            die('Incorrect length!');
        }

        if (!DB::query('SELECT id FROM posts WHERE id=:postid', array(':postid' => $postId))) {
            echo 'Invalid post ID';
        } else {
            DB::query('INSERT INTO comments VALUES (NULL, :comment, :userid, NOW(), :postid)', array(':comment' => $commentBody, ':userid' => $userId, ':postid' => $postId));
        }

    }

    public static function displayComments($postId)
    {

        $comments = DB::query('SELECT comments.*, users.username, users.profileimg FROM comments, users WHERE post_id = :postid AND comments.user_id = users.id', array(':postid' => $postId));
        $coms = "";
        foreach ($comments as $comment) {
            $profileLink = 'profile.php?username=' . $comment['username'] . '';
            $coms .= "<div class=\"lead text-muted\">" . "<img class='commentavatar ui rounded image' src='" . $comment['profileimg'] . "'><span class='post postcomment'><a href=" . $profileLink . "> " . $comment['username'] . " </a> " . $comment['comment'] . "</span></div><span>";

            if (empty($comment['commentimg']) && empty($comment['commentvideo'])) {
            } else if (isset($comment['commentimg'])) {
                $coms .= "<img class='comm img-rounded' src='" . $comment['commentimg'] . "'>";
            } else {
                $coms .= "<video class='comm' controls> <source src=" . $comment['commentvideo'] . " type='video/mp4'>
                                 Your browser does not support the video tag. </video>";

            }


        }

        echo $coms;


    }

    public static function createImgComment($commentBody, $postId, $userId)
    {
        if (strlen($commentBody) > 160 || strlen($commentBody) < 1) {
            die('Incorrect length!');
        }
        if (!DB::query('SELECT id FROM posts WHERE id=:postid', array(':postid' => $postId))) {
            echo 'Invalid post ID';
        } else {
            DB::query('INSERT INTO comments VALUES (NULL, :comment, :userid, NOW(), :postid,NULL,NULL)', array(':comment' => $commentBody, ':userid' => $userId, ':postid' => $postId));
            $commentid = DB::query('SELECT id FROM comments WHERE user_id = :userid AND post_id = :postid ORDER BY ID DESC LIMIT 1', array(':userid' => $userId, ':postid' => $postId))[0]['id'];
            return $commentid;
        }

    }
}

?>
