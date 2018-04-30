<?php

include('./classes/DB.php');
if (isset($_GET['userid'])) {
    DB::query('DELETE FROM users WHERE users.id = :userid', array(':userid' => $_GET['userid']));
    echo " Delete account sccessfully !";
}

?>