<?php

    require_once('init.php');

	foreach ($oDatabase->getUsers() as $aUser) {
	
		try {
			print PHP_EOL . PHP_EOL . $aUser["user_name"];
			
			// fetch this user's past Last.fm events!
			$aPastEvents = User::getPastEvents($aUser['user_name'], 20);
			
			$aScrobbledEvents = $oDatabase->getEventIds($aUser['user_name']);
			
			foreach ($aPastEvents as $oPastEvent) {
			
				if ($oPastEvent->getStatus() == Event::ATTENDING) {	
					// they attended this event
					
					print PHP_EOL . $oPastEvent->getTitle();
								
					$two_weeks_ago = strtotime('now - 2 weeks');
					$date = getdate($oPastEvent->getStartDate());
					$eventDate = new DateTime();
					$eventDate->setDate($date["year"], $date["mon"], $date["mday"]);
					$eventDate->setTime($date["hours"], $date["minutes"]);
					
					if ($oPastEvent->getStartDate() < $two_weeks_ago) {
						// we can't scrobble tracks older than 2 weeks so just stop
						print ' was over two weeks ago.';
						break;
					}
					
					$scrobbled = false;
					
					// check if this event has been scrobbled for this user
					foreach ($aScrobbledEvents as $eventId) {
						if ($eventId["event_id"] == $oPastEvent->getId()) {
							// we have already scrobbled this event
							
							print " has already been scrobbled.";
							$scrobbled = true;
							break;
						}
					}
					
					if ($scrobbled == false) {
						// we haven't scrobbled this event
						// so nowsearch for the event on SongKick
						
						$aPastEventArtists = $oPastEvent->getArtists();
						$headliner = $aPastEventArtists['headliner'];
						print ' has headliner ' . $headliner . ' and';
						
						
						$dateString = $eventDate->format("Y-m-d");
						$params = array("apikey" => "D7XQBrzFd8K6bv9Z",
											"artist_name" => $headliner,
											"min_date" => $dateString,
											"max_date" => $dateString);
		
						$curl  = curl_init();
						$url = "http://api.songkick.com/api/3.0/events.xml?" . http_build_query($params, '', '&');
			
						curl_setopt($curl, CURLOPT_URL, $url);
						curl_setopt($curl, CURLOPT_POST, 0);
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
			
						$response = curl_exec($curl);
						$response = new SimpleXMLElement($response);
			
						foreach ($response->results->event as $oEvent) {
							// Look through the SongKick search responses
							print ' has been found';
						
							$aEventAttributes = $oEvent->attributes();
						
							if ($aEventAttributes["type"] == "Concert") {
								// Only scrobble tracks form concerts because festivals are too messy
								print ', is a concert';
							
								// Try to find a setlist on SongKick for this event
								curl_setopt($curl, CURLOPT_URL, "http://api.songkick.com/api/3.0/events/" . $aEventAttributes["id"] . "/setlists.json?apikey=D7XQBrzFd8K6bv9Z");
							
								$response = curl_exec($curl);
								$response = json_decode($response);
							
								$aSetlist = $response->{'resultsPage'}->{'results'}->{'setlist'};
							
								if ($aSetlist) {
									print ' and we\'ve found a list of setlists';
									
									foreach ($aSetlist as $oSetlist) 
										{
										if ($headliner == $oSetlist->{'artist'}->{'displayName'}) {
										
											// we have a setlist so scrobble this track
											print ' The headliner: ' . $aEventAttributes["displayName"] . PHP_EOL;
										
											$trackTime = intval($oPastEvent->getStartDate());
											// Assume that the headliner starts 2 hours after the doors open
											$trackTime += 60 * 120;
											$trackLength = 60 * 4;
											
											$session = new Session( $aUser['user_name'], $aUser['user_session'], false );
									
											foreach ($oSetlist->{'setlistItem'} as $aSetlistItem) {
												// scrobble every track in the playlist
											
												$trackTimeDate = getdate($trackTime);
												print $oSetlist->{'artist'}->{'displayName'} . " - " . $aSetlistItem->{'name'} . " @ " . $trackTimeDate["hours"] . ":" . $trackTimeDate["minutes"] . PHP_EOL;
											
												$scrobbleResponse = Track::scrobble($oSetlist->{'artist'}->{'displayName'}, $aSetlistItem->{'name'}, $trackTime, $session);
								
												var_dump($scrobbleResponse);
								
												$trackTime += $trackLength;
											}
											
											// Make sure we don't scrobble it again!
											$oDatabase->addEventId($aUser['user_name'], $oPastEvent->getId());
											
											// email myself so that I can celebrate!
											mail('eartle@gmail.com', 'An event was scrobbled!', $aUser['user_name'] . ' scrobbled the event: ' . $aEventAttributes["displayName"]);
										}
										else {
											print ' (not the headliner)';
										}
									}
								}
								else {
									print ', but doesn\'t have a setlist. :(';
								}
							}
							else {
								print ', but isn\'t a concert. :(';
							}
						}
					}
				}
			}
		}
		catch (Exception $e) {
			print 'Caught exception: ' .  $e->getMessage() . PHP_EOL;
		}
	}
	
	print PHP_EOL;
?>

