<html>
<HEAD>
<script type="text/javascript">
/*
Password Validator 0.1
(c) 2007 Steven Levithan <stevenlevithan.com>
MIT License
*/

function validatePassword (pw) {
	// default options (allows any password)


	var o = {
		lower:    1,
		upper:    1,
		alpha:    1, /* lower + upper */
		numeric:  1,
		special:  1,
		length:   [8, Infinity],
		custom:   [ /* regexes and/or functions */ ],
		badWords: [],
		badSequenceLength: 0,
		noQwertySequences: false,
		noSequential:      false
	};

	var	re = {
		lower:   /[a-z]/g,
		upper:   /[A-Z]/g,
		alpha:   /[A-Z]/gi,
		numeric: /[0-9]/g,
		special: /[\W_]/g
	},

	rule, i;

	function alertUser() {
		alert(  'Password rules:\n  '+
			'- Password must be between '+o.length[0]+' and '+o.length[1]+' in length.\n  '+
			'- The password must consist of the following characters:\n    '+
			o.lower+' lowercase characters\n    '+
			o.upper+' uppercase characters\n    '+
			o.alpha+' alphas\n    '+
			o.numeric+' numeric characters\n    '+
			o.special+' specials (_#$, etc)');
	}


	//var pw = document.forms[0].password.value;

	// enforce min/max length
	if (pw.length < o.length[0] || pw.length > o.length[1]) {
		alertUser();
		return false;
	}

	// enforce lower/upper/alpha/numeric/special rules
	for (rule in re) {
		if ((pw.match(re[rule]) || []).length < o[rule]) {
			alertUser();
			return false;
		}
	}

	// enforce word ban (case insensitive)
	for (i = 0; i < o.badWords.length; i++) {
		if (pw.toLowerCase().indexOf(o.badWords[i].toLowerCase()) > -1) {
			alertUser();
			return false;
		}
	}

	// enforce the no sequential, identical characters rule
	if (o.noSequential && /([\S\s])\1/.test(pw)) {
		alertUser();
		return false;
	}

	// enforce alphanumeric/qwerty sequence ban rules
	if (o.badSequenceLength) {
		var	lower   = "abcdefghijklmnopqrstuvwxyz",
		upper   = lower.toUpperCase(),
		numbers = "0123456789",
		qwerty  = "qwertyuiopasdfghjklzxcvbnm",
		start   = o.badSequenceLength - 1,
		seq     = "_" + pw.slice(0, start);
		for (i = start; i < pw.length; i++) {
			seq = seq.slice(1) + pw.charAt(i);
			if (
				lower.indexOf(seq)   > -1 ||
				upper.indexOf(seq)   > -1 ||
				numbers.indexOf(seq) > -1 ||
				(o.noQwertySequences && qwerty.indexOf(seq) > -1)
			) {
				alertUser();
				return false;
			}
		}
	}

	// enforce custom regex/function rules
	for (i = 0; i < o.custom.length; i++) {
		rule = o.custom[i];
		if (rule instanceof RegExp) {
			if (!rule.test(pw))
			return false;
		} else if (rule instanceof Function) {
			if (!rule(pw))
				return false;
		}
	}

	// great success!
	return true;
}

</script>
</head>
<body>
<?php
require_once('utilities.php');
require_once('header.php');



/*
$enc = new Encryption('mypass');
$encrypted_text = $enc->encrypt('this text is unencrypted');
echo "ENCRY=".$encrypted_text;echo "<br/>";

////I am using this part(decryption) coz data already encryption 
// Encrypted text from app 
// Decrypt text
$decrypted_text = $enc->decrypt($encrypted_text);
echo "ENCRY=".$decrypted_text;echo "<br/>";
*/


if (isset($_POST['username'])) {
	$user=trim($_POST['username']);
	$pwd1=$_POST['password1'];
	$pwd2=$_POST['password2'];
	
	if ($pwd1==$pwd2) {
		$res = $db->query("select * from users where email='$user'");
		$PHP_SELF=$_SERVER['PHP_SELF'];
		if ($res->fetchArray()) die ("Account already in use.  Please <a href='$PHP_SELF'>try again</a>.");
		$enc = new Encryption($pwd1);
		try {
			$db->exec("begin transaction");
			$db->exec("insert into users values ('$user')");
			$db->exec("insert into groups (email,groupname) values ('$user','root')");
			$db->exec("insert into passwords (email,encrypted,isteststring) values ('$user','".$enc->encrypt($TEST_STRING)."',1)");
			$db->exec("commit");
			die ('Sign up successfull. <a href="index.php">Login</a>');
		} catch (Exception $e) {
			$db->exec("rollback");
			die ("Error: $db->lastErrorMsg()");
		}
	}
	
}

?>


<form id="signupform" method="post" onSubmit="return validatePassword(this.password1.value)">
<p>Enter your email (username): <input type=text name=username><br>
Enter your password: <input type=password name=password1> Again: <input type=password name=password2><br>
<input type=submit>
</form>

</html>
</body>
