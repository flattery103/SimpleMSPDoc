<?php
	if(isset($_GET['search'])){$_GET['search']=filter_var($_GET['search'], FILTER_SANITIZE_STRING);}


	if(isset($_GET['mod'])) {
		if($_GET['mod']=="companies"){
			//We are seaching in the company Mod
			if(!isset($_GET['c'])){
				//We are searching the assets for a company
				searchCompany($_POST['search']);
			}else{
				if(isset($_GET['a'])){
					//We are searching in a tab
					//echo "we search in tab";
					searchTab($_POST['search']);
				}else{
					//We are seaching companies
					searchAsset($_POST['search']);
				}
			}
		}elseif($_GET['mod']=="knowledge"){
			searchKnowledge($_POST['search']);
		}elseif($_GET['mod']=="links") {
			//Search Links
			searchLinks($_POST['search']);
		}else{
			//No mods left - Do global search
			searchAll($_POST['search']);
		}

	}else{
		//Doing a global Search
		globalSearch();
	}


function searchAll($searchTerm){
	echo "<div class='container'>";
	searchCompany($searchTerm);
	echo "</div>";
	echo "<div class='container'>";
	searchAsset($searchTerm);
	echo "</div>";
	echo "<div class='container'>";
	searchKnowledge($searchTerm);
	echo "</div>";
	echo "<div class='container'>";
	searchLinks($searchTerm);
	echo "</div>";

}

function globalSearch(){
        echo'<div class="container">
		<br><br><br><br>
                <div class="row">
                        <div class="eleven columns">
			<form action="index.php?p=search&mod=search" method="POST">
                        <input class="u-full-width" type="text" placeholder="Global Search" id="search" name="search">
                        </div>
                        <div class="one column">
                                <input class="button-primary" type="submit" value="Search KB">
                        </div>
			</form>
                </div>
        </div>';
}

function searchCompany($searchTerm){
	$searchTerm="%".$searchTerm."%";
	$query= "SELECT * FROM companies WHERE account LIKE '$searchTerm' OR company LIKE '$searchTerm' OR contact LIKE '$searchTerm' OR email LIKE '$searchTerm' OR phone LIKE '$searchTerm' OR location LIKE '$searchTerm' OR notes LIKE '$searchTerm'";

	//Show Company list Grid
	echo '<table class="company-grid"><thead><tr><th>Account</th><th>Company</th><th>Contact</th><th>Email</th><th>Location</th><th></th></tr></thead><tbody>';
	$result = QueryMysql($query);
	while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
		echo "<tr><td>".$row['account']."</td><td><a href='index.php?p=companies&c=".$row['id']."'>  ".$row['company']."</td><td>".$row['contact']."</td><td>".$row['email']."</td><td>".$row['location']."</td><td><a href='index.php?p=companies&aa=editcompany&ec=".$row['id']."'>Edit</a></td></tr>";
	}
	echo '</tbody></table>';
	//End Show Company list Grid
}


function searchTab($searchTerm){
	$searchTerm="%".$searchTerm."%";
	$query= "SELECT * FROM assets WHERE tab_name='".$_GET['a']."' AND company_id='".$_GET['c']."' AND (notes LIKE '$searchTerm' OR column1 LIKE '$searchTerm' OR column2 LIKE '$searchTerm' OR column3 LIKE '$searchTerm' OR column4 LIKE '$searchTerm' OR column5 LIKE '$searchTerm')";
	$result = QueryMysql($query);
	echo '<table class="company-grid">';
	echo '<thead><tr><th></th><th></th><th></th><th></th><th></th><th></th></thead><tbody>';
	while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
		if($row['item_type']!="header"){
			echo '<tr><td>'.$row['column1'].'</td><td>'.$row['column2'].'</td><td>'.$row['column3'].'</td><td>'.$row['column4'].'</td><td>'.$row['column5'].'</td><td><a href="index.php?p=companies&c='.$_GET['c'].'&a='.$_GET['a'].'&ec='.$row['id'].'&aa=editasset">Edit</a></td></tr>'; 
		}
	}
	echo '</tbody></table>';
}

