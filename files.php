<?php
        if(isset($_GET['siteName'])){$_GET['siteName']=filter_var($_GET['siteName'], FILTER_SANITIZE_STRING);}

//first need to get list of tabs SHOW TAB LISTS
$query = "SELECT DISTINCT tab_name FROM assets WHERE company_id='".$_GET['c']."' AND item_type='header'";
$result = QueryMysql($query);
while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
        echo '<a class="button" href="index.php?p=companies&c='.$_GET['c'].'&a='.$row['tab_name'].'">'.$row['tab_name'].'</a> ';
}
echo '<a class="button" href="index.php?p=files&c='.$_GET['c'].'&">Files</a> ';
echo '<a class="button" href="index.php?p=companies&c='.$_GET['c'].'&aa=addtab">Add</a> ';




//Files code.
echo '<table class="company-grid"><thead><tr><th>File Name</th><th>Description</th><th>Action</th><th><a class="add-button" href="index.php?p=files">Upload Files</a></th></tr></thead>';



?>
