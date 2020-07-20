<?php
include("classes/DomParser.php");


function followLinks($url) {
    $parser = new DomParser($url);

    $linkList = $parser->getLinks();
    foreach($linkList as $link) {
        $href = $link->getAttribute("href");
        echo $href . "<br>";
    }
}


$startUrl = "http://www.bbc.com";
followLinks($startUrl);

?>