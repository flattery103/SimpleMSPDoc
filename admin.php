<?php

//Sanitize inputs
if(isset($_POST['security'])){$_POST['security']=filter_var($_POST['security'], FILTER_SANITIZE_STRING);}
if(isset($_POST['username'])){$_POST['username']=filter_var($_POST['username'], FILTER_SANITIZE_STRING);}
if(isset($_POST['password'])){$_POST['password']=filter_var($_POST['password'], FILTER_SANITIZE_STRING);}
if(isset($_POST['fname'])){$_POST['fname']=filter_var($_POST['fname'], FILTER_SANITIZE_STRING);}
if(isset($_POST['lname'])){$_POST['lname']=filter_var($_POST['lname'], FILTER_SANITIZE_STRING);}
if(isset($_GET['deluser'])){$_GET['deluser']=filter_var($_GET['deluser'], FILTER_SANITIZE_STRING);}
if(isset($_GET['id'])){$_GET['id']=filter_var($_GET['id'], FILTER_SANITIZE_STRING);}

if(isset($_GET['deluser'])){
	$query = "DELETE FROM users WHERE id='".$_GET['deluser']."'";
	$result = QueryMysql($query);
	echo "<script>alert('User Removed');</script>";
}

if(isset($_GET['a'])){
	if($_GET['a']=="users"){

		if(isset($_GET['add'])) {
			$session_string = rand();
			$session_string = sha1($session_string);
			$passnew=$_POST['password'];
			$password=password_hash($passnew, PASSWORD_DEFAULT);
			$query="INSERT INTO users (security,username,password,fname,lname,session_id) VALUES ('".$_POST['security']."','".$_POST['username']."','".$password."','".$_POST['fname']."','".$_POST['lname']."','".$session_string."')";
			$result = QueryMysql($query);
			echo "<script>alert('User Added');</script>";
		}elseif(isset($_GET['update'])) {
			if($_POST['password']!=""){
				$session_string = rand();
				$session_string = sha1($session_string);
				$passnew=$_POST['password'];
				$password=password_hash($passnew, PASSWORD_DEFAULT);
				$query = "UPDATE users SET security='".$_POST['security']."',username='".$_POST['username']."',password='$password',fname='".$_POST['fname']."',lname='".$_POST['lname']."',session_id='$session_string' WHERE id='".$_GET['id']."'";
			}else{
				$query = "UPDATE users SET security='".$_POST['security']."',username='".$_POST['username']."',fname='".$_POST['fname']."',lname='".$_POST['lname']."' WHERE id='".$_GET['id']."'";
			}
			$result = QueryMysql($query);
			echo "<script>alert('User ".$_POST['username']." Updated');</script>";
		}
		echo '<table class="company-grid"><thead><tr><th>User</th><th>First Name</th><th>Last Name</th><th>Security Level</th><th><a class="add-button" href="index.php?p=admin&a=adduserform">Add</a></th></tr></thead><tbody>';
		$query="SELECT * FROM users";
		$result = QueryMysql($query);
		while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
			echo '<td>'.$row['username'].'</td><td>'.$row['fname'].'</td><td>'.$row['lname'].'</td><td>'.$row['security'].' - '. $sec_levels[$row['security']] .'</td><td><a  href="index.php?p=admin&a=edituserform&id='.$row['id'].'">edit</a></td></tr>';
		}
		echo '</tbody></table>';
	}elseif($_GET['a']=="settings"){
		settingsPage();
	}elseif($_GET['a']=="adduserform"){
		userForm("addUser", 0);
	}elseif($_GET['a']=="edituserform"){
		userForm("editUser", $_GET['id']);
	}


}


function settingsPage(){
	echo '<div class="container">
		<div class="row">
			<div class="twelve columns">
			<a href="index.php?logout=true">Logout</a>
			</div>
		</div>';
}

function userForm($action, $id) {
	if($action=="editUser") {
		$query="SELECT * FROM users WHERE id='$id'";
		$result = QueryMysql($query);
		$row = @mysqli_fetch_array($result, MYSQLI_ASSOC);
		echo '<form action="index.php?p=admin&a=users&update=true&id='.$id.'" onSubmit ="return checkPassword(this)" method="POST">';
		$passmsg = " (Leave blank if not changing)";
		$submitTxt= "Update User";
	}else{
		echo '<form action="index.php?p=admin&a=users&add=true" onSubmit ="return checkPassword(this)" method="POST">';
		$submitTxt= "Add User";
	}

	echo '
	<div class="container"><div class="row"><div class="six columns">
		<label>First Name</label>
		<input class="u-full-width" type="text" placeholder="John" id="fname" name="fname" value="'.$row['fname'].'">
		</div><div class="six columns">
		<label>Last Name</label>
		<input class="u-full-width" type="text" placeholder="Doe" id="lname" name="lname" value="'.$row['lname'].'">
	</div>
	<div class="row">
		<div class="six columns">
		<label>Username</label>
		<input class="u-full-width" type="text" placeholder="Login Username" id="username" name="username" value="'.$row['username'].'">
		</div><div class="six columns">
		<label>Security Level</label>
		<input class="u-full-width" type="text" placeholder="0 - 20" id="security" name="security" value="'.$row['security'].'">
	</div>
	<div class="row">
		<div class="six columns">
		<label>Password '.$passmsg.'</label>
		<input class="u-full-width" type="password" placeholder="Monkey123" id="password" name="password">
		</div><div class="six columns">
		<label>Password Again</label>
		<input class="u-full-width" type="password" placeholder="Monkey123" id="password2" name="password2">
	</div>
	<div class="row">
		<div class="six columns">
		<input class="button-primary" type="submit" value="'.$submitTxt.'">
		</div>';
	if($action=="editUser") {
		echo '<div class="six columns">
                <a class="button button-primary" href="index.php?p=admin&a=users&deluser='.$row['id'].'" onclick="return confirm(\'Are you sure you want to delete this user\')">Delete User</a>
                </div>';
	}

	echo '</div>

	</div></form>';



        echo '<script>

            // Function to check Whether both passwords
            // is same or not.
            function checkPassword(form) {
                password1 = form.password.value;
                password2 = form.password2.value;

                // If password not entered
                if (password1 == \'\') {
		';
	if($action=="addUser") {
                    echo 'alert ("Please enter Password");
		    return false;
			';
	}
		echo '}

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

?>

