<?php

// Change this to what you want.  Remember that the directory you list
// must be readwriteable to the web server. Do *NOT* create the file;
// it will be created automatically.
// While this file is likely useless to your average villain, you'd
// do well to keep it hidden away.  It should definitely not be 
// visible to the Internet.  Weak file permissions on a public server
// are just as bad.
$db_name='/var/sqlite-www/passwordsafe.sqlite3';

// The following blocks failed logins by IP for an interval of time.
// By default, an IP that fails to login 3 times must wait an hour
// before trying again.
$PERIOD=1;
$FAILED_LOGINS=3;

// The following is used to validate a user's passphrase when they log
// on.  You *must* change this to something else, preferably a random
// assortment of letters,numbers and punctuation.
$TEST_STRING="The Test String";

// If you use the generate password feature, you can change it's 
// behaviour here.
$PWD_LENGTH=18;
$INCLUDE_SYMBOLS=True;


// Leave everything below this line alone.
class MyDB extends SQLite3 {
	function __construct()
	{
		global $db_name;
		$this->open($db_name,SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
		if (!$this->query('select * from users limit 1')) {
			$this->exec("CREATE TABLE failedlogins ( ip text,
				thetime timestamp DEFAULT CURRENT_TIMESTAMP,
				id integer primary key)");
			$this->exec("CREATE TABLE groups ( email text NOT NULL,
				groupname text NOT NULL)");
			$this->exec("CREATE TABLE passwords ( email text,
    		title text, notes text, encrypted text,
				isteststring smallint DEFAULT 0, id integer primary key,
				username text,groupname text DEFAULT 'root')");
			$this->exec("CREATE TABLE users (email text primary key)");
			$this->exec("CREATE TABLE version (version_number integer)");
			$this->exec("insert into version values (1)");
		}
	}
}


$db = new MyDB();

?>
