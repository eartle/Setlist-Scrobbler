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

            $db->query(
                'CREATE TABLE users (
                    name string,
                    enabled string,
                    key string,
                    PRIMARY KEY (name)
                );'
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

            $oUserFetch = $db->prepare(
                'SELECT * FROM users WHERE name = ?;'
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

        function createUser($name, $key) {
            $db = $this->db();
			$enabled = 'true';

            $oUserInsert = $db->prepare('
                INSERT INTO users
                    (name, key, enabled)
                VALUES
                    (?,?,?);'
            );

            $aUserData = array(
                $name, $key, $enabled
            );

            return $oUserInsert->execute($aUserData);
        }
		
		function setUserEnabled($name, $enabled) {
            $db = $this->db();

            $oUserUpdate = $db->prepare('
                UPDATE users SET enabled = ? WHERE name = ?;'
            );

            $aUserData = array(
                $enabled, $name
            );

            return $oUserUpdate->execute($aUserData);
        }
		
		
    }
