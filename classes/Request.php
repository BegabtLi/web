<?php
/**
 * Created by PhpStorm.
 * User: RMBP
 * Date: 4/26/18
 * Time: 02:23
 */

class Request
{
    public static function commentRequest($postid, $userid)
    {
        $temp = DB::query('SELECT posts.user_id AS receiver FROM posts WHERE posts.id=:postid', array(':postid' => $postid));
        $r = $temp[0]["receiver"];
        $s = $userid;
        DB::query('INSERT INTO notifications VALUES (NULL, :type, :receiver, :sender, :extra)', array(':type' => 3, ':receiver' => $r, ':sender' => $s, ':extra' => ""));
    }

    public static function friendRequest($userid, $followerid)
    {
        $r = $userid;
        $s = $followerid;
        DB::query('INSERT INTO notifications VALUES (NULL, :type, :receiver, :sender, :extra)', array(':type' => 4, ':receiver' => $r, ':sender' => $s, ':extra' => ""));
    }
}