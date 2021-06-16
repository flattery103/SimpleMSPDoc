<?php
	//Sanitize inputs
        if(isset($_GET['cat'])){$_GET['cat']=filter_var($_GET['cat'], FILTER_SANITIZE_STRING);}
        if(isset($_GET['kb'])){$_GET['kb']=filter_var($_GET['kb'], FILTER_SANITIZE_STRING);}
        if(isset($_GET['cat'])){$_GET['cat']=filter_var($_GET['cat'], FILTER_SANITIZE_STRING);}

if(isset($_GET['delkb'])){
	$query="DELETE FROM knowledge WHERE id='".$_GET['kb']."'";
	$result = QueryMysql($query);
	echo "<script>alert('KB Deleted')</script>";
}

if(!isset($_GET['a'])){ listCategories(); searchForm(); }else{
	if($_GET['a']=="showcat"){ showCategory($_GET['cat']); }
	elseif($_GET['a']=="createkb"){ createKB(); }
	elseif($_GET['a']=="addkb"){ showKB($_POST['category'],$_POST['newcategory'],$_POST['title'],$_POST['tags'],$_POST['document'],'true'); }
	elseif($_GET['a']=="showkb"){ 
		$query= "SELECT * FROM knowledge WHERE id='".$_GET['kb']."'";
		$result = QueryMysql($query);
		$row = @mysqli_fetch_array($result, MYSQLI_ASSOC);
		showKB($row['category'],'',$row['title'],$row['tags'],$row['document'],'false');
	}elseif($_GET['a']=="editkb"){ 
		editKB($_GET['kb']);
	}elseif($_GET['a']=="updatekb"){ 
		showKB($_POST['category'],$_POST['newcategory'],$_POST['title'],$_POST['tags'],$_POST['document'],'update');
	}elseif($_GET['a']=="deletekb"){ 
		deleteKB($_GET['kb']);
	}elseif($_GET['a']=="deletecat"){ 
                deleteCAT($_GET['cat']);
        }
}

function deleteKB($id) {
	$query= "DELETE FROM knowledge WHERE id='$id'";
	//echo "<br><Br>".$query;
	$result = QueryMysql($query);
	listCategories(); searchForm();
}
function deleteCAT($category) {
	$query= "DELETE FROM knowledge WHERE category='$category'";
	//echo "<br><Br>".$query;
	$result = QueryMysql($query);
	listCategories(); searchForm();
}

function editKB($id){
	$query = "SELECT * FROM knowledge WHERE id='$id'";
	$result = QueryMysql($query);
        $row = @mysqli_fetch_array($result, MYSQLI_ASSOC);

	echo '<form action="index.php?p=knowledge&a=updatekb&kb='.$id.'" method="POST">
	<div class="container">
	<div class="row">
		<div class="six columns">
			<label>Title</label>
			<input class="u-full-width" type="text" id="title" name="title" value="'.$row['title'].'">
		</div><div class="six columns">
			<label>Tags</label>
			<input class="u-full-width" type="text" id="tags" name="tags" value="'.$row['tags'].'">
		</div>
	</div>
	<div class="row">
		<div class="six columns">
			<label>Category</label>
			<select class="u-full-width" name="category">
				<option value="'.$row['category'].'">'.$row['category'].'</option>
				<option value="NewCat">New Category - Fill New Category Box</option>';
			categoryOptions();
			echo '</select>
		</div><div class="six columns">
			<label>Or Create New Category</label>
			<input class="u-full-width" type="text" id="newcategory" name="newcategory" value="'.$row['category'].'">
		</div>
	</div>
	<div class="row">
		<div class="twelve columns">
			<label>Article</label>
			<textarea class="u-full-width" id="document" name="document">'.$row['document'].'</textarea>
		</div>
	</div>
	<div class="row">
		<div class="ten columns">
			<input class="button-primary" type="submit" value="Update KB Document">
		</div>
		<div class="two columns">
<a onclick="return confirm(\'Are you sure you want to delete this KB?\')" class="del-button" href="index.php?p=knowledge&delkb=true&kb='.$row['id'].'">Delete KB</a>
		</div>
	</div></div></form>';
}



