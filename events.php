<?php
    require_once('init.php');
    $activePage = 2;
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Setlist Scrobbler | Events</title>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="/css/main.css">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    </head>
    <body>
        <?php include_once("analyticstracking.php") ?>
        <?php require_once('header.php') ?>

        <div class="container">
            <div class="panel panel-primary">
                <div class="panel-heading">Events of Fame</div>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Event</th>
                                <th>Venue</th>
                                <th>Scrobblers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php    
                                $aEvents = $oDatabase->getTopEvents($page, $limit);
                                $position = 1 + ($page * $limit);
                            
                                foreach ($aEvents as $aEvent) {
                                    try {
                                        $oEvent = Event::getInfo($aEvent['event_id']);

                                        print('<tr>');
                                        print('<td>' . $position++ . '</td>' );
                                        print('<td nowrap>' . date("d-m-Y", $oEvent->getStartDate()) . '</td>');
                                        print('<td><a target="_blank" href="' . $oEvent->getUrl() . '">' . $oEvent->getTitle() . '</a></td>' );
                                        print('<td><a target="_blank" href="' . $oEvent->getVenue()->getUrl() . '">' . $oEvent->getVenue()->getName() . '</a></td>' );
                                        print('<td>' . $aEvent['event_count'] . '</td>');
                                        print('</tr>');
                                    } catch (Exception $e) {
                                        print('<tr>');
                                        print('<td>' . $position++ . '</td>' );
                                        print('<td nowrap>error</td>');
                                        print('<td><a href="http://www.last.fm/event/' . $aEvent['event_id'] . '">event id (' . $aEvent['event_id'] . ')</a></td>' );
                                        print('<td>venue</td>' );
                                        print('<td>' . $aEvent['event_count'] . '</td>');
                                        print('</tr>');
                                    }
                                }

                                $nextDisabled = count($aEvents) < $limit;
                                
                                if (!$nextDisabled) {
                                    # the results fill the page, check if there's another page
                                    $nextDisabled = (count($oDatabase->getTopEvents($page + 1, $limit)) == 0);
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
