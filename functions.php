<?php

//log off user
if(isset($_GET['logout'])) {
	$session_string = rand();
	$session_string = sha1($session_string);
	$query = "UPDATE users SET session_id = '$session_string' WHERE session_id='".$_COOKIE['session_id']."'";
	$result = QueryMysql($query);
}


//Initiate logging in. Check password and register login
if(isset($_POST['Login'])) {
	$_POST['username']=filter_var($_POST['username'], FILTER_SANITIZE_STRING);
	$query = "SELECT * FROM users WHERE username='$_POST[username]'";
	$result = QueryMysql($query);
      $row = @mysqli_fetch_array($result, MYSQLI_ASSOC);
	if (password_verify($_POST['userpass'], $row['password'])) {
 		$session_id = reg_session($_POST['username'],$row['req_mfa']);
	} else {
		echo 'Login Incorrect';
	}
}

//MFA Code Entered
if(isset($_POST['mfa_submit'])) {
	mfaCheck($_POST['mfa']); //Run mfaCheck to confirm if correct or not.
}

//Check if logged in
if (!$_COOKIE['session_id'] && !$session_id) {
	include('login.php');
	exit;
}else{
	if(!isset($session_id)){$session_id=filter_var($_COOKIE['session_id'], FILTER_SANITIZE_STRING);}
	$query = "SELECT * FROM users WHERE session_id='$session_id'";
	$result = QueryMysql($query);
        $row = @mysqli_fetch_array($result, MYSQLI_ASSOC);
	if($row['username']==""){
		include('login.php');
		exit;
	}
	//If MFA set required and  Code is set, then check if correct to show form.
	if(($row['req_mfa']==1) && ($row['mfa']>999)) { mfaCheck('0'); }
}







//MFA Check and Verify
function mfaCheck($mfa) {
	if($mfa>999) {
		$query = "SELECT * FROM users WHERE session_id='".$_COOKIE['session_id']."'";
		$result = QueryMysql($query);
		$row = @mysqli_fetch_array($result, MYSQLI_ASSOC);
		//echo "$mfa --" . $row['mfa'];
		if($mfa==$row['mfa']){
			//echo "MFA Correct";
			$query = "UPDATE users SET mfa='0' WHERE session_id='".$_COOKIE['session_id']."'";
			$result = QueryMysql($query);
		}else{
			echo "MFA incorrect check";
			mfaForm();
			exit;
		}
	}else{
		//echo "check $mfa";
		mfaForm();
		exit;
	}
}

function mailMFA($username){
	$query = "SELECT * FROM users WHERE username='$username'";
	$result = QueryMysql($query);
	$row = @mysqli_fetch_array($result, MYSQLI_ASSOC);

	$random_hash = md5(date('r', time())); 
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
	$FromEmail = "noreply@StarHostDesign.com";

	$Message = "Here's your one-time verification token: " . $row['mfa'];

	$MailDate=Date(r) . " (CDT)";
	mail($row['email'], "Authentication Token", "$Message", $headers."Date: $MailDate\nFrom: \"Simple MSPDoc\" <$FromEmail> \r\nReply-To: $FromEmail\r\n");

	//echo "Send email to $row[email] with MFA $row[mfa]";
}

//Registers a random session ID on the users SQL table as well as a user Cookie
function reg_session($user, $mfa_req){
	$user=filter_var($user, FILTER_SANITIZE_STRING);
	$session_string = rand();
	$mfa_gen = random_int(100000, 999999);
	$session_string = sha1($session_string);
	$query = "UPDATE users SET session_id = '$session_string', mfa = '$mfa_gen' WHERE username='$user'";
	$result = QueryMysql($query);
	setcookie("session_id",$session_string);
	if($mfa_req==1){mailMFA($user);}
	return $session_string;
}



//SQL boilerplate
function QueryMysql($query) {
	include('config.php');
	$con=mysqli_connect($host,$user,$pass,$dbname);

	if (mysqli_connect_errno()) {
		echo "Failed to connect to MySQL: " . mysqli_connect_error();
		exit();
	}

	$result = mysqli_query($con,$query);
	return $result;
}


function mfaForm(){
echo '<html>
<title>Authorized personel only - MFA</title>
<head>
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

        <form action="index.php" method=post><br clear=all /><center><table class="login-box" id="login">
        <tr><th></th><th colspan="2" align="left">Please Enter Your MFA Code</th>
        <tr><td width="15"></td><td id="login">MFA:</td><td><input type="text" name="mfa"></td></tr>
        <tr><td colspan="3" style="text-align: center" valign="middle">
        <input class="button" type="submit" name="mfa_submit" value="Enter"> <a class="button button-primary" href="index.php?logout=true">Cancel</a>
        </td></tr></table></center>
        </form>

</body>
</html>';
}

function securityLevel(){
	$query="SELECT * FROM users WHERE session_id='" . $_COOKIE['session_id'] . "'";
	$result = QueryMysql($query);
	$row = @mysqli_fetch_array($result, MYSQLI_ASSOC);
	return $row['security'];
}


function ckeditorJS($id) {
	$test= "<script>
	ClassicEditor
	    .create( document.querySelector( '$id' ) )
	    .then( editor => {
	        console.log( editor );
	    } )
	    .catch( error => {
	        console.error( error );
	    } );
	</script>";

echo "<script>ClassicEditor
			.create( document.querySelector( '$id' ), {

				toolbar: {
					items: [
						'bold',
						'italic',
						'underline',
						'|',
						'removeFormat',
						'strikethrough',
						'highlight',
						'|',
						'link',
						'|',
						'bulletedList',
						'numberedList',
						'outdent',
						'indent',
						'|',
						'insertTable',
						'codeBlock',
						'horizontalLine',
						'|',
						'undo',
						'redo'
					]
				},
				language: 'en',
				table: {
					contentToolbar: [
						'tableColumn',
						'tableRow',
						'mergeTableCells'
					]
				},
				licenseKey: '',

			} )
			.then( editor => {
				window.editor = editor;
			} )
			.catch( error => {
				console.error( error );
			} );
	</script>";

}

//Security Levels
        $sec_levels = array(0 => 'Global Admin',20 => 'Read Only');
?>
