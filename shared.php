<?php

    function scrobbleUser($aUser, $oDatabase) {    
        // Find the Setlist Scrobbler account so that we can
        // shout at the user when their event gets scrobbled
        $aSlsUser = $oDatabase->getUserByName('setscrobbler');
        $slsSession = new Session( $aSlsUser['user_name'], $aSlsUser['user_session'], false );

        try {
            print(PHP_EOL . PHP_EOL . $aUser["user_name"] );

            if ($aUser['user_session'] == "") {
                print(' is not authenticated. :(' . PHP_EOL );
            } else {
            
                // fetch this user's past Last.fm events!
                $aPastEvents = User::getPastEvents($aUser['user_name'], 14);
                
                $aScrobbledEvents = $oDatabase->getUserEventIds($aUser['user_name']);
                
                foreach ($aPastEvents as $oPastEvent) {
                
                    if ($oPastEvent->getStatus() == Event::ATTENDING) { 
                        // they attended this event
                        
                        print(PHP_EOL . $oPastEvent->getTitle());
                                    
                        $two_weeks_ago = strtotime('now - 2 weeks');
                        $date = getdate($oPastEvent->getStartDate());
                        $eventDate = new DateTime();
                        $eventDate->setDate($date["year"], $date["mon"], $date["mday"]);
                        $eventDate->setTime($date["hours"], $date["minutes"]);
                        
                        if ($oPastEvent->getStartDate() < $two_weeks_ago) {
                            // we can't scrobble tracks older than 4 weeks so just stop
                            print(' was over two weeks ago.' );
                            break;
                        }
                        
                        $scrobbled = false;
                        
                        // check if this event has been scrobbled for this user
                        foreach ($aScrobbledEvents as $eventId) {
                            if ($eventId["event_id"] == $oPastEvent->getId()) {
                                // we have already scrobbled this event
                                
                                print( " has already been scrobbled." );
                                $scrobbled = true;
                                break;
                            }
                        }
                        
                        if ($scrobbled == false) {
                            // we haven't scrobbled this event
                            // so now search for the event on setlist.fm
                            
                            $aPastEventArtists = $oPastEvent->getArtists();
                            $headliner = $aPastEventArtists['headliner'];
                            print(' has headliner ' . $headliner);
                            
                            
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

                            $notFoundPosition = strpos($response, 'not found');

                            if ($notFoundPosition === 0) {
                                // the string 'not found' was found at position 0 
                                print( ', but has not been found on setlist.fm.');
                            } else {

                                try {
                                    $response = new SimpleXMLElement($response);
                        
                                    foreach ($response->setlist as $oSetlist) {
                                        // Look through the Setlist.fm search responses
                                        $aArtistAttributes = $oSetlist->artist->attributes();
                                    
                                        if ($aArtistAttributes["name"] == $headliner) {
                                            // This is the setlist for the headliner
                                            print( ' and has been found on setlist.fm');
                                            
                                            if ($oSetlist->sets->set) {
                                                print( ' there is at least one set with headliner ' . $headliner);

                                                // email myself so that I can celebrate!
                                                mail('eartle@gmail.com', 'An event was scrobbled!', $aUser['user_name'] . ' scrobbled the event: ' . $oPastEvent->getTitle(), "From: eartle@mobbler.co.uk\n");
                                                
                                                // Assume that the headliner starts 2 hours after the doors open
                                                // and that tracks last about 4 minutes
                                                $trackTime = intval($oPastEvent->getStartDate());
                                                $trackTime += 60 * 120;
                                                $trackLength = 60 * 4;

                                                // create session for this user to do the scrobbling
                                                $session = new Session( $aUser['user_name'], $aUser['user_session'], false );

                                                try {
                                                                    
                                                    foreach ($oSetlist->sets->set as $oSet) {
                                                        // we have a set so scrobble it
                                                        print( ' Set: ' . PHP_EOL);
                                            
                                                        foreach ($oSet->song as $aSong) {
                                                            // scrobble every track in the set
                                                            $aSongAttributes = $aSong->attributes();
                                                            
                                                            $trackTitle = $aSongAttributes["name"];

                                                            if ($trackTitle != "") {
                                                                $trackTimeDate = getdate($trackTime);
                                                                
                                                                print( $headliner . " - " . $trackTitle . " @ " . $trackTimeDate["hours"] . ":" . $trackTimeDate["minutes"] . PHP_EOL);
                                                        
                                                                $scrobbleResponse = Track::scrobble($headliner, $trackTitle, $trackTime, $session);
                                                                //var_dump($scrobbleResponse);
                                                            }
                                        
                                                            $trackTime += $trackLength;
                                                        }

                                                        // Add a track length waiting for the band to come back on stage "BRAVO! ETC!"
                                                        $trackTime += $trackLength;
                                                    }

                                                    // Make sure we don't scrobble it again!
                                                    $oDatabase->addEventId($aUser['user_name'], $oPastEvent->getId());

                                                    // Shout at the user from the setlist scrobble account so that they know something has happened
                                                    $message = 'The event [url=' . $oPastEvent->getUrl() . ']' . $oPastEvent->getTitle() . '[/url] has had its [url=' . $oSetlist->url . ']Setlist.fm setlist[/url] scrobbled for you by [url=http://mobbler.co.uk/sls/]Setlist Scrobbler[/url].';
                                                    $shoutResponse = User::shout($aUser['user_name'], $message, $slsSession);
                                                    //var_dump($shoutResponse);
                                                } catch (Error $e) {
                                                    // There was a problem scrobbling a track
                                                    print( 'Caught Error: ' . $e->getCode() . ' ' . $e->getMessage() . PHP_EOL);

                                                    mail('eartle@gmail.com', 'A scrobbling Error occoured!', $aUser['user_name'] . ' ' . $e->getCode() . ' ' . $e->getMessage(), "From: eartle@mobbler.co.uk\n");

                                                    if ($e->getCode() == 9) {
                                                        // it was an auth problem so make sure we don't try this user again
                                                        $oDatabase->createUser($aUser['user_name'], "");

                                                        // They probably deauthed on purpose, but shout at them just in case
                                                        //$message = 'Your auth details have become invalid. Please re-authenticate with [url=http://mobbler.co.uk/sls/]Setlist Scrobbler[/url] if you wish to continue scrobbling setlists.';
                                                        //$shoutResponse = User::shout($aUser['user_name'], $message, $slsSession);
                                                        //var_dump($shoutResponse);
                                                    }
                                                } catch (Exception $e) {
                                                    print( 'Caught Exception: ' . $e->getCode() . ' ' . $e->getMessage() . PHP_EOL);

                                                    mail('eartle@gmail.com', 'A scrobbling Exception occoured!', $aUser['user_name'] . ' ' . $e->getCode() . ' ' . $e->getMessage(), "From: eartle@mobbler.co.uk\n");
                                                }
                                            } else {
                                                print( ', but doesn\'t have a setlist. :(');
                                            }
                                        }
                                    }
                                } catch (Exception $e) {
                                    print( 'Caught Exception: ' . $e->getCode() . ' ' . $e->getMessage() . PHP_EOL);

                                    mail('eartle@gmail.com', 'An event processing error occoured!', $aUser['user_name'] . ' ' . $e->getCode() . ' ' . $e->getMessage() . ' - Headliner: ' . $headliner, "From: eartle@mobbler.co.uk\n");
                                }
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            print( 'Caught Exception: ' . $e->getCode() . ' ' . $e->getMessage() . PHP_EOL);

            if ($e->getCode() != 3) {
                // don't email for error code '3' "Invalid Method - No method with that name in this package"
                // Last.fm currently isn't working for user's events, for example "user.getPastEvents"
                mail('eartle@gmail.com', 'A user processing error occoured!', $aUser['user_name'] . ' ' . $e->getCode() . ' ' . $e->getMessage(), "From: eartle@mobbler.co.uk\n");
            } 
            
            if ($e->getCode() == 6) {
                // "invalid parameters" this is always "Invalid user name supplied"
                // which means they've deleted their account so delete them here too
                var_dump($oDatabase->deleteUser($aUser['user_name']));
            }
        }
    }

?>
