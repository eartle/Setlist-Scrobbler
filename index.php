<?php
    require_once('init.php');
    $activePage = 0;
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Setlist Scrobbler</title>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
        <link rel="stylesheet" href="/css/main.css">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    </head>
    <body>
        <?php include_once("analyticstracking.php") ?>
        <?php require_once('header.php') ?>

        <div class="container">
            <div class="jumbotron">
                <?php
                    if ($notLoggedIn) {
                        print('<p><b>Setlist Scrobbler</b> will scrobble the setlists found on <a  target="_blank" href="http://www.setlist.fm">Setlist.fm</a> for the headliners of the <a target="_blank" href="http://www.last.fm">Last.fm</a> events you\'ve attended.</p>');
                        print('<p>Register as attending a <a target="_blank" href="http://www.last.fm">Last.fm</a> event (within the last two weeks) and if its also on <a target="_blank" href="http://www.setlist.fm">Setlist.fm</a>, our daily job will scrobble it for you. Cool, eh?</p>');
                        print('<p><b>' . $oDatabase->getActiveUserCount() . '</b> active users have scrobbled <b>' . $oDatabase->getEventCount() . '</b> events already, so why not join them by logging in below?</p>');
                        print('<a class="btn btn-primary" href="http://www.last.fm/api/auth/?api_key=' . $oCall->getApiKey() . '">Login with Last.fm</a>');
                    } else {
                        print('<p>Hello, <a target="_blank" href="http://www.last.fm/user/' . $aUser['user_name'] . '">' . $aUser['user_name'] . '</a>!</p>');
                        if (isset($_GET['login'])) {
                            print('<p>Success! Your <a target="_blank" href="http://www.last.fm">Last.fm</a> events will now be scrobbled when setlists are added to <a target="_blank" href="http://www.setlist.fm">Setlist.fm</a>.</p>');
                        } else {
                            print('<p>You\'re logged in and your <a target="_blank" href="http://www.last.fm">Last.fm</a> events will be scrobbled when setlists are added to <a target="_blank" href="http://www.setlist.fm">Setlist.fm</a>.</p>');
                        }
                    }
                ?>
            </div>

            <?php
                if (!$notLoggedIn) {
                    $username = $aUser['user_name'];
                    $aScrobbledEvents = $oDatabase->getUserEventIdsPaged($username, $page, $limit);
                    $eventCount = $oDatabase->getUserEventCount($username);
                    
                    $nextDisabled = count($aScrobbledEvents) < $limit;
                                
                    if (!$nextDisabled) {
                        # the results fill the page, check if there's another page
                        $nextDisabled = (count($oDatabase->getUserEventIdsPaged($username, $page + 1, $limit)) == 0);
                    } 

                    print('<div class="panel panel-primary">');
                    print('<div class="panel-heading">Your Scrobbled Events ' .  (1 + ($page * $limit)) . '-' . min($eventCount, (($page + 1) * $limit)) . '</div>');

                    $hasEvents = count($eventCount) > 0;

                    if ($hasEvents) {
                        print('<div class="table-responsive">');
                        print('<table class="table table-striped table-bordered">');
                        print('<thead>');
                        print('<tr>');
                        print('<th>Date</th>');
                        print('<th>Title</th>');
                        print('<th>Venue</th>');
                        print('</tr>');
                        print('</thead>');

                        print('<tbody>');
                    
                        foreach ($aScrobbledEvents as $aEvent) {
                            try {
                                $oEvent = Event::getInfo($aEvent['event_id']);
                                print('<tr>');
                                print('<td nowrap>' . date("d-m-Y", $oEvent->getStartDate()) . '</td>');
                                print('<td><a target="_blank" href="' . $oEvent->getUrl() . '">' . $oEvent->getTitle() . '</a></td>');
                                print('<td><a target="_blank" href="' . $oEvent->getVenue()->getUrl() . '">' . $oEvent->getVenue()->getName() . '</td>');
                                print('</tr>');
                            } catch (Exception $e) {
                                // do nothing
                            }   

                            print('<tr>');
                            print('<td nowrap>error</td>');
                            print('<td><a target="_blank" href="http://www.last.fm/event/' . $aEvent['event_id'] . '">event id (' . $aEvent['event_id'] . ')</a></td>');
                            print('<td>unknown venue</td>');
                            print('</tr>');
                        }

                        print('</tbody>');
                        print('</table>');
                        print('</div>');
                    } else {
                        print('<div class="panel-body">We\'ve not found any events to scrobble for you yet, but we only try once a day so hang in there.</div>');
                    }

                    print('</div>');
                    if ($hasEvents) {
                        require_once('pager.php');
                    }
                    print('<div class="panel panel-danger">');
                    print('<div class="panel-heading">Stop Scrobbling Events?</div>');
                    print('<div class="panel-body">To stop scrobbling events, please remove <b>Setlist Scrobbler</b> from your <a target="_blank" href="http://www.last.fm/settings/applications">Connected Applications</a>.</div>');
                    print('</div>');
                }
            ?>
            
        </div>
        <?php require_once('footer.php') ?>
    </body>
</html>


