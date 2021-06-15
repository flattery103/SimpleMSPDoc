<?php
if(!is_numeric($_GET['c'])) {exit;}

include('config.php');

if(isset($_GET['f'])){
	//File download
	//BUG: Only seems to work on basic text files.
	if(file_exists($file_upload_dir."/".$_GET['c']."/".$file)){
		$_GET['f']=stripslashes(str_replace( '/', '', $_GET['f'] ));
		$file = $_GET['f'] ?: '.';
		$filename = basename($file);
		$finfo = finfo_open(FILEINFO_MIME_TYPE);

		header('Content-Type: ' . finfo_file($finfo, $file));
		header('Content-Length: '. filesize($file));
		header(sprintf('Content-Disposition: attachment; filename=%s',
			strpos('MSIE',$_SERVER['HTTP_REFERER']) ? rawurlencode($filename) : "\"$filename\"" ));
		ob_flush();
		readfile($file_upload_dir."/".$_GET['c']."/".$file);
	}
	exit;
}
if(isset($_GET['upload'])){
	$updir=$file_upload_dir."/".$_GET['c'].'/';
	if (!is_dir($updir)) {mkdir($updir, 0755);}

	$count=0;
	foreach ($_FILES['fileList']['name'] as $filename) {
		$tmp=$_FILES['fileList']['tmp_name'][$count];
		$count=$count + 1;
		$upfile=$updir.basename($filename);
		move_uploaded_file($tmp,$upfile);
                $temp='';
                $tmp='';
            }

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


if(isset($_GET['a'])){
	if($_GET['a']=="upload"){
		echo '<form action="index.php?p=files&c='.$_GET['c'].'&upload=true" method="POST" enctype="multipart/form-data">
		<div class="container"><div class="row"><div class="four columns">
			<label>File</label>
			<input class="u-full-width" type="file" placeholder="file.txt" id="file" name="fileList[]" multiple><input type="submit" value="Upload">
			</div><div class="eight columns">
		</div></div></div></form>';
	}
}else{
	//Files code.
	echo '<table class="company-grid"><thead><tr><th></th><th>File Name</th><th>Action<a class="add-button" href="index.php?p=files&c='.$_GET['c'].'&a=upload">Upload Files</a></th></tr></thead>';

	$dir = scandir($file_upload_dir."/".$_GET['c']."/");
	//print_r($dir);
	for($i=2;$i<sizeof($dir);$i++){
		echo '<tr><td></td><td>'.$dir[$i].'</td><td><a href="files.php?c='.$_GET['c'].'&f='.$dir[$i].'" target="_blank">Download</a> </td></tr>';
	}
}
?>
