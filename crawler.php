<?php
include("config.php");
include("classes/DomParser.php");

$crawlingList = array();        // urls to crawl
$crawledList = array();         // urls already crawled

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


/**
 * Get details of the given url
 */
function getDetails($url) {
    $parser = new DomParser($url);
    $titleTags = $parser->getTitleTags();
    if (sizeof($titleTags) == 0 || $titleTags->item(0) == NULL) {
        return;
    }

    $title = $titleTags->item(0)->nodeValue;
    $title = str_replace("\n", "", $title);
    if ($title == "") {
        return;
    } 


	$description = "";
	$keywords = "";
    $metaTags = $parser->getMetaTags();
    foreach($metaTags as $meta) {
		if($meta->getAttribute("name") == "description") {
			$description = $meta->getAttribute("content");
		}

		if($meta->getAttribute("name") == "keywords") {
			$keywords = $meta->getAttribute("content");
		}
    }
    $description = str_replace("\n", "", $description);
	$keywords = str_replace("\n", "", $keywords);
}


/**
 * Insert urls into database
 */
function insertLinkToDb($url, $title, $description, $keywords) {
    global $conn;
    $query = $conn->prepare("INSERT INTO sites(url, title, description, keywords)
							VALUES(:url, :title, :description, :keywords)");

	$query->bindParam(":url", $url);
	$query->bindParam(":title", $title);
	$query->bindParam(":description", $description);
	$query->bindParam(":keywords", $keywords);

	return $query->execute();
}



function followLinks($url) {
    global $crawlingList;
    global $crawledList;

    $parser = new DomParser($url);

    $linkList = $parser->getLinks();
    foreach($linkList as $link) {
        $href = $link->getAttribute("href");

        if (strpos($href, "#") !== false || substr($href, 0, 11) == "javascript:") {
            continue;
        }

        $href = createLink($href, $url);

        if (!in_array($href, $crawledList)) {
            $crawlingList[] = $href;
            $crawledList[] = $href;

            getDetails($href);
        }
    }

    array_shift($crawlingList);
    foreach($crawlingList as $site) {
        followLinks($site);
    }
}


$startUrl = "http://www.bbc.com";
followLinks($startUrl);
