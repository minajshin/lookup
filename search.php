<?php
include("config.php");
include("classes/SiteResultProvider.php");

// Get search term
if (isset($_GET["q"])) {
    $term = $_GET["q"];
}

$type = isset($_GET["type"]) ? $_GET["type"] : "sites";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lookup</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="wrapper search-page">
    <header>
        <div class="top-header">
            <div class="logo-wrapper">
                <a href="/"><img src="assets/images/logo.png" alt="Lookup_Logo"></a>
            </div>

            <div class="search-wrapper">
                <form action="search.php">
                    <input class="search-box" type="text" name="q" value="<?php echo $term ?>">
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

    <main class="sites-wrapper">
        <?php 
        $resultsProvider = new SiteResultsProvider($conn);
        $numResults = $resultsProvider->getNumResults($term);

        echo "<p class='sites-counts'>$numResults results found</p>";

        echo $resultsProvider->getResultsHtml(1, 20, $term);
        
        ?>
    </main>


</body>

</html>