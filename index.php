<?php
	require_once('init.php');

	$user = $oDatabase->validate($_COOKIE['user']);
	$aUser = $oDatabase->getUserByName($user);
	
	if (!$aUser || isset($_GET['logout'])) {
		setcookie('user', '');
	}
	
	$notLoggedIn = (!isset($_COOKIE['user']) || !$oDatabase->validate($_COOKIE['user']) || $_GET['logout'] == 1);
?>
<html>
    <head>
        <title>Setlist Scrobbler</title>
        <style type="text/css">
            body {
                font-family: Helvetica, Bitstream Vera Sans, sans-serif;
                color: #000000;
            }
            .content {
                text-align: left;
                margin-left: 20%;
                margin-top: 8%;
            }
			.heading {
                font-size: 3em;
                font-weight: bold;
            }
            .heading2 {
                font-size: 1em;
                font-weight: bold;
            }
            A:link {
                text-decoration: none;
                color: #008000;
            }
            A:visited {
                text-decoration: none;
                color: #008000;
            }
            A:active {
                text-decoration: none;
                color: #008000;
            }
            A:hover {
                text-decoration: underline;
                color: #008000;
            }
        </style>
    </head>
    <body>
	<?
	if (!$notLoggedIn) {
		print ('<div><a href="?logout=1">Not ' . htmlentities($aUser['user_name']) . '?</a></div>');
	}
	?>
        <div class="content">
			<div class="heading">Setlist Scrobbler</div>
			
			<?
				if ($notLoggedIn) {
					print('
						<div class="item" style="width:40%; margin-top: 1%">
							<small>Setlist Scrobbler will scrobble the setlists found on <a href="http://www.setlist.fm">Setlist.fm</a> for the headliners of the <a href="http://www.last.fm">Last.fm</a> events you\'ve attended.<br><a href="http://www.last.fm/api/auth/?api_key=' . $oCall->getApiKey() . '">Click here</a> to authenticate with <a href="http://www.last.fm">Last.fm</a> and start using Setlist Scrobbler.</small>
						</div>
						');
				}
				else {
					print('
					
					<div class="item" style="width:40%; margin-top: 1%">
						<small>Success!  Your <a href="http://www.last.fm">Last.fm</a> events will now be scrobbled when setlists are added to <a href="http://www.setlist.fm">Setlist.fm</a>.</small>
					</div>
					');
					
					$aScrobbledEvents = $oDatabase->getEventIds($aUser['user_name']);
					
					if ($aScrobbledEvents) {
						print('
						<div class="item" style="margin-top: 1%">
						<div class="heading2">Scrobbled events</div>
						');
					
						foreach ($aScrobbledEvents as $aEvent) {
							$oEvent = Event::getInfo($aEvent['event_id']);
							$image = preg_replace('/34/', '/34s/', $oEvent->getImage(Media::IMAGE_SMALL));
						
							print( '
							<div>
							<a href="' . $oEvent->getUrl() . '">
							<img width="34" height="34" src="' . $image . '" alt="' . $oEvent->getTitle() . '"/>' .
							$oEvent->getTitle() . '</a>
							</div>
							' );
						}
						
						print('<div>');
					}
				}
			?>
			
			<div class="item" style="margin-top: 1%">
				<a href="http://twitter.com/share" class="twitter-share-button" data-url="http://mobbler.co.uk/sls/index.php" data-text="Scrobble your @lastfm events with Setlist Scrobbler." data-count="horizontal" data-via="eartle">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>
			</div>
			<div class="item" style="margin-top: 1%">
				<iframe src="http://www.facebook.com/plugins/like.php?href=http%3A%2F%2Fmobbler.co.uk%2Fsls%2Findex.php&amp;layout=standard&amp;show_faces=true&amp;width=450&amp;action=like&amp;colorscheme=light&amp;height=80" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:450px; height:80px;" allowTransparency="true"></iframe>
			</div>
		</div>
    </body>
</html>
<?


