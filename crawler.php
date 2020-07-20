<?php
include("config.php");
include("classes/DomParser.php");

$crawlingList = array();        // urls to crawl
$crawledSites = array();         // urls already crawled
$crawledImages = array();         // urls already crawled

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
    global $crawledImages;

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
    
    if(linkExists($url)) {
		echo "$url already exists<br>";
	}
	else if(insertLinkToDb($url, $title, $description, $keywords)) {
		echo "$url inserted<br>";
	}
	else {
		echo "Failed to insert $url<br>";
    }
    
    // image detail
    $imgTags = $parser->getImgTags();
    foreach($imgTags as $img) {
        $src = $img->getAttribute("src");
        $alt = $img->getAttribute("alt");
        $title = $img->getAttribute("title");
        if(!$title && !$alt) {      
            // ignore img with no title and no alt
			continue;
        }
        
        $src = createLink($src, $url);
        if(!in_array($src, $crawledImages)) {
			$crawledImages[] = $src;
            insertImage($url, $src, $alt, $title);
        }
    }
}


/**
 * Check if a link exsists in the database already
 */
function linkExists($url) {
    global $conn;
    $query = $conn->prepare("SELECT * FROM sites WHERE url=:url");

    $query->bindParam(":url", $url);
    $query->execute();

    return $query->rowCount() != 0;
}



/**
 * Insert urls into DB
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


/**
 * Insert image into DB
 */
function insertImage($url, $src, $alt, $title) {
    global $conn;
    $query = $conn->prepare("INSERT INTO images(siteUrl, imageUrl, alt, title)
                            VALUES(:siteUrl, :imageUrl, :alt, :title)");

	$query->bindParam(":siteUrl", $url);
	$query->bindParam(":imageUrl", $src);
	$query->bindParam(":alt", $alt);
	$query->bindParam(":title", $title);

	return $query->execute();
}


function followLinks($url) {
    global $crawlingList;
    global $crawledSites;

    $parser = new DomParser($url);

    $linkList = $parser->getLinks();
    foreach($linkList as $link) {
        $href = $link->getAttribute("href");

        if (strpos($href, "#") !== false || substr($href, 0, 11) == "javascript:") {
            continue;
        }

        $href = createLink($href, $url);

        if (!in_array($href, $crawledSites)) {
            $crawlingList[] = $href;
            $crawledSites[] = $href;

            getDetails($href);
        }
    }

    array_shift($crawlingList);
    foreach($crawlingList as $site) {
        followLinks($site);
    }
}


$startUrl = "http://www.cnn.com";
followLinks($startUrl);
