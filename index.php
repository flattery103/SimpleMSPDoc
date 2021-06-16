<?php
  include('functions.php');
  //Sanitize inputs
  if(isset($_GET['c'])){$_GET['c']=filter_var($_GET['c'], FILTER_SANITIZE_STRING);}
  if(isset($_GET['a'])){$_GET['a']=filter_var($_GET['a'], FILTER_SANITIZE_STRING);}
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta charset="utf-8">
  <title>Simple MSP Doc</title>
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
<!--<script src="https://cdn.ckeditor.com/ckeditor5/27.1.0/classic/ckeditor.js"></script>-->
<script src="js/ckeditor.js"></script>
</head>
<body>

    <img class="logo" src='MSPDoc.png'>
    <nav class="navbar">
        <ul class="navbar-list" style="list-style: none;" id="navbar-list">
          <li class="navbar-item"><a class="navbar-link" href="index.php">Dashboard</a></li>
          <li class="navbar-item"><a class="navbar-link" href="index.php?p=companies">Companies</a></li>
          <li class="navbar-item"><a class="navbar-link" href="index.php?p=knowledge">Knowledge</a></li>
          <li class="navbar-item"><a class="navbar-link" href="index.php?p=links">Links</a></li>
          <li class="navbar-item search-bar" style="top:15px;">
	  <?php
		$action="index.php?p=search";
		if(isset($_GET['p'])){if(isset($_GET['mod'])){$action=$action."&mod=".$_GET['mod'];}else{$action=$action."&mod=".$_GET['p'];}}
		if(isset($_GET['c'])){$action=$action."&c=".$_GET['c'];}
		if(isset($_GET['a'])){$action=$action."&a=".$_GET['a'];}
		echo '<form action="'.$action.'" method="POST">';
	  ?>
	  <input type="text" minlength="3" placeholder="Search" name="search">
	  <input class="button-primary" type="submit" value="Go"></li>
	  </form>
	</ul>
	<a id="navicon" class="navicon" href="javascript:void(0);" onclick="myFunction()">&#9776;</a>

   </nav>



  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
<?php

if(isset($_GET['p'])){
	if($_GET['p']=="companies") {
		include('companies.php');
	}elseif($_GET['p']=="knowledge") {
	        include('knowledge.php');
	}elseif($_GET['p']=="links") {
	        include('links.php');
	}elseif($_GET['p']=="search") {
                include('search.php');
	}elseif($_GET['p']=="admin") {
                include('admin.php');
	}elseif($_GET['p']=="files") {
                include('files.php');
        }
}else{
	include('dashboard.php');

}

?>

<!-- End Document
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->

<script>
function myFunction() {
  var x = document.getElementById("navbar-list");
  if (x.style.display === "block") {
    x.style.display = "none";
  } else {
    x.style.display = "block";
  }
}
</script>
</body>
</html>