function showKB($category,$newcategory,$title,$tags,$document,$add){
	if($add=="true"){
		if($category=="NewCat") {$category=$newcategory;}
		$query = "INSERT INTO knowledge (category,title,tags,document) VALUES ('$category','$title','$tags','$document')";
		$result = QueryMysql($query);
	}elseif($add=="update"){
		if($category=="NewCat") {$category=$newcategory;}
		$query = "UPDATE knowledge SET category='$category',title='$title',tags='$tags',document='$document' WHERE id='".$_GET['kb']."'";
		$result = QueryMysql($query);
	}
	echo '<div class="container">
	<div class="row">
		<div class="twelve columns"><h2>'.$title.'</h2>
		<h5>Category: '.$category.'</h5>
		Tags: '.$tags.'
	</div>
	<div class="row">
	</div>
	<div class="row">
		<div class="twelve columns doc-box">
			'.$document.'
		</div>';
		if(securityLevel()<20){
		echo '<div class="two columns">
			<a class="button button-primary" href="index.php?p=knowledge&a=editkb&kb='.$_GET['kb'].'">Edit</a>
		</div>
		<div class="two columns">
			<a class="button button-primary" onclick="return confirm(\'Are you sure you want to delete this KB?\')" href="index.php?p=knowledge&a=deletekb&kb='.$_GET['kb'].'">Delete</a>
		</div>';
		}
	echo '</div></div>';

}

function categoryOptions(){
	$query = "SELECT DISTINCT category FROM knowledge";
	$result = QueryMysql($query);
	while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
		echo '<option value="'.$row['category'].'">'.$row['category'].'</option>';
	}
}

function createKB(){
	echo '<form action="index.php?p=knowledge&a=addkb" method="POST">
	<div class="container">
	<div class="row">
		<div class="six columns">
			<label>Title</label>
			<input class="u-full-width" type="text" placeholder="KB Title" id="title" name="title">
		</div><div class="six columns">
			<label>Tags</label>
			<input class="u-full-width" type="text" placeholder="Optional tags for searching by words not in article" id="tags" name="tags">
		</div>
	</div>
	<div class="row">
		<div class="six columns">
			<label>Category</label>
			<select class="u-full-width" name="category">
				<option value="NewCat">New Category - Fill New Category Box</option>';
			categoryOptions();
			echo '</select>
		</div><div class="six columns">
			<label>Or Create New Category</label>
			<input class="u-full-width" type="text" placeholder="New Category" id="newcategory" name="newcategory">
		</div>
	</div>
	<div class="row">
		<div class="twelve columns">
			<label>Article</label>
			<textarea class="u-full-width" placeholder="This is the main article information" id="document" name="document"></textarea>
			<input class="button-primary" type="submit" value="Add KB Document">
		</div>
	</div></div></form>';
}

function listCategories() {
        echo'<div class="container">
                <div class="row">';
		$query = "SELECT DISTINCT category FROM knowledge";
		$result = QueryMysql($query);
		while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
			echo '<div class="three columns cat-box">
                      	<a href="index.php?p=knowledge&a=showcat&cat='.$row['category'].'"><li>'.$row['category'].'</li></a>
                      	</div>';
		}

		echo '</div>
	</div><br>';
}

function searchForm(){
	echo'<div class="container">
  		<div class="row">
    			<div class="eleven columns">
			<input class="u-full-width" type="text" placeholder="Search KB Articls" id="searchkb" name="searhkb">
			</div>
			<div class="one column">
				<input class="button-primary" type="submit" value="Search KB">
			</div>
		</div>';
	if(securityLevel()<20){
		echo '<div class="row">
                        <div class="two columns"><a href="index.php?p=knowledge&a=createkb" class="button button-primary">Create KB</a></div>
		</div>';
	}
	echo '</div>';

}

function showCategory($category){
	$query = "SELECT * FROM knowledge WHERE category='$category'";
	echo '<div class="container"><h2>'.$category.'</h2><table class="u-full-width"><thead><tr><th>KB Article</th><th></th><th>';
	if(securityLevel()<20){
		echo '<a class="add-button" onclick="return confirm(\'Are you sure you want to delete the '.$category.' category and all the KBs in it?\')" href="index.php?p=knowledge&a=deletecat&cat='.$category.'">Delete Category</a>';
	}
	echo '</th></tr></thead><tbody>';
	$result = QueryMysql($query);

	while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
		echo '<tr><td><a href="index.php?p=knowledge&a=showkb&kb='.$row['id'].'">'.$row['title'].'</a></td><td>'.substr(trim(strip_tags($row['document'])),0,100).'</td><td>';
		if(securityLevel()<20){
			echo '<a href="index.php?p=knowledge&a=editkb&kb='.$row['id'].'">edit</a>';
		}
		echo '</td></tr>';
	}
	echo '</tbody></table>';
}


ckeditorJS("#document");
?>
