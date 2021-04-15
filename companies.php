<?php
	//Sanitize inputs
	if(isset($_GET['c'])){$_GET['c']=filter_var($_GET['c'], FILTER_SANITIZE_STRING);}
	if(isset($_GET['a'])){$_GET['a']=filter_var($_GET['a'], FILTER_SANITIZE_STRING);}

	//If no company is selected, show the list of companies
	if(!isset($_GET['c'])){
		if(!isset($_GET['aa'])) {
			echo '<table class="company-grid"><thead><tr><th>Acctout</th><th>Company</th><th>Contact</th><th>Email</th><th>Location</th><th><a class="add-button" href="'.$_SERVER['REQUEST_URI'].'&aa=addcompany">Add</a></th></tr></thead><tbody>';
			$query = "SELECT * FROM companies";
			$result = QueryMysql($query);
		        while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
				echo "<tr><td>".$row['account']."</td><td><a href='".$_SERVER['REQUEST_URI']."&c=".$row['id']."'>  ".$row['company']."</td><td>".$row['contact']."</td><td>".$row['email']."</td><td>".$row['location']."</td><td></td></tr>";
			}
			echo '</tbody></table>';
		}else{
			if($_GET['aa']=="addcompany"){
				if(isset($_GET['add'])){
					if(isset($_POST['account'])){$_POST['account']=filter_var($_POST['account'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['company'])){$_POST['company']=filter_var($_POST['company'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['contact'])){$_POST['contact']=filter_var($_POST['contact'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['email'])){$_POST['email']=filter_var($_POST['email'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['phone'])){$_POST['phone']=filter_var($_POST['phone'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['location'])){$_POST['location']=filter_var($_POST['location'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['notes'])){$_POST['notes']=filter_var($_POST['notes'], FILTER_SANITIZE_STRING);}
					$query = "INSERT INTO companies (account, company, contact, email, phone, location, notes) VALUES ('".$_POST['account']."','".$_POST['company']."','".$_POST['contact']."','".$_POST['email']."','".$_POST['phone']."','".$_POST['location']."','".$_POST['notes']."')";
					$result = QueryMysql($query);
					echo "<script>alert('".$_POST['company']." Added')</script>";
				}
				//Add Company Form
				echo '
				<form action="'.$_SERVER['REQUEST_URI'].'&add=true" method="POST">
				<div class="container"><div class="row"><div class="six columns">
					<label for="exampleEmailInput">Account</label>
					<input class="u-full-width" type="text" placeholder="123456" id="account" name="account">
					</div><div class="six columns">
					<label for="exampleEmailInput">Company</label>
					<input class="u-full-width" type="text" placeholder="Acme Tools" id="company" name="company">
				</div>
				<div class="row">
					<div class="six columns">
					<label for="exampleEmailInput">Contact</label>
					<input class="u-full-width" type="text" placeholder="John Doe" id="contact" name="contact">
					</div><div class="six columns">
					<label for="exampleEmailInput">Email</label>
					<input class="u-full-width" type="email" placeholder="test@domain.com" id="email" name="email">
				</div>
				<div class="row">
					<div class="six columns">
					<label for="exampleEmailInput">Phone</label>
					<input class="u-full-width" type="text" placeholder="555-555-5555" id="phone" name="phone">
					</div><div class="six columns">
					<label for="exampleEmailInput">Location</label>
					<input class="u-full-width" type="text" placeholder="New Ulm" id="location" name="location">
				</div>
				<div class="row">
					<div class="twelve columns">
					<label for="exampleMessage">Notes</label>
					<textarea class="u-full-width" placeholder="Type in any extra notes that doesn\'t fit anywhere else" id="notes" name="notes"></textarea>
					<input class="button-primary" type="submit" value="Add Company">
					</div>
				</div>

				</div></form>';
			}
		}
	}



	//If a company is selected but asset isn't selected show asset list
	else {
		if(!isset($_GET['aa'])){
			//first need to get list of tabs and show them.
			$query = "SELECT DISTINCT tab_name FROM assets WHERE company_id='".$_GET['c']."'";
			$result = QueryMysql($query);
	                while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
				echo '<a class="button" href="'.$_SERVER['REQUEST_URI'].'&a='.$row['tab_name'].'">'.$row['tab_name'].'</a> ';
			}
			echo '<a class="button" href="'.$_SERVER['REQUEST_URI'].'&aa=addtab">Add</a> ';

			//If tab is selected, show those assets.
			if(isset($_GET['a'])) {
				//$query = "SELECT * FROM assets WHERE company_id='".$_GET['c']."' AND tab_name='".$_GET['a']."' AND item_type='asset'";
				$query = "SELECT * FROM assets WHERE company_id='".$_GET['c']."' AND tab_name='".$_GET['a']."'";
				$result = QueryMysql($query);
				echo '<table class="company-grid">';
		                while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
					if($row['item_type']=="header") { echo '<thead><tr><th>'.$row['column1'].'</th><th>'.$row['column2'].'</th><th>'.$row['column3'].'</th><th>'.$row['column4'].'</th><th>'.$row['column5'].'</th><th><a class="add-button" href="">Add</a></th></tr></thead><tbody>'; }
					else{	echo '<tr><td>'.$row['column1'].'</td><td>'.$row['column2'].'</td><td>'.$row['column3'].'</td><td>'.$row['column4'].'</td><td>'.$row['column5'].'</td><td></td></tr>'; }
				}
				echo '</tbody></table>';
			}
		}else{
			//TODO: aa is set. go to correct action form to add asset or add tab
			echo "Add tab or add asset";

		}
	}

?>
