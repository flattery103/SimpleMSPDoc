<?php
  include('functions.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <!-- Basic Page Needs
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
  <meta charset="utf-8">
  <title>Your page title here :)</title>
  <meta name="description" content="">
  <meta name="author" content="">

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

</head>
<body>

    <img class="logo" src='MSPDoc.png'>
    <nav class="navbar">
        <ul class="navbar-list" id="navbar-list">
          <li class="navbar-item"><a class="navbar-link" href="#intro">Dashboard</a></li>
          <li class="navbar-item"><a class="navbar-link" href="#intro">Companies</a></li>
          <li class="navbar-item"><a class="navbar-link" href="#intro">Knowledge</a></li>
          <li class="navbar-item" style="top:15px;"><input type="text" placeholder="Search" name="search"><input class="button-primary" type="submit" value="Go"></li>
	</ul>
<a id="navicon" class="navicon" href="javascript:void(0);" onclick="myFunction()">&#9776;</a>

   </nav>



  <!-- Primary Page Layout
  –––––––––––––––––––––––––––––––––––––––––––––––––– -->
<?php include('companies.php'); ?>

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
