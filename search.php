<?php
include("config.php");
include("classes/SiteResultProvider.php");

// Get search term
if (isset($_GET["term"])) {
    $term = $_GET["term"];
}

$type = isset($_GET["type"]) ? $_GET["type"] : "sites";
$page = isset($_GET["page"]) ? $_GET["page"] : "1";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lookup</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
</head>

<body class="wrapper search-page">
    <header>
        <div class="top-header">
            <div class="logo-wrapper">
                <a href="/"><img src="assets/images/logo.png" alt="Lookup_Logo"></a>
            </div>

            <div class="search-wrapper">
                <form action="search.php">
                    <input type="hidden" name="type" value="<?php echo $type ?>">
                    <input class="search-box" type="text" name="term" value="<?php echo $term ?>">
                    <input class="search-button" type="submit" value="Search">

                </form>
            </div>
        </div> <!-- .top-header -->

        <div class="tab-wrapper">
            <ul class="tabs">
                <li class="<?php echo $type == 'sites' ? 'active' : '' ?>">
                    <a href='<?php echo "search.php?term=$term&type=sites"; ?>'>Sites</a>
                </li>

                <li class="<?php echo $type == 'images' ? 'active' : '' ?>">
                    <a href='<?php echo "search.php?term=$term&type=images"; ?>'>Images</a>
                </li>
            </ul>
        </div> <!-- .tab-wrapper -->
    </header>

    <main>
        <div class="sites-wrapper">
            <?php
            $resultsProvider = new SiteResultsProvider($conn);
            $pageSize = 20;
            $numResults = $resultsProvider->getNumResults($term);

            echo "<p class='sites-counts'>$numResults results found</p>";

            echo $resultsProvider->getResultsHtml($page, $pageSize, $term);

            ?>
        </div>

        <div class="pagination-wrapper">
            <div class="page-buttons">
                <div class="button">
                    <img src="assets/images/page_start.png">
                </div>

                <?php

                $pagesToShow = 10;
                $numPages = ceil($numResults / $pageSize);
                $pagesLeft = min($pagesToShow, $numPages);
                $currentPage = $page - floor($pagesToShow / 2);
                if ($currentPage < 1) {
                    $currentPage = 1;
                }

                if ($currentPage + $pagesLeft > $numPages + 1) {
                    $currentPage = $numPages + 1 - $pagesLeft;
                }


                while ($pagesLeft != 0 && $currentPage <= $numPages) {

                    if ($currentPage == $page) {
                        echo "<div class='button'>
                                <img src='assets/images/page_selected.png'>
                                <span class='number'>$currentPage</span>
                            </div>";
                    }
                    else {
                        echo "<div class='button'>
                            <a href='search.php?term=$term&type=$type&page=$currentPage'>
                                <img src='assets/images/page.png'>
                                <span class='number'>$currentPage</span>
                            </a>
                        </div>";
                    }

                    $currentPage++;
                    $pagesLeft--;
                }
                ?>

                <div class="button">
                    <img src="assets/images/page_end.png">
                </div>
            </div>
        </div>
    </main>

    <script src="assets/js/script.js"></script>
</body>

</html>