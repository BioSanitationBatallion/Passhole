<?php 
ini_set('session.cookie_lifetime','1440');
ini_set('session.gc_maxlifetime','1440');
session_start();
require_once('utilities.php');
?>
<html><head>
<script type="text/javascript">

// Stolen mostly verbatim from:
// https://stackoverflow.com/questions/1349404
function generatePassword()
{
	var text = "";
<?php 
	if ($INCLUDE_SYMBOLS) echo '    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()";'."\n";
	else                  echo '    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";'."\n";
	echo "    var passwordlength = $PWD_LENGTH;\n";
?>
	for( var i=0; i < passwordlength; i++ )
		text += possible.charAt(Math.floor(Math.random() * possible.length));
	
	document.getElementById('newpassword').value=text;
	return false;
}


function changeType(obj) {
	try {
		obj.setAttribute('type','text');
		obj.select();
		var retval = document.execCommand('copy');
		obj.setAttribute('type','password');
		if (retval) alert('Copied to clipboard');
	} catch (err) {
		alert('Unable to copy');
	}	
}
</script>

<style>
.blink {
  -webkit-animation: blink 1.2s steps(5, start) infinite;
  -moz-animation:    blink 1.2s steps(5, start) infinite;
  -o-animation:      blink 1.2s steps(5, start) infinite; 
  animation:         blink 1.2s steps(5, start) infinite;
}

@-webkit-keyframes blink {
  to { visibility: hidden; }
}
@-moz-keyframes blink {
  to { visibility: hidden; }
}
@-o-keyframes blink {
  to { visibility: hidden; }
}
@keyframes blink {
  to { visibility: hidden; }
}
</style>
</head>
<body>

<?php
// TODO: Keep working on Groups (folders)
$PHP_SELF=$_SERVER['PHP_SELF'];


// If we need to login...
if ( (!isset($_POST['email'])) && (!isset($_SESSION['passwordsafe'])) ) {
	?>
	<p>Not logged in.
	<form method="post">
	<p>Email Address: <input type=text name=email><br>
	Password: <input type=password name=pwd><br>
	<input type=submit value="Login"></form>

	<p><form method="post" action="signup.php"><input type=submit value="Sign Up"></form>

	<?php
	exit();
}
// We've just typed in user/pwd and now we need to check them.
elseif (!isset($_SESSION['passwordsafe'])) {
	$user=trim($_POST['email']);
	$pass=$_POST['pwd'];
	// Has this person failed to login too many times?
	$stmt = $db->prepare("select count(*) from failedlogins where ip=:ip and thetime between date('now','-$PERIOD hour') and date('now')");
	$stmt->bindValue(':ip',getIP());
	$res=$stmt->execute();
	$row=$res->fetchArray();
	$loginsleft=$FAILED_LOGINS-$row[0];
	if ($loginsleft<1) die ('You have failed to login too often.  Please try again later or contact <a href=\'http://endoftheinternet.com\'>Technical Support.</a>');
	// Does the user exist?
	$stmt = $db->prepare("select email from users where email = :user"); 
	$stmt->bindValue(':user',$user);
	$res=$stmt->execute();
	if (!$res->fetchArray()) {
		$db->exec("INSERT INTO failedlogins (ip) VALUES ('".getIP()."')"); 
		die("<span class=\"blink\"><font face=\"monospace\">&gt;&gt; Changes Locked Out &lt;&lt;</span><br> ------------------------<br>&nbsp;** Improper Request **<br> <span class=\"blink\">&nbsp;&nbsp;** Access Denied **</font></span><br><br><a href='$PHP_SELF'>Try again</a> or <a href=\"signup.php\">Sign Up</a>.");
	}
	// Encrypt the password and then compare it to that with which they signed up.
	if (!checkPassword($user,$pass)) {
		$db->exec("INSERT INTO failedlogins (ip) VALUES ('".getIP()."')"); 
		die('Login Incorrect. <a href="#">Try again</a> or <a href="signup.php">Sign Up</a>.');
	}
	else {
		$_SESSION['passwordsafe']=$pass;
		$_SESSION['email']=$user;
		// echo "<p><a href=\"".$_SERVER['PHP_SELF']."\">Continue...</a>";
	}
}
// If notes is set, we're trying to add/insert/delete.
elseif (isset($_POST['notes'])) {
	$enc= new Encryption($_SESSION['passwordsafe']);
	$id = $_POST['id'];
	$title = $_POST['title'];
	$notes = $enc->encrypt($_POST['notes']);
	$username = $enc->encrypt($_POST['username']);
	$password = $enc->encrypt($_POST['password']);
	if (isset($_POST['ChangeRecord'])) {
		$stmt=$db->prepare('UPDATE passwords set title=:title, username=:username, notes=:notes, encrypted=:password where email=:email and id=:id');
		$stmt->bindValue(':title',$title);
		$stmt->bindValue(':username',$username);
		$stmt->bindValue(':notes',$notes);
		$stmt->bindValue(':password',$password);
		$stmt->bindValue(':email',$_SESSION['email']);
		$stmt->bindValue(':id',$id);
		$stmt->execute();
	} elseif (isset($_POST['AddRecord'])) {
		$stmt=$db->prepare('INSERT INTO passwords (email,title,username,notes,encrypted) values (:email,:title,:username,:notes,:password)');
		$stmt->bindValue(':title',$title);
		$stmt->bindValue(':username',$username);
		$stmt->bindValue(':notes',$notes);
		$stmt->bindValue(':password',$password);
		$stmt->bindValue(':email',$_SESSION['email']);
		$res=$stmt->execute();
	} elseif (isset($_POST['DeleteRecord'])) {
		$stmt=$db->prepare("DELETE FROM passwords where email=:email and id=:id");
		$stmt->bindValue(':email',$_SESSION['email']);
		$stmt->bindValue(':id',$id);
		$res=$stmt->execute();
	}
}
elseif (isset($_POST['logoff'])) {
	unset($_SESSION['passwordsafe']);
	unset($_SESSION['email']);
	echo "<p>You have logged off.  <a href='$PHP_SELF'>Login</a>";
	exit();
}

