<nav class="navbar navbar-default hidden-xs navigation-clean">
    <div class="container">
        <div class="navbar-header"><a class="navbar-brand navbar-link"
                                      href="profile.php?username=<?php echo isset($followername) ? $followername : $username ?>"><i
                        class="icon ion-ios-people"></i></a>
        </div>
        <div class="collapse navbar-collapse" id="navcol-1">
            <form class="navbar-form navbar-left" method="post"
                  action="profile.php?username=<?php echo($username); ?>">
                <div class="searchbox"><i class="glyphicon glyphicon-search"></i>
                    <input class="form-control" name="searchbox" type="text">
                </div>
            </form>
            <ul class="nav navbar-nav hidden-xs hidden-sm navbar-left">
                <li role="presentation"><a href="index.php">Timeline</a></li>

                <?php

                if (Login::isLoggedIn()) {
                    echo "<li role=\"presentation\"><a href=\"my-messages.php\">Messages</a></li>";
                    echo "<li role=\"presentation\"><a href=\"notify.php\">Notifications</a></li>";

                    if ($isAdmin) {
                        echo "<li class=\"dropdown\"><a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-expanded=\"false\" href=\"#\">Admin<span class=\"caret\"></span></a>";
                    } else {
                        echo "<li class=\"dropdown\"><a class=\"dropdown-toggle\" data-toggle=\"dropdown\" aria-expanded=\"false\" href=\"javascript:void(0);\" id=\"userdrop1\" aria-haspopup=\"true\">User<span class=\"caret\"></span></a>";
                    }

                    echo "<ul class=\"dropdown-menu dropdown-menu-right\" role=\"menu\">";

                    if ($isAdmin) {
                        echo "<li role=\"presentation\"><a href='delete-account.php?userid=$userid'>Delete User Account </a></li>";
                        echo "<li role=\"presentation\"><a href='update-account.php?userid=$userid'>Update User Account </a></li>";
                        echo "<li role=\"presentation\"><a href=\"userlist.php\">UserList</a></li>";
                    }

                    echo "<li role=\"presentation\"><a href=\"send-message.php?receiver=$userid\">Send Messages </a></li>";
                    echo "<li role=\"presentation\"><a href=\"logout.php\">Logout </a></li>";
                } else {
                    echo "<li role=\"presentation\"><a href=\"create-account.php\">Register</a></li>";
                    echo "<li role=\"presentation\"><a href=\"login.php\">Login </a></li>";
                }

                echo "</ul>";

                ?>
                </li>
            </ul>
        </div>
    </div>
</nav>