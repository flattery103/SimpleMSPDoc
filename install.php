<html>
<head>
<title>Simple MSPDoc Installation</title>
</head>
<body>

<?php

	if(isset($_GET['step'])){
		if($_GET['step']=="2") {
			//Provide a Create user form
		}elseif($_GET['step']=="2") {
			//Install DB and check for succsess. If successful. Suggest delete install.php
		}

	}else{
		echo "Step 1 - Edit the Config.php file with your mySql info";
		//Check if file is updated
	}

?>




</body>
</html>
