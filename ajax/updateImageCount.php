<?php
include("../config.php");

if(isset($_POST["imageUrl"])) {
	$query = $conn->prepare("UPDATE images SET clicks = clicks + 1 WHERE imageUrl=:imgeURL");
	$query->bindParam(":imgeURL", $_POST["imageUrl"]);

	$query->execute();
}
else {
	echo "No images passed to page";
}
?>