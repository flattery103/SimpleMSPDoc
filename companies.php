<?php

	//Sanitize inputs
	if(isset($_GET['ec'])){$_GET['ec']=filter_var($_GET['ec'], FILTER_SANITIZE_STRING);}

	if(isset($_GET['delasset'])){
		$query = "DELETE FROM assets WHERE id='".$_GET['ec']."'";
		$result = QueryMysql($query);
	}
	if(isset($_GET['deltab'])){
		$query = "DELETE FROM assets WHERE tab_name='".$_GET['a']."'";
		$result = QueryMysql($query);
		unset($_GET['a']);
	}
	if(isset($_GET['delcompany'])){
		$query = "DELETE FROM companies WHERE id='".$_GET['ec']."'";
		$result = QueryMysql($query);
		$query = "DELETE FROM assets WHERE company_id='".$_GET['ec']."'";
		$result = QueryMysql($query);
	}


	//If no company is selected, show the list of companies
	if(!isset($_GET['c'])){
		if(!isset($_GET['aa'])) {
			//Show Company list Grid
			echo '<table class="company-grid"><thead><tr><th>Account</th><th>Company</th><th>Contact</th><th>Email</th><th>Location</th><th><a class="add-button" href="index.php?p=companies&aa=addcompany">Add</a></th></tr></thead><tbody>';
			$query = "SELECT * FROM companies";
			$result = QueryMysql($query);
		        while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
				echo "<tr><td>".$row['account']."</td><td><a href='index.php?p=companies&c=".$row['id']."'>  ".$row['company']."</td><td>".$row['contact']."</td><td>".$row['email']."</td><td>".$row['location']."</td><td><a href='index.php?p=companies&aa=editcompany&ec=".$row['id']."'>Edit</a></td></tr>";
			}
			echo '</tbody></table>';
			//End Show Company list Grid
		}else{
			//Add Company Form
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
				<form action="index.php?p=companies&aa=addcompany&add=true" method="POST">
				<div class="container"><div class="row"><div class="six columns">
					<label>Account</label>
					<input class="u-full-width" type="text" placeholder="123456" id="account" name="account">
					</div><div class="six columns">
					<label for="exampleEmailInput">Company</label>
					<input class="u-full-width" type="text" placeholder="Acme Tools" id="company" name="company">
				</div>
				<div class="row">
					<div class="six columns">
					<label>Contact</label>
					<input class="u-full-width" type="text" placeholder="John Doe" id="contact" name="contact">
					</div><div class="six columns">
					<label>Email</label>
					<input class="u-full-width" type="email" placeholder="test@domain.com" id="email" name="email">
				</div>
				<div class="row">
					<div class="six columns">
					<label>Phone</label>
					<input class="u-full-width" type="text" placeholder="555-555-5555" id="phone" name="phone">
					</div><div class="six columns">
					<label>Location</label>
					<input class="u-full-width" type="text" placeholder="New Ulm" id="location" name="location">
				</div>
				<div class="row">
					<div class="twelve columns">
					<label>Notes</label>
					<textarea class="u-full-width" placeholder="Type in any extra notes that doesn\'t fit anywhere else" id="notes" name="notes"></textarea>
					<input class="button-primary" type="submit" value="Add Company">
					</div>
				</div>

				</div></form>';
			}
			//End Add Company Form


			//Edit company form
			if($_GET['aa']=="editcompany"){
				if(isset($_GET['edit'])){
					if(isset($_POST['account'])){$_POST['account']=filter_var($_POST['account'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['company'])){$_POST['company']=filter_var($_POST['company'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['contact'])){$_POST['contact']=filter_var($_POST['contact'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['email'])){$_POST['email']=filter_var($_POST['email'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['phone'])){$_POST['phone']=filter_var($_POST['phone'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['location'])){$_POST['location']=filter_var($_POST['location'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['notes'])){$_POST['notes']=filter_var($_POST['notes'], FILTER_SANITIZE_STRING);}

					//UPDATE Company
					$query = " UPDATE companies SET account='".$_POST['account']."', company='".$_POST['company']."', contact='".$_POST['contact']."', email='".$_POST['email']."', phone='".$_POST['phone']."', location='".$_POST['location']."', notes='".$_POST['notes']."' WHERE id='".$_GET['ec']."'";
					$result = QueryMysql($query);
					echo "<script>alert('".$_POST['company']." Updated')</script>";
				}

				//Pull company info to populate inputs
				$query = "SELECT * FROM companies WHERE id='".$_GET['ec']."'";
				$result = QueryMysql($query);
				$row = @mysqli_fetch_array($result, MYSQLI_ASSOC);
				//Edit Company Form
				echo '
				<form action="index.php?p=companies&aa=editcompany&ec='.$_GET['c'].'&edit=true" method="POST">
				<div class="container"><div class="row"><div class="six columns">
					<label>Account</label>
					<input class="u-full-width" type="text" id="account" name="account" value="'.$row['account'].'">
					</div><div class="six columns">
					<label>Company</label>
					<input class="u-full-width" type="text" id="company" name="company" value="'.$row['company'].'">
				</div>
				<div class="row">
					<div class="six columns">
					<label>Contact</label>
					<input class="u-full-width" type="text" id="contact" name="contact" value="'.$row['contact'].'">
					</div><div class="six columns">
					<label>Email</label>
					<input class="u-full-width" type="email" id="email" name="email" value="'.$row['email'].'">
				</div>
				<div class="row">
					<div class="six columns">
					<label>Phone</label>
					<input class="u-full-width" type="text" id="phone" name="phone" value="'.$row['phone'].'">
					</div><div class="six columns">
					<label>Location</label>
					<input class="u-full-width" type="text" id="location" name="location" value="'.$row['location'].'">
				</div>
				<div class="row">
					<div class="twelve columns">
					<label>Notes</label>
					<textarea class="u-full-width" id="notes" name="notes">'.$row['notes'].'</textarea>
					<input class="button-primary" type="submit" value="Edit Company">
<a onclick="return confirm(\'Are you sure you want to delete this company and all assets associated with it?\')" class="del-button" href="index.php?p=companies&ec='.$_GET['ec'].'&delcompany=true">Delete Company</a>
					</div>
				</div>

				</div></form>';
			}
			//END Edit Company Form






		}
	}
	//End showing Company list



////////////////////////////////////////////////////////


	//If a company
	else {
		if(!isset($_GET['aa'])){
			//first need to get list of tabs SHOW TAB LISTS
			$query = "SELECT DISTINCT tab_name FROM assets WHERE company_id='".$_GET['c']."' AND item_type='header'";
			$result = QueryMysql($query);
	                while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
				echo '<a class="button" href="index.php?p='.$_GET['p'].'&c='.$_GET['c'].'&a='.$row['tab_name'].'">'.$row['tab_name'].'</a> ';
			}
			echo '<a class="button" href="index.php?p=files&c='.$_GET['c'].'&">Files</a> ';
			echo '<a class="button" href="index.php?p=companies&c='.$_GET['c'].'&aa=addtab">Add</a> ';

			//If tab is selected, SHOW ASSET GRID.
			if(isset($_GET['a'])) {
				$query = "SELECT * FROM assets WHERE company_id='".$_GET['c']."' AND tab_name='".$_GET['a']."'";
				$result = QueryMysql($query);
				echo '<table class="company-grid">';
		                while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
					if($row['item_type']=="header") { echo '<thead><tr><th>'.$row['column1'].'</th><th>'.$row['column2'].'</th><th>'.$row['column3'].'</th><th>'.$row['column4'].'</th><th>'.$row['column5'].'</th><th><a class="add-button" href="'.$_SERVER['REQUEST_URI'].'&aa=addasset">Add</a> <a class="add-button" href="'.$_SERVER['REQUEST_URI'].'&aa=edittab&ec='.$row['id'].'">Edit Tab</a></th></tr></thead><tbody>'; }
					else if($row['item_type']=="asset"){	echo '<tr><td>'.$row['column1'].'</td><td>'.$row['column2'].'</td><td>'.$row['column3'].'</td><td>'.$row['column4'].'</td><td>'.$row['column5'].'</td><td><a href="index.php?p=companies&c='.$_GET['c'].'&a='.$_GET['a'].'&ec='.$row['id'].'&aa=editasset">Edit</a></td></tr>'; }
				}
				echo '</tbody></table>';
			}
			//END ASSET GRID
		}else{
		//This section addtab or add asset
			if($_GET['aa']=="addtab") {
				if(isset($_GET['add'])){
					if(isset($_POST['tab_name'])){$_POST['tab_name']=filter_var($_POST['tab_name'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column1'])){$_POST['column1']=filter_var($_POST['column1'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column2'])){$_POST['column2']=filter_var($_POST['column2'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column3'])){$_POST['column3']=filter_var($_POST['column3'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column4'])){$_POST['column4']=filter_var($_POST['column4'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column5'])){$_POST['column5']=filter_var($_POST['column5'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['notes'])){$_POST['notes']=filter_var($_POST['notes'], FILTER_SANITIZE_STRING);}
					$query = "INSERT INTO assets (company_id,item_type,tab_name,notes,column1,column2,column3,column4,column5) VALUES ('".$_GET['c']."','header','".$_POST['tab_name']."','".$_POST['notes']."','".$_POST['column1']."','".$_POST['column2']."','".$_POST['column3']."','".$_POST['column4']."','".$_POST['column5']."')";
					$result = QueryMysql($query);
					//echo $query;
					echo "<script>alert('".$_POST['tab_name']." Added')</script>";
				}

				//Add Tab Form
				echo '
                                <form action="index.php?p=companies&c='.$_GET['c'].'&aa=addtab&add=true" method="POST">
                                <div class="container">
				<div class="row">
                                        <div class="twelve columns">
					<label>Tab Name</label>
                                        <input class="u-full-width" type="text" placeholder="Hardware" id="tab_name" name="tab_name">
                                        </div>
				</div>
				<div class="row"><div class="six columns">
                                        <label>Column 1 Header</label>
                                        <input class="u-full-width" type="text" placeholder="123456" id="column1" name="column1">
                                        </div><div class="six columns">
                                        <label>Column 2 Header</label>
                                        <input class="u-full-width" type="text" placeholder="Acme Tools" id="column2" name="column2">
                                </div>
                                <div class="row">
                                        <div class="six columns">
                                        <label>Column 3 Header</label>
                                        <input class="u-full-width" type="text" placeholder="column3" id="column3" name="column3">
                                        </div><div class="six columns">
                                        <label>Column 4 Header</label>
                                        <input class="u-full-width" type="text" placeholder="" id="column4" name="column4">
                                </div>
                                <div class="row">
                                        <div class="six columns">
                                        <label>Column 5 Header</label>
                                        <input class="u-full-width" type="text" placeholder="555-555-5555" id="column5" name="column5">
                                        </div><div class="six columns">
                                        <label>Note</label>
                                        <textarea class="u-full-width" placeholder="Notes on this tab type" id="notes" name="notes"></textarea>
                                </div>
                                <div class="row">
                                        <div class="twelve columns">
                                        <input class="button-primary" type="submit" value="Add Tab">
                                        </div>
                                </div>

                                </div></form>';
			}elseif($_GET['aa']=="addasset"){
				//Add Asset Form
				if(isset($_GET['add'])){
					if(isset($_POST['tab_name'])){$_POST['tab_name']=filter_var($_POST['tab_name'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column1'])){$_POST['column1']=filter_var($_POST['column1'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column2'])){$_POST['column2']=filter_var($_POST['column2'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column3'])){$_POST['column3']=filter_var($_POST['column3'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column4'])){$_POST['column4']=filter_var($_POST['column4'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column5'])){$_POST['column5']=filter_var($_POST['column5'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['notes'])){$_POST['notes']=filter_var($_POST['notes'], FILTER_SANITIZE_STRING);}
					$query = "INSERT INTO assets (company_id,item_type,tab_name,notes,column1,column2,column3,column4,column5) VALUES ('".$_GET['c']."','asset','".$_POST['tab_name']."','".$_POST['notes']."','".$_POST['column1']."','".$_POST['column2']."','".$_POST['column3']."','".$_POST['column4']."','".$_POST['column5']."')";
					$result = QueryMysql($query);
					//echo $query;
					echo "<script>alert('Asset has been added')</script>";
				}

				//Get Header Labels
				$query = "SELECT * FROM assets WHERE company_id='".$_GET['c']."' AND tab_name='".$_GET['a']."' AND item_type='header'";
				$result = QueryMysql($query);
                       		$row = @mysqli_fetch_array($result, MYSQLI_ASSOC);

				//Add Asset Form
				echo '
                                <form action="'.$_SERVER['REQUEST_URI'].'&add=true" method="POST">
                                <div class="container">
				<div class="row">
                                        <div class="twelve columns">
					<h3>'.$row['tab_name'].'</h3>

                                        </div>
				</div>
				<div class="row"><div class="six columns">
                                        <label>'.$row['column1'].'</label>
                                        <input class="u-full-width" type="text" placeholder="123456" id="column1" name="column1">
                                        </div><div class="six columns">
                                        <label>'.$row['column2'].'</label>
                                        <input class="u-full-width" type="text" placeholder="Acme Tools" id="column2" name="column2">
                                </div>
                                <div class="row">
                                        <div class="six columns">
                                        <label>'.$row['column3'].'</label>
                                        <input class="u-full-width" type="text" placeholder="column3" id="column3" name="column3">
                                        </div><div class="six columns">
                                        <label>'.$row['column4'].'</label>
                                        <input class="u-full-width" type="text" placeholder="" id="column4" name="column4">
                                </div>
                                <div class="row">
                                        <div class="six columns">
                                        <label>'.$row['column5'].'</label>
                                        <input class="u-full-width" type="text" placeholder="555-555-5555" id="column5" name="column5">
                                        </div><div class="six columns">
                                        <label>Note</label>
                                        <textarea class="u-full-width" placeholder="Notes on this tab type" id="notes" name="notes"></textarea>
                                </div>
                                <div class="row">
                                        <div class="twelve columns">
					<input type="hidden" name="tab_name" value="'.$row['tab_name'].'">
                                        <input class="button-primary" type="submit" value="Add Asset">
                                        </div>
                                </div>

                                </div></form>';

			}elseif($_GET['aa']=="edittab"){
				//Edit tab start
				if(isset($_GET['edit'])){
					if(isset($_POST['tab_name'])){$_POST['tab_name']=filter_var($_POST['tab_name'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column1'])){$_POST['column1']=filter_var($_POST['column1'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column2'])){$_POST['column2']=filter_var($_POST['column2'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column3'])){$_POST['column3']=filter_var($_POST['column3'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column4'])){$_POST['column4']=filter_var($_POST['column4'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column5'])){$_POST['column5']=filter_var($_POST['column5'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['notes'])){$_POST['notes']=filter_var($_POST['notes'], FILTER_SANITIZE_STRING);}
					$query = "UPDATE assets SET tab_name='".$_POST['tab_name']."',notes='".$_POST['notes']."',column1='".$_POST['column1']."',column2='".$_POST['column2']."',column3='".$_POST['column3']."',column4='".$_POST['column4']."',column5='".$_POST['column5']."' WHERE id='".$_GET['ec']."'";
					$result = QueryMysql($query);

					//if GET[a] is different than POST[tab_name] then we need to change all tab_names where company=c and tab_name=a
					if($_GET['a']!=$_POST['tab_name']){
						$query = "UPDATE assets SET tab_name='".$_POST['tab_name']."' WHERE company_id='".$_GET['c']."' AND tab_name='".$_GET['a']."'";
						$result = QueryMysql($query);
					}

					echo "<script>alert('".$_POST['tab_name']." Updated')</script>";
				}

				$query = "SELECT * FROM assets WHERE id='".$_GET['ec']."'";
                                $result = QueryMysql($query);
                                $row = @mysqli_fetch_array($result, MYSQLI_ASSOC);

				//Edit Tab Form
				echo '
                                <form action="index.php?p=companies&c='.$_GET['c'].'&a='.$row['tab_name'].'&aa=edittab&ec='.$_GET['ec'].'&edit=true" method="POST">
                                <div class="container">
				<div class="row">
                                        <div class="twelve columns">
					<label>Tab Name</label>
                                        <input class="u-full-width" type="text" id="tab_name" name="tab_name" value="'.$row['tab_name'].'">
                                        </div>
				</div>
				<div class="row"><div class="six columns">
                                        <label>Column 1 Header</label>
                                        <input class="u-full-width" type="text" id="column1" name="column1" value="'.$row['column1'].'">
                                        </div><div class="six columns">
                                        <label>Column 2 Header</label>
                                        <input class="u-full-width" type="text" id="column2" name="column2" value="'.$row['column2'].'">
                                </div>
                                <div class="row">
                                        <div class="six columns">
                                        <label>Column 3 Header</label>
                                        <input class="u-full-width" type="text" id="column3" name="column3" value="'.$row['column3'].'">
                                        </div><div class="six columns">
                                        <label>Column 4 Header</label>
                                        <input class="u-full-width" type="text" id="column4" name="column4" value="'.$row['column4'].'">
                                </div>
                                <div class="row">
                                        <div class="six columns">
                                        <label>Column 5 Header</label>
                                        <input class="u-full-width" type="text" id="column5" name="column5" value="'.$row['column5'].'">
                                        </div><div class="six columns">
                                        <label>Note</label>
                                        <textarea class="u-full-width" id="notes" name="notes">'.$row['notes'].'</textarea>
                                </div>
                                <div class="row">
                                        <div class="twelve columns">
                                        <input class="button-primary" type="submit" value="Edit Tab">
<a onclick="return confirm(\'Are you sure you want to delete this tab and all the assets in it??\')" class="del-button" href="index.php?p=companies&c='.$_GET['c'].'&a='.$_GET['a'].'&ec='.$_GET['ec'].'&deltab=true">Delete Tab</a>
                                        </div>
                                </div>

                                </div></form>';
				//Edit tab End



			}elseif($_GET['aa']=="editasset"){
				//Edit asset start
				if(isset($_GET['edit'])){
					if(isset($_POST['tab_name'])){$_POST['tab_name']=filter_var($_POST['tab_name'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column1'])){$_POST['column1']=filter_var($_POST['column1'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column2'])){$_POST['column2']=filter_var($_POST['column2'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column3'])){$_POST['column3']=filter_var($_POST['column3'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column4'])){$_POST['column4']=filter_var($_POST['column4'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['column5'])){$_POST['column5']=filter_var($_POST['column5'], FILTER_SANITIZE_STRING);}
					if(isset($_POST['notes'])){$_POST['notes']=filter_var($_POST['notes'], FILTER_SANITIZE_STRING);}
					$query = "UPDATE assets SET notes='".$_POST['notes']."',column1='".$_POST['column1']."',column2='".$_POST['column2']."',column3='".$_POST['column3']."',column4='".$_POST['column4']."',column5='".$_POST['column5']."' WHERE id='".$_GET['ec']."'";
					$result = QueryMysql($query);
					//echo $query;
					echo "<script>alert('Asset has been Updated')</script>";
				}

				//Get Header Labels
				$query = "SELECT * FROM assets WHERE company_id='".$_GET['c']."' AND tab_name='".$_GET['a']."' AND item_type='header'";
				$result = QueryMysql($query);
                       		$row = @mysqli_fetch_array($result, MYSQLI_ASSOC);

				//Get input values
				$query = "SELECT * FROM assets WHERE id='".$_GET['ec']."'";
                                $result = QueryMysql($query);
                                $values = @mysqli_fetch_array($result, MYSQLI_ASSOC);

				//Edit Asset Form
				echo '
                                <form action="index.php?p=companies&c='.$_GET['c'].'&a='.$_GET['a'].'&ec='.$_GET['ec'].'&aa=editasset&edit=true" method="POST">
                                <div class="container">
				<div class="row">
                                        <div class="twelve columns">
					<h3>'.$row['tab_name'].'</h3>

                                        </div>
				</div>
				<div class="row"><div class="six columns">
                                        <label>'.$row['column1'].'</label>
                                        <input class="u-full-width" type="text" id="column1" name="column1" value="'.$values['column1'].'">
                                        </div><div class="six columns">
                                        <label>'.$row['column2'].'</label>
                                        <input class="u-full-width" type="text" id="column2" name="column2" value="'.$values['column2'].'">
                                </div>
                                <div class="row">
                                        <div class="six columns">
                                        <label>'.$row['column3'].'</label>
                                        <input class="u-full-width" type="text" id="column3" name="column3" value="'.$values['column3'].'">
                                        </div><div class="six columns">
                                        <label>'.$row['column4'].'</label>
                                        <input class="u-full-width" type="text" id="column4" name="column4" value="'.$values['column4'].'">
                                </div>
                                <div class="row">
                                        <div class="six columns">
                                        <label>'.$row['column5'].'</label>
                                        <input class="u-full-width" type="text" id="column5" name="column5" value="'.$values['column5'].'">
                                        </div><div class="six columns">
                                        <label>Note</label>
                                        <textarea class="u-full-width" id="notes" name="notes">'.$values['notes'].'</textarea>
                                </div>
                                <div class="row">
                                        <div class="twelve columns">
                                        <input class="button-primary" type="submit" value="Edit Asset"> 
					<a onclick="return confirm(\'Are you sure you want to delete this asset?\')" class="del-button" href="index.php?p=companies&c='.$_GET['c'].'&a='.$_GET['a'].'&ec='.$_GET['ec'].'&delasset=true">Delete Asset</a>
                                        </div>
                                </div>

                                </div></form>';


				//Edit asset END
			}

		}
	}

ckeditorJS("#notes");
?>
