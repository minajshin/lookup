<?php
include("classes/DomParser.php");


/**
 * Convert a relative link to absolute url
 */
function createLink($src, $url) {
    $scheme = parse_url($url)["scheme"];
    $host = parse_url($url)["host"];

    if (substr($src, 0, 2) == "//") {
        $src = $scheme . ":" . $src;
    }
    else if (substr($src, 0, 1) == "/") {
        $src = $scheme . "://" . $host . $src;
    }
    else if (substr($src, 0, 2) == "./") {
        $src = $scheme . "://" . $host . dirname(parse_url($url)["path"]) . substr($src, 1);
    }
    else if(substr($src, 0, 3) == "../") {
		$src = $scheme . "://" . $host . "/" . $src;
	}
	else if(substr($src, 0, 5) != "https" && substr($src, 0, 4) != "http") {
		$src = $scheme . "://" . $host . "/" . $src;
	}

	return $src;
}


function followLinks($url) {
    $parser = new DomParser($url);

    $linkList = $parser->getLinks();
    foreach($linkList as $link) {
        $href = $link->getAttribute("href");

        if (strpos($href, "#") !== false || substr($href, 0, 11) == "javascript:") {
            continue;
        }

        $href = createLink($href, $url);
        echo $href . "<br>";
    }
}


$startUrl = "http://www.bbc.com";
followLinks($startUrl);
