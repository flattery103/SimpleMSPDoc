<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta charset="utf-8">
  <title>Simple MSP Doc - INSTALLER</title>
  <meta name="description" content="Simple IT Documentation for MSP">
  <meta name="author" content="Rob Campbell">

  <!-- Mobile Specific Metas
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- FONT
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link href="//fonts.googleapis.com/css?family=Raleway:400,300,600" rel="stylesheet" type="text/css">

  <!-- CSS
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link rel="stylesheet" href="css/normalize.css">
  <link rel="stylesheet" href="css/skeleton.css">
  <link rel="stylesheet" href="css/style.css">

  <!-- Favicon
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <link rel="icon" type="image/png" href="images/favicon.png">

</head>
<body>
<?php
	include('config.php');

	if(isset($_GET['step'])){
		if($_GET['step']=="2") {
			//Provide a Create user form
			setupForm();
		}elseif($_GET['step']=="2") {
			//Install DB and check for succsess. If successful. Suggest delete install.php
		}elseif($_GET['step']=="3"){
			echo "<div class='container cat-box'><div class='row'><div class='twelve columns'>";
			createTables();
			echo "</div></div></div>";
		}

	}else{
		$success = 1;
		echo "<div class='container cat-box'><div class='row'>
		<div class='twelve columns'>Step 1 - Create an empty MySQL database and configure config.php with database settings</div>";
		echo "<div class='twelve columns'>DB host set - "; if($host=="CHANGEME!") { echo "Fail"; $success=0;}else{echo "Success";} echo "</div>";
		echo "<div class='twelve columns'>DB user set - "; if($user=="CHANGEME!") { echo "Fail"; $success=0;}else{echo "Success";} echo "</div>";
		echo "<div class='twelve columns'>DB pass set - "; if($pass=="CHANGEME!") { echo "Fail"; $success=0;}else{echo "Success";} echo "</div>";
		echo "<div class='twelve columns'>DB dbname set - "; if($dbname=="CHANGEME!") { echo "Fail"; $success=0;}else{echo "Success";} echo "</div>";
		if($success==1){
			echo "<div class='twelve columns'><a class='button button-primary' href='install.php?step=2'>Continue to Step 2</a></div>";
		}else{
			echo "<div class='twelve columns'><br>config.php needs to be configured. Click Refesh when ready. <br><a class='button button-primary' href='install.php'>Refresh</a></div>";
		}
		//Check if file is updated
	}





function setupForm(){
        echo '
	<form action="install.php?step=3" method="POST">
        <div class="container"><div class="row"><div class="four columns">
                <label>First Name</label>
                <input class="u-full-width" type="text" placeholder="John" id="fname" name="fname">
                </div><div class="four columns">
                <label>Last Name</label>
                <input class="u-full-width" type="text" placeholder="Doe" id="lname" name="lname">
        	</div><div class="four columns">
                <label>Username</label>
                <input class="u-full-width" type="text" placeholder="Login Username" id="username" name="username">
                </div>
        </div>
        <div class="row">
                <div class="six columns">
                <label>Password</label>
                <input class="u-full-width" type="password" placeholder="Monkey123" id="password" name="password">
                </div><div class="six columns">
                <label>Password Again</label>
                <input class="u-full-width" type="password" placeholder="Monkey123" id="password2" name="password2">
		</div>
        </div>
        <div class="row">
                <div class="six columns">
                <input class="button-primary" type="submit" value="Continue to Step 3">
                </div>
       </div>

        </div></form>';
        echo '<script>

            // Function to check Whether both passwords
            // is same or not.
            function checkPassword(form) {
                password1 = form.password.value;
                password2 = form.password2.value;

                // If password not entered
                if (password1 == \'\') {
                    alert ("Please enter Password")
                    return false;
                }

                // If confirm password not entered
                else if (password2 == \'\') {
                    alert ("Please enter confirm password");
                    return false;
                }

                // If Not same return False.
                else if (password1 != password2) {
                    alert ("\nPassword did not match: Please try again...")
                    return false;
                }

                // If same return True.
                else{
                    return true;
                }
            }
        </script>';
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


function createTables(){
	$query="CREATE TABLE assets (id int NOT NULL AUTO_INCREMENT,company_id int NOT NULL,item_type varchar(100) NOT NULL,tab_name varchar(100) NOT NULL,notes text NOT NULL,column1 varchar(255) NOT NULL,column2 varchar(255) NOT NULL,column3 varchar(255) NOT NULL,column4 varchar(255) NOT NULL,column5 varchar(255) NOT NULL,PRIMARY KEY (id))";
	QueryMysql($query);
	echo "assets table created<br>";

	$query="CREATE TABLE companies (id int NOT NULL AUTO_INCREMENT,account varchar(255) NOT NULL,company varchar(255) NOT NULL,contact varchar(255) NOT NULL,email varchar(255) NOT NULL,phone varchar(20) NOT NULL,location varchar(255) NOT NULL,notes text NOT NULL,PRIMARY KEY (id))";
	QueryMysql($query);
	echo "companies table created<br>";


	$query = "CREATE TABLE knowledge (id int NOT NULL AUTO_INCREMENT,category varchar(255) NOT NULL,title varchar(255) NOT NULL,tags varchar(255) NOT NULL,document text NOT NULL,PRIMARY KEY (id))";
	QueryMysql($query);
	echo "knowledge table created<br>";


	$query="CREATE TABLE links (id int NOT NULL AUTO_INCREMENT,name varchar(255) NOT NULL,link varchar(255) NOT NULL,featured tinyint(1) NOT NULL DEFAULT 0,PRIMARY KEY (id))";
	QueryMysql($query);
	echo "links table created<br>";


	$query="CREATE TABLE users (id int NOT NULL AUTO_INCREMENT,security int NOT NULL DEFAULT 1000,username varchar(255) NOT NULL,password varchar(255) NOT NULL,fname varchar(255) NOT NULL,lname varchar(255) NOT NULL,session_id varchar(255) NOT NULL,PRIMARY KEY (id))";
	QueryMysql($query);
	echo "users table created<br>";

	$session_string = rand();
	$session_string = sha1($session_string);
	$passnew=$_POST['password'];
	$password=password_hash($passnew, PASSWORD_DEFAULT);
	$query="INSERT INTO users (id, security, username, password, fname, lname, session_id) VALUES(1, 0, '".$_POST['username']."','$password', '".$_POST['fname']."', '".$_POST['lname']."','$session_string')";
	QueryMysql($query);
	echo "First user created<br>";

	echo "<b>Installation Complete</b> please delete install.php<br><a href='index.php'>Continue to Login</a>";

}
?>

</body>
</html>
