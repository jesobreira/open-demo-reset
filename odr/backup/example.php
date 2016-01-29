<?php
session_start();

if(!isset($_SESSION['userid'])) {
	$_SESSION['userid'] = rand();
}

if(!isset($_COOKIE['userid'])) {
	setcookie('userid', rand(), strtotime("+1 week"), '/');
}

?>

<!-- odr -->
<link rel="stylesheet" href="odr/style.css" type="text/css" />
<script type="text/javascript" src="odr/odr.js.php"></script>
<!-- /odr -->

Hi! Here's a simple user ID generated 
for you and stored as session: <?=$_SESSION['userid']?>, 
and here's a simple user ID stored as 
cookie: <?=$_COOKIE['userid']?>. It shall change
every time the demo resets.
<hr/>
<?php

$db = new mysqli('localhost', 'root', '1708', 'opendemoreset');
if($_SERVER['REQUEST_METHOD']=='POST' AND $_FILES['file']['size']) {
	if(strtolower(end(explode(".", $_FILES['file']['name'])))=='jpg') {
		$filename = 'uploads/'.uniqid().'.jpg';
		move_uploaded_file($_FILES['file']['tmp_name'], $filename);
		$db->query("INSERT INTO images VALUES ('', '$filename')");
	} else {
		echo '<p>JPG files only!</p>';
	}
}
?>

<form method="post" enctype="multipart/form-data">
	<input type="file" name="file" />
	<input type="submit" />
</form>
<hr/>
<?php
$qry = $db->query("SELECT * FROM images ORDER BY id DESC LIMIT 10");
if($qry->num_rows) {
	while($row = $qry->fetch_array()) {
		echo '<img src="'.$row['image'].'" /><br/>';
	}
} else {
	echo '<p>No images sent, yet.</p>';
}
?>
