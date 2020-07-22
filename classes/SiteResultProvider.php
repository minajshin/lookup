<?php
class SiteResultsProvider {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getNumResults($term) {
        $query = $this->conn->prepare("SELECT COUNT(*) as total
                                        FROM sites WHERE title LIKE :term
                                        OR url LIKE :term
                                        OR keywords LIKE :term
                                        OR description LIKE :term");
        $searchTerm = "%" . $term . "%";
        $query->bindParam(":term", $searchTerm);
        $query->execute();

        $row = $query->fetch(PDO::FETCH_ASSOC);
        return $row["total"];
    }

    public function getResultsHtml($page, $pageSize, $term) {
        $fromLimit = ($page-1) * $pageSize;

        $query = $this->conn->prepare("SELECT *
                                        FROM sites WHERE title LIKE :term
                                        OR url LIKE :term
                                        OR keywords LIKE :term
                                        OR description LIKE :term
                                        ORDER BY clicks DESC
                                        LIMIT :fromLimit, :pageSize");
        $searchTerm = "%" . $term . "%";
        $query->bindParam(":term", $searchTerm);
        $query->bindParam(":fromLimit", $fromLimit, PDO::PARAM_INT);
        $query->bindParam(":pageSize", $pageSize, PDO::PARAM_INT);
        $query->execute();

        $resultsHtml = "<div class='sites-result'>";
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $id = $row["id"];
            $url = $row["url"];
            $title = $row["title"];
            $description = $row["description"];

            $title = $this->trimField($title, 55);
            $description = $this->trimField($description, 230);



            $resultsHtml .= "<div class='result-wrapper'>
                                <h3 class='title'><a href='$url' data-linkId='$id'>$title</a></h3>
                                <span class='url'>$url</span>
                                <span class='description'>$description</span>
                            </div>";
        }

        $resultsHtml .= "</div>";

        return $resultsHtml;
    }



    private function trimField($str, $characterLimit) {
        $dots = strlen($str) > $characterLimit ? "..." : "";

        return substr($str, 0, $characterLimit) . $dots;
    }



} // End of class

?>