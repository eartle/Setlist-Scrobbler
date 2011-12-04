<?php

    require_once('init.php');
	
	// Find the Setlist Scrobbler account so that we can
	// shout at the user when their event gets scrobbled
	$aSlsUser = $oDatabase->getUserByName('setscrobbler');
	$slsSession = new Session( $aSlsUser['user_name'], $aSlsUser['user_session'], false );
	
	$aUsers = $oDatabase->getUsers();

	foreach ($aUsers as $aUser) {
	
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
						// we can't scrobble tracks older than 4 weeks so just stop
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
						// so now search for the event on setlist.fm
						
						$aPastEventArtists = $oPastEvent->getArtists();
						$headliner = $aPastEventArtists['headliner'];
						print ' has headliner ' . $headliner . ' and';
						
						
						$dateString = $eventDate->format("d-m-Y");
						$params = array("artistName" => $headliner,
											"date" => $dateString);
		
						$curl  = curl_init();
						$url = "http://api.setlist.fm/rest/0.1/search/setlists?" . http_build_query($params, '', '&');
			
						curl_setopt($curl, CURLOPT_URL, $url);
						curl_setopt($curl, CURLOPT_POST, 0);
						curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($curl, CURLOPT_FRESH_CONNECT, 1);
			
						$response = curl_exec($curl);

						try {
							$response = new SimpleXMLElement($response);
				
							foreach ($response->setlist as $oSetlist) {
								// Look through the Setlist.fm search responses
								$aArtistAttributes = $oSetlist->artist->attributes();
							
								if ($aArtistAttributes["name"] == $headliner) {
									// This is the setlist for the headliner
									print ' has been found';
									
									if ($oSetlist->sets->set) {
										print ' there is at least one set ';
										
										$trackTime = intval($oPastEvent->getStartDate());
										// Assume that the headliner starts 2 hours after the doors open
										$trackTime += 60 * 120;
										$trackLength = 60 * 4;

										$session = new Session( $aUser['user_name'], $aUser['user_session'], false );
															
										foreach ($oSetlist->sets->set as $oSet) {
											// we have a set so scrobble it
											print ' The headliner: ' . $aArtistAttributes["name"] . PHP_EOL;
								
											foreach ($oSet->song as $aSong) {
												// scrobble every track in the set
												$aSongAttributes = $aSong->attributes();
												
												$trackTitle = $aSongAttributes["name"];
										
												$trackTimeDate = getdate($trackTime);
												
												print $headliner . " - " . $trackTitle . " @ " . $trackTimeDate["hours"] . ":" . $trackTimeDate["minutes"] . PHP_EOL;
										
												$scrobbleResponse = Track::scrobble($headliner, $trackTitle, $trackTime, $session);
												//var_dump($scrobbleResponse);
							
												$trackTime += $trackLength;
											}
										}
											
										// Make sure we don't scrobble it again!
										$oDatabase->addEventId($aUser['user_name'], $oPastEvent->getId());
										
										$message = 'The event [url=' . $oPastEvent->getUrl() . ']' . $oPastEvent->getTitle() . '[/url] has had its [url=' . $oSetlist->url . ']Setlist.fm setlist[/url] scrobbled for you by [url=http://mobbler.co.uk/sls/]Setlist Scrobbler[/url].';
										
										// email myself so that I can celebrate!
										mail('eartle@gmail.com', 'An event was scrobbled!', $aUser['user_name'] . ' scrobbled the event: ' . $oPastEvent->getTitle());
										
										// Shout at the user from the setlist scrobble account
										// so that they know something has happened
										$scrobbleResponse = User::shout($aUser['user_name'], $message, $slsSession);
										//var_dump($scrobbleResponse);
									}
									else {
										print ', but doesn\'t have a setlist. :(';
									}
								}
							}
						}
						catch (Exception $e) {
							print 'Caught exception: ' .  $e->getMessage() . PHP_EOL;
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

