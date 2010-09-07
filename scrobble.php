<?php

    require_once('init.php');

	foreach ($oSession->getUsers() as $aUser) {
		echo $aUser["user_name"], PHP_EOL;
		
		// fetch this user's past Last.fm events!
		$aPastEvents = User::getPastEvents($aUser['user_name']);
		
		$aScrobbledEvents = $oSession->getEventIds($aUser['user_name']);
		
		foreach ($aPastEvents as $oPastEvent) {
		
			if ($oPastEvent->getStatus() == Event::ATTENDING) {	
				// they attended this event
							
				$two_weeks_ago = strtotime('now - 2 weeks');
				$date = getdate($oPastEvent->getStartDate());
				$eventDate = new DateTime();
				$eventDate->setDate($date["year"], $date["mon"], $date["mday"]);
				$eventDate->setTime($date["hours"], $date["minutes"]);
				
				if ($oPastEvent->getStartDate() < $two_weeks_ago) {
					// we can't scrobble tracks older than 2 weeks so just stop
					break;
				}
				
				$scrobbled = false;
				
				// check if this event has been scrobbled for this user
				foreach ($aScrobbledEvents as $eventId) {
					if ($eventId["event_id"] == $oPastEvent->getId()) {
						// we have already scrobbled this event
						
						echo "we have scrobbled this event already", PHP_EOL;
						$scrobbled = true;
						break;
					}
				}
				
				if ($scrobbled == false) {
					// we haven't scrobbled this event
					// so nowsearch for the event on SongKick
					
					$dateString = $eventDate->format("Y-m-d");
					$params = array("apikey" => "D7XQBrzFd8K6bv9Z",
										"artists" => implode(",", $oPastEvent->getArtists()),
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
					
						$aEventAttributes = $oEvent->attributes();
					
						echo $aEventAttributes["type"], PHP_EOL;
					
						if ($aEventAttributes["type"] == "Concert") {
							// Only scrobble tracks form concerts because festivals are too messy
						
							// Try to find a setlist on SongKick for this event
							echo $aEventAttributes["displayName"], PHP_EOL;
							curl_setopt($curl, CURLOPT_URL, "http://api.songkick.com/api/3.0/events/" . $aEventAttributes["id"] . "/setlists.json?apikey=musichackdaylondon");
						
							$response = curl_exec($curl);
							$response = json_decode($response);
						
							$aSetlist = $response->{'resultsPage'}->{'results'}->{'setlist'};
						
							if ($aSetlist) {
								// we have a setlist so scrobble this track
							
								$trackTime = intval($oPastEvent->getStartDate());
								$trackLength = 60 * 4;
						
								foreach ($aSetlist[0]->{'setlistItem'} as $aSetlistItem) {
									// scrobble every track in the playlist
								
									$trackTimeDate = getdate($trackTime);
								
									echo $trackTimeDate["hours"] . " " . $trackTimeDate["minutes"] . PHP_EOL;
								
									echo $aSetlist[0]->{'artist'}->{'displayName'} . $aSetlistItem->{'name'} . $trackTime . PHP_EOL;
								
									$oScrob = new Scrobbler($aUser['user_name'], $aUser['user_session'], $oCall->getApiKey(), $oCall->getApiSecret());
						        	$scrobbleResponse = $oScrob->scrobble($aSetlist[0]->{'artist'}->{'displayName'}, $aSetlistItem->{'name'}, $trackTime);
					
									var_dump($scrobbleResponse);
					
									$trackTime += $trackLength;
								}
								
								// Make sure we don't scrobble it again!
								$oSession->addEventId($aUser['user_name'], $oPastEvent->getId());
							}
						}
					}
				}
			}
		}
	}

    print "OK";
