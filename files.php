<?php

include('config.php');

if(isset($_GET['f'])){
	$file = $_GET['f'] ?: '.';
	$filename = basename($file);
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	header('Content-Type: ' . finfo_file($finfo, $file));
	header('Content-Length: '. filesize($file));
	header(sprintf('Content-Disposition: attachment; filename=%s',
		strpos('MSIE',$_SERVER['HTTP_REFERER']) ? rawurlencode($filename) : "\"$filename\"" ));
	ob_flush();
	readfile($file_upload_dir."/".$_GET['c']."/".$file);
	exit;
}

        //if(isset($_GET['siteName'])){$_GET['siteName']=filter_var($_GET['siteName'], FILTER_SANITIZE_STRING);}

//echo $file_upload_dir;


//first need to get list of tabs SHOW TAB LISTS
$query = "SELECT DISTINCT tab_name FROM assets WHERE company_id='".$_GET['c']."' AND item_type='header'";
$result = QueryMysql($query);
while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
        echo '<a class="button" href="index.php?p=companies&c='.$_GET['c'].'&a='.$row['tab_name'].'">'.$row['tab_name'].'</a> ';
}
echo '<a class="button" href="index.php?p=files&c='.$_GET['c'].'">Files</a> ';
echo '<a class="button" href="index.php?p=companies&c='.$_GET['c'].'&aa=addtab">Add</a> ';



//Asset file DB 
//company_id = the companiy id number
//item_type = file
//tab_name = Files
//notes = The file description
//column1 = The file name
//column2 = The file location - not needed. Just use $file_upload_dir/$row['company_id']/column1
//column3 = unused
//column4 = unused
//column5 = unused

if($_GET['a']=="upload"){
	echo '<form action="'.$_SERVER['REQUEST_URI'].'&add=true" method="POST">
	<div class="container"><div class="row"><div class="four columns">
		<label>File</label>
		<input class="u-full-width" type="file" placeholder="file.txt" id="file" name="file">
		</div><div class="eight columns">
		<label for="exampleEmailInput">Description</label>
		<input class="u-full-width" type="text" placeholder="File description here" id="description" name="descriptioin">
	</div></div></div>';

}else{
	//Files code.
	echo '<table class="company-grid"><thead><tr><th>File Name</th><th>Description</th><th>Action</th><th><a class="add-button" href="index.php?p=files&c='.$_GET['c'].'&a=upload">Upload Files</a></th></tr></thead>';
	$query = "SELECT * FROM assets WHERE company_id='".$_GET['c']."' AND item_type='file'";
	$result = QueryMysql($query);
	while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
	        echo '<tr><td>'.$row['column1'].'</td><td>'.$row['notes'].'</td><td><a href="files.php?c='.$row['company_id'].'&f='.$row['column1'].'" target="_blank">Download</a> </td></tr>';
	}
}


?>
