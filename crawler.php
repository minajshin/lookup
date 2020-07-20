<?php
include("classes/DomParser.php");


function followLinks($url) {
    $parser = new DomParser($url);
}


$startUrl = "http://www.bbc.com";
followLinks($startUrl);

?>