function searchAsset($searchTerm){
	$searchTerm="%".$searchTerm."%";
	$query= "SELECT * FROM assets WHERE company_id='".$_GET['c']."' AND (notes LIKE '$searchTerm' OR column1 LIKE '$searchTerm' OR column2 LIKE '$searchTerm' OR column3 LIKE '$searchTerm' OR column4 LIKE '$searchTerm' OR column5 LIKE '$searchTerm')";
	$result = QueryMysql($query);
	echo '<table class="company-grid">';
	echo '<thead><tr><th></th><th></th><th></th><th></th><th></th><th></th></thead><tbody>';
	while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
		if($row['item_type']!="header"){
			echo '<tr><td>'.$row['column1'].'</td><td>'.$row['column2'].'</td><td>'.$row['column3'].'</td><td>'.$row['column4'].'</td><td>'.$row['column5'].'</td><td><a href="index.php?p=companies&c='.$_GET['c'].'&a='.$_GET['a'].'&ec='.$row['id'].'&aa=editasset">Edit</a></td></tr>'; 
		}
	}
	echo '</tbody></table>';
}


function searchLinks($searchTerm){
	$searchTerm="%".$searchTerm."%";
	$query = "SELECT * FROM links WHERE name LIKE '$searchTerm' OR link LIKE '$searchTerm'";
	echo '<table class="company-grid"><thead><tr><th>Site Name</th><th>Link URL</th><th>&#9733; Featured</th><th></th></tr></thead><tbody>';
	$result = QueryMysql($query);
	while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
	        if(isset($row['featured'])){if($row['featured']==1){$featured="&#9733;&#9733;&#9733;";}else{$featured="";}}
	        echo '<tr><td>'.$row['name'].'</td><td><a href="'.$row['link'].'" target="_blank">'.$row['link'].'</a></td><td>'.$featured.'</td><td>Edit</td></tr>';
	}
	echo '</tbody></table>';

}

function searchKnowledge($searchTerm){
	$searchTerm="%".$searchTerm."%";
	$query = "SELECT * FROM knowledge WHERE category LIKE '$searchTerm' OR title LIKE '$searchTerm' OR tags LIKE '$searchTerm' OR document LIKE '$searchTerm'";
        echo '<div class="container"><table class="u-full-width"><thead><tr><th>KB Article</th><th></th><th></th></thead><tbody>';
        $result = QueryMysql($query);

        while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
                echo '<tr><td><a href="index.php?p=knowledge&a=showkb&kb='.$row['id'].'">'.$row['title'].'</a></td><td>'.substr(trim(strip_tags($row['document'])),0,100).'</td><td><a href="index.php?p=knowledge&a=editkb&kb='.$row['id'].'">edit</a></td></tr>';
        }
        echo '</tbody></table>';

}

//TODO:

//Search results for each page p=companies knowledge or links. Otherwise is does a global search.
//Global Search will show four boxes with results from the above three and assets.

//Seach page will default to like %search term%


//SELECT * FROM assets WHERE tab_name LIKE '%3111%' OR notes LIKE '%3111%' OR column1 LIKE '%3111%' OR column2 LIKE '%3111%' OR column3 LIKE '%3111%' OR column4 LIKE '%3111%' OR column5 LIKE '%3111%'

//SELECT * FROM companies WHERE account LIKE '%3111%' OR company LIKE '%3111%' OR contact LIKE '%3111%' OR email LIKE '%3111%' OR phone LIKE '%3111%' OR location LIKE '%3111%' OR notes LIKE '%3111%'

//SELECT * FROM knowledge WHERE category LIKE '%3111%' OR title LIKE '%3111%' OR tags LIKE '%3111%' OR document LIKE '%3111%' 

//SELECT * FROM links WHERE name LIKE '%3111%' OR link LIKE '%3111%'
?>

