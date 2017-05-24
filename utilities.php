<?php

require 'config.php';

class Encryption
{
static $cypher = 'blowfish';
static $mode   = 'cfb';
static $iv	= '12345678';
public $key;
public $td;

function __construct($key) {
	$this->key = $key;
	$this->td = mcrypt_module_open(self::$cypher, '', self::$mode, '');
	mcrypt_generic_init($this->td, $this->key, self::$iv);
}

public function encrypt($plaintext) {
	if ($plaintext=='') return '';
	mcrypt_generic_init($this->td, $this->key, self::$iv);
	$crypttext = mcrypt_generic($this->td, $plaintext);
	return base64_encode($crypttext);
}

public function decrypt($crypttext) {
	if ($crypttext=='') return '';
	mcrypt_generic_init($this->td, $this->key, self::$iv);
	$plaintext = mdecrypt_generic($this->td, base64_decode($crypttext));
	return $plaintext;
}

function __destruct() {
	mcrypt_generic_deinit($this->td);
}


} // End of class


function checkPassword($user,$password) {
	global $TEST_STRING;
	global $db;
	$enc = new Encryption($password);
	$encrypted = $enc->encrypt($TEST_STRING);
	$stmt = $db->prepare("select encrypted from passwords where isteststring=1 and email=:email");
	$stmt->bindValue(':email',$user);
	$res=$stmt->execute();
	$row=$res->fetchArray();
	if ($row && $row[0]==$encrypted) 
		return true;
	else
		return false;
}


function getIP() {
	if (getenv("HTTP_CLIENT_IP")) $ip = getenv("HTTP_CLIENT_IP");
	else if(getenv("HTTP_X_FORWARDED_FOR")) $ip = getenv("HTTP_X_FORWARDED_FOR");
	else if(getenv("REMOTE_ADDR")) $ip = getenv("REMOTE_ADDR");
	else $ip = "UNKNOWN";
	return $ip;
}


// Not immplemented yet.
function changePassword($username,$oldpassword,$newpassword) {
	global $conn;

	if (!checkPassword($username,$oldpassword)) return array(-1,'Password Failed.');

	// Copy everything about this fellow to array
	$enc = new Encryption($oldpassword);
	$res = sqlite_query($conn,"select title,notes,encrypted,id,username from passwords where isteststring=0 and email='".$_SESSION['email']."' order by title");
	while ($row=sqlite_fetch_object($res)) {
		$row->encrypted=$enc;
	}

}

# Stolen from https://stackoverflow.com/questions/4356289/php-random-string-generator/
function generatePassword() {
		global $INCLUDE_SYMBOLS;
		global $PWD_LENGTH;
		$x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		if ($INCLUDE_SYMBOLS) $x.='!@#$%^&*()';
    return substr(str_shuffle(str_repeat($x, ceil($PWD_LENGTH/strlen($x)) )),1,$length);
}

?>
