<?php
	require_once('init.php');
    $isHome = False;
    $isStats = True;
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Setlist Scrobbler | Stats</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="css/main.css">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    </head>
    <body>

        <?php require_once('header.php') ?>

        <div class="container">
            <h2 class="page-subheader">Stats</h2>
            <p><b><?=$oDatabase->getActiveUserCount()?></b> active users have scrobbled <b><?=$oDatabase->getEventCount()?></b> events.</p>
            <h2 class="page-subheader">Hall of Fame</h2>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th>User</th>
                            <th>Events Scrobbled</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php       
                            $aUsers = $oDatabase->getUserEventCounts();
                            $position = 1;
                        
                            foreach ($aUsers as $aUser) {
                                $username = $aUser['user_name'];
                                print('<tr>');
                                print('<td>' . $position++ . '</td>' );
                                print('<td><a target="_blank" href="http://www.last.fm/user/' . $username . '">' . $username . '</a></td>' );
                                print('<td>' . $aUser['event_count'] . '</td>');
                                print('</tr>');
                            }
                        ?>
                    </tbody>
                </table>
            </div>
            <h2 class="page-subheader">Events of Fame</h2>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th>User</th>
                            <th>Events Scrobbled</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php       
                            $aEvents = $oDatabase->getTopEvents();
                            $position = 1;
                        
                            foreach ($aEvents as $aEvent) {
                                $oEvent = Event::getInfo($aEvent['event_id']);
                                print('<tr>');
                                print('<td>' . $position++ . '</td>' );
                                print('<td><a target="_blank" href="' . $oEvent->getUrl() . '">' . $oEvent->getName() . '</a></td>' );
                                print('<td>' . $oEvent['event_count'] . '</td>');
                                print('</tr>');
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php require_once('footer.php') ?>
    </body>
</html>