?>
<form method="post" autocomplete="off">
<table>
<tr><th>Title<th>Username<th>Notes<th>Password<th></th>
<tr><td><form method="post"><input type=hidden name=id><input type=text name=title><td><input type=text name=username><td><textarea rows="1" cols="20" name=notes></textarea><td><input type=text name=password autocomplete="off" id="newpassword"><td><input type=submit value="Add New" name=AddRecord></form><td><input type=submit value="Generate Password" onclick="return generatePassword();">
<?php
$res = $db->query("select title,notes,encrypted,id,username from passwords where isteststring=0 and email='".$_SESSION['email']."'");
if (!$res->fetchArray()) { echo '</table><p>You have no current entries'; }
$res = $db->query("select title,notes,encrypted,id,username from passwords where isteststring=0 and email='".$_SESSION['email']."' order by lower(title)");
$enc = new Encryption($_SESSION['passwordsafe']);
while ($row=$res->fetchArray()) {
	$id=$row['id'];
	$title=$row['title'];
	$username=$enc->decrypt($row['username']);
	$notes=$enc->decrypt($row['notes']);
	$password=$enc->decrypt($row['encrypted']);
	echo '<tr><form method="post" autocomplete="off"><input type=hidden name=id value="'.$id.'"><td><input type=text name=title value="'.$title.'">';
	echo '<td><input type=text name=username value="'.$username.'">';
	echo '<td><textarea rows=1 cols=20 name="notes">'.$notes.'</textarea><td><input value="'.$password.'" type=password onclick="changeType(this)" name=password autocomplete="off"><td><input type=submit value="Change" name="ChangeRecord"><input type=submit onclick="return confirm(\'Really Delete this entry?\');" value="Delete" name=DeleteRecord></form>';
}
echo "</table>";
echo "<p><form method=\"post\"><input type=submit name=logoff value=logoff></form><form method=\"post\"><input type=submit name=refresh value=Refresh></form>";
?>


</body></html>
