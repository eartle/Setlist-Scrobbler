<?php

    class Database {
        const SECRET = 'badger';
        private $dbFile = 'lib/sessions.db';
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

            $result = $db->query(
                'CREATE TABLE users (
                    user_id INTEGER PRIMARY KEY AUTOINCREMENT,
                    user_name TEXT,
                    user_session TEXT
                )'
            );

            $db->query(
                'CREATE TABLE events (
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

            $oUserFetch = $db->prepare('SELECT * FROM users WHERE user_name = ?');

            // Create the table if it doesn't exist
            $oUserFetch->execute(array($name));

            $aUserData = null;
            if ($aRows = $oUserFetch->fetchAll()) {
                $aUserData = $aRows[0];
            }

            return $aUserData;
        }

        function getUsers() {
            $db = $this->db();

            $oFetch = $db->prepare('SELECT * FROM users');
            $oFetch->execute();
            return $oFetch->fetchAll();
        }

        function getActiveUserCount() {
            $db = $this->db();

            $oFetch = $db->prepare('SELECT COUNT(*) FROM users
                                    WHERE users.user_session IS NOT \'\' OR null');
            
            $oFetch->execute();
            $aResult = $oFetch->fetchAll();
            return $aResult[0][0];
        }

        function getTopEvents($page, $limit) {
            $db = $this->db();

            $oFetch = $db->prepare('SELECT events.event_id, count(*) as event_count
                                    FROM events
                                    GROUP BY events.event_id
                                    ORDER BY event_count DESC
                                    LIMIT ?, ?');
            
            $oFetch->execute(array($page * $limit, $limit));
            return $oFetch->fetchAll();
        }

        function getEventCount() {
            $db = $this->db();

            $oFetch = $db->prepare('SELECT COUNT(*) FROM events');
            
            $oFetch->execute();
            $aResult = $oFetch->fetchAll();
            return $aResult[0][0];
        }

        function getUserEventCounts($page, $limit) {
            $db = $this->db();

            $oUserFetch = $db->prepare('SELECT users.user_name, users.user_session, count(events.event_id) as event_count
                                        FROM users, events
                                        WHERE events.user_id = users.user_id
                                        GROUP BY users.user_name
                                        ORDER BY event_count DESC
                                        LIMIT ?, ?');
            
            $oUserFetch->execute(array($page * $limit, $limit));
            return $oUserFetch->fetchAll();
        }

        function createUser($name, $session) {
            $db = $this->db();

            $aUser = $this->getUserByName($name);

            if ($aUser) {
                // the user exists in the db so update their entry

                if ($session != "") {
                    mail('eartle@gmail.com', $name . ' just re-registered.', '...and I have nothing more to say about that.', "From: eartle@mobbler.co.uk\n");
                }
                
                $oUserCreate = $db->prepare(
                    'UPDATE users
                     SET user_session=?
                     WHERE user_name=?');

                $aUserData = array($session, $name);
            }
            else {
            
                mail('eartle@gmail.com', $name . ' just registered.', '...and I have nothing more to say about that.', "From: eartle@mobbler.co.uk\n");
            
                $oUserCreate = $db->prepare(
                    'INSERT INTO users (user_name, user_session)
                     VALUES (?,?)');

                $aUserData = array($name, $session);
            }

            return $oUserCreate->execute($aUserData);
        }   

        function deleteUser($name) {
            $db = $this->db();
                
            $oUserDelete = $db->prepare('DELETE FROM users WHERE user_name=?');
            $aUserData = array($name);
            return $oUserDelete->execute($aUserData);
        }   

        function getUserEventIds($name) {
            $db = $this->db();

            $oEventIdsFetch = $db->prepare(
                'SELECT events.event_id
                 FROM users, events
                 WHERE events.user_id = users.user_id
                 AND users.user_name = ?
                 ORDER BY events.event_id DESC');

            $oEventIdsFetchData = array($name);
            $oEventIdsFetch->execute($oEventIdsFetchData);
            return $oEventIdsFetch->fetchAll();
        }

        function getUserEventIdsPaged($name, $page, $limit) {
            $db = $this->db();

            $oEventIdsFetch = $db->prepare(
                'SELECT events.event_id
                 FROM users, events
                 WHERE events.user_id = users.user_id
                 AND users.user_name = ?
                 ORDER BY events.event_id DESC
                 LIMIT ?, ?');

            $oEventIdsFetchData = array($name, $page * $limit, $limit);
            $oEventIdsFetch->execute($oEventIdsFetchData);
            return $oEventIdsFetch->fetchAll();
        }

        function getUserEventCount($name) {
            $db = $this->db();

            $oEventIdsFetch = $db->prepare(
                'SELECT count(*)
                 FROM users, events
                 WHERE events.user_id = users.user_id
                 AND users.user_name = ?');

            $oEventIdsFetch->execute(array($name));
            return $oEventIdsFetch->fetchAll()[0][0];
        }

        function addEventId($name, $id) {
            $db = $this->db();

            $oEventInsert = $db->prepare(
                'INSERT INTO events
                 SELECT user_id, ?
                 FROM users 
                 WHERE user_name=?');

            $oEventInsertData = array($id, $name);
            $queryResult = $oEventInsert->execute($oEventInsertData);
            return $queryResult;
        }
    }
?>
