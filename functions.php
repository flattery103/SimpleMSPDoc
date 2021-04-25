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
 		$session_id = reg_session($_POST['username']);
	} else {
		echo 'Login Incorrect';
	}
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
}


//Registers a random session ID on the users SQL table as well as a user Cookie
function reg_session($user){
	$user=filter_var($user, FILTER_SANITIZE_STRING);
	$session_string = rand();
	$session_string = sha1($session_string);
	$query = "UPDATE users SET session_id = '$session_string' WHERE username='$user'";
	$result = QueryMysql($query);
	setcookie("session_id",$session_string);
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
