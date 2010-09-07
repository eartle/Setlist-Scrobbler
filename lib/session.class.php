<?php

    class Session {
        const SECRET = 'badger';
        private $dbFile = 'sessions.db';
        private $db;

        function db($reinitialise = false) {
            if (!$this->db || $reinitialise) {
                $this->db = new PDO("sqlite:{$this->dbFile}");
            }
            return $this->db;
        }

        function createDb() {
            // Reinit the db to nuke the error that caused the create to occur
            $db = $this->db(true);

           	$result = $db->query('
                CREATE TABLE users (
					user_id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_name TEXT,
                    user_session TEXT
                )'
            );

	        $db->query('
	            CREATE TABLE events (
	                user_id INTEGER,
	                event_id INTEGER,
	
					PRIMARY KEY (user_id, event_id),
	                FOREIGN KEY (user_id) REFERENCES users(user_id) 
	            )'
	        );
        }

        function validate($session) {
            list($name, $hash) = explode('-', $session);
            return ($this->generate($name) === $session) ? $name : false;
        }

        function generate($name) {
            return $name.'-'.md5($name . self::SECRET);
        }

        function getUserByName($name) {
            $db = $this->db();

            $oUserFetch = $db->prepare('
                SELECT * FROM users WHERE user_name = ?'
            );

            // Create the table if it doesn't exist
            $oUserFetch->execute(
                array($name)
            );

            $aUserData = null;
            if ($aRows = $oUserFetch->fetchAll()) {
                $aUserData = $aRows[0];
            }

            return $aUserData;
        }

        function getUsers() {
            $db = $this->db();

            $oUserFetch = $db->prepare('
                SELECT * FROM users'
            );

            // Create the table if it doesn't exist
            $oUserFetch->execute();

            $aRows = $oUserFetch->fetchAll();

            return $aRows;
        }

        function createUser($name, $session) {
            $db = $this->db();

			$aUser = $this->getUserByName($name);

			if ($aUser) {
				// the user exists in the db so update their entry
				
				$oUserCreate = $db->prepare('
	                UPDATE users
					SET 
	                    user_session=?
	                WHERE
	                    user_name=?'
	            );

	            $aUserData = array(
	                $session, $name
	            );
			}
			else {
			
	            $oUserCreate = $db->prepare('
	                INSERT INTO users
	                    (user_name, user_session)
	                VALUES
	                    (?,?)'
	            );

	            $aUserData = array(
	                $name, $session
	            );
			}

            return $oUserCreate->execute($aUserData);
        }	

        function getEventIds($name) {
        	$db = $this->db();

            $oEventIdsFetch = $db->prepare('
                SELECT 
					events.event_id
				FROM
					users,
					events
				WHERE
					events.user_id = users.user_id
				AND
					users.user_name = ?'
            );

	        $oEventIdsFetchData = array(
	            $name
	        );

            $oEventIdsFetch->execute($oEventIdsFetchData);

            return $oEventIdsFetch->fetchAll();
		}

        function addEventId($name, $id) {
			$db = $this->db();

            $oEventInsert = $db->prepare('
                INSERT INTO
					events
				SELECT 
					user_id AS user_id,
					? AS events 
				FROM 
					users 
				WHERE 
					user_name=?'
            );

            $oEventInsertData = array(
                $id, $name
            );

            return $oEventInsert->execute($oEventInsertData);
        }
    }
