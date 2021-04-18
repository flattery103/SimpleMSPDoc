<br><br>
<div class="container">
  <div class="row">
    <div class="three columns dash-box">Companies<br><?php echo myDashCounts('companies','',''); ?></div>
    <div class="three columns dash-box">Assets<br><?php echo myDashCounts('assets','item_type','asset'); ?></div>
    <div class="three columns dash-box">KB Articles<br><?php echo myDashCounts('knowledge','',''); ?></div>
    <div class="three columns dash-box">Links<br><?php echo myDashCounts('links','',''); ?></div>
  </div>

  <div class="row">
    <div class="twelve columns">&nbsp;</div>
  </div>
  <div class="row">
    <div class="six columns dash-box">Administration<br>Users<br>Settings</br></div>
    <div class="six columns dash-box">Featured Links<br><?php showLinks(); ?></div>
  </div>



</div>




<?php
	function myDashCounts($table,$filterVar, $filterVal) {
		$query = "SELECT * FROM ".$table;
		if($filterVar!=""){$query=$query." WHERE ".$filterVar."='".$filterVal."'";}
		$result = QueryMysql($query);
		$row = @mysqli_num_rows($result);
		return $row;
	}

	function showLinks() {
		$query = "SELECT * FROM links WHERE featured='1'";
		$result = QueryMysql($query);
                while($row = @mysqli_fetch_array($result, MYSQLI_ASSOC)){
			echo "&#9733; <a href='".$row['link']."' target='_blank'> ".$row['name']."</a><br>";
		}
	}
?>

