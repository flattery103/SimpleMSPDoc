<?php
        if(isset($_GET['siteName'])){$_GET['siteName']=filter_var($_GET['siteName'], FILTER_SANITIZE_STRING);}
        if(isset($_GET['url'])){$_GET['url']=filter_var($_GET['url'], FILTER_SANITIZE_STRING);}
        if(isset($_GET['featur'])){$_GET['feature']=filter_var($_GET['feature'], FILTER_SANITIZE_STRING);}


	if(isset($_GET['a'])) {
		if (isset($_GET['add'])){
			if(!isset($_POST['feature'])){$_POST['feature']=0;}
			$query="INSERT INTO links (name,link,featured) VALUES ('".$_POST['siteName']."','".$_POST['url']."','".$_POST['feature']."')";
	                $result = QueryMysql($query);
		}
	}
?>









<form action="index.php?p=links&a=addlink&add=true" method="POST">
  <div class="row">
    <div class="three columns">
      <label>Link Name</label>
      <input class="u-full-width" type="text" placeholder="My Site" id="siteName" name="siteName">
    </div>
    <div class="nine columns">
      <label>Link URL</label>
      <input class="u-full-width" type="text" placeholder="https://google.com" id="url" name="url">
    </div>
  </div>

 <div class="row">
    <div class="one column">
	<input class="button-primary" type="submit" value="Add Link">
    </div>
    <div class="two columns">
	<input type="checkbox" name="feature" value="1"><span class="label-body">Feature on Dashboard</span>
    </div>
</form>
</div>




<table class="company-grid"><thead><tr><th>Site Name</th><th>Link URL</th><th></th></tr></thead><tbody>
<?php
$query = "SELECT * FROM links";
$result = QueryMysql($query);
while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
	if(isset($row['featured'])){if($row['featured']==1){$featured="&#9733;&#9733;&#9733;";}else{$featured="";}}
	echo '<tr><td>'.$row['name'].'</td><td><a href="'.$row['link'].'" target="_blank">'.$row['link'].'</a></td><td>'.$featured.'</td></tr>';
}
?>
</tbody></table>
