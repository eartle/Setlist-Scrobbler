<?php
    require_once('init.php');
    $activePage = 1;
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Setlist Scrobbler | Users</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="/css/main.css">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    </head>
    <body>
        <?php include_once("analyticstracking.php") ?>
        <?php require_once('header.php') ?>

        <div class="container">
            <div class="panel panel-primary">
                <div class="panel-heading">Users of Fame (<b><?=$oDatabase->getActiveUserCount()?></b> active users have scrobbled <b><?=$oDatabase->getEventCount()?></b> events)</div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>User</th>
                                <th>Events Scrobbled</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $aUsers = $oDatabase->getUserEventCounts($page, $limit);
                                $position = 1 + ($page * $limit);
                            
                                foreach ($aUsers as $aUser) {
                                    $username = $aUser['user_name'];
                                    $session = $aUser['user_session'];
                                    if ($session == "") {
                                        print('<tr class="danger">');
                                    } else {
                                        print('<tr>');
                                    }
                                    print('<td>' . $position++ . '</td>' );
                                    print('<td><a target="_blank" href="http://www.last.fm/user/' . $username . '">' . $username . '</a></td>' );
                                    print('<td>' . $aUser['event_count'] . '</td>');
                                    print('</tr>');
                                }

                                $nextDisabled = count($aUsers) < $limit;
                                
                                if (!$nextDisabled) {
                                    # the results fill the page, check if there's another page
                                    $nextDisabled = (count($oDatabase->getUserEventCounts($page + 1, $limit)) == 0);
                                } 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php require_once('pager.php') ?>
        </div>
        <?php require_once('footer.php') ?>
    </body>
</html>
