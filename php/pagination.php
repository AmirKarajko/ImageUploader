<?php
    function getPagination($currentPage, $itemsPerPage, $totalItems, $href) {
        $html = "";

        $totalPages = ceil($totalItems / $itemsPerPage);
        if($totalItems <= $itemsPerPage) {
            return $html;
        }


        $prevPage = $currentPage - 1;
        $nextPage = $currentPage + 1;


        $html .= <<<HTML
                        <div class="container mt-3">
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                    HTML;


        if($currentPage > 1) {
            $html .= <<<HTML
                            <li class="page-item">
                                <a class="page-link" href="$href?page=$prevPage">Previous</a>
                            </li>
                        HTML;
        }
        else {
            $html .= <<<HTML
                            <li class="page-item disabled">
                                <a class="page-link" href="#">Previous</a>
                            </li>
                        HTML;
        }

        for ($i = 1; $i <= $totalPages; $i++) {
            if($i == $currentPage) {
                $html .= <<<HTML
                                <li class="page-item disabled">
                                    <a class="page-link" href="#">$i</a>
                                </li>
                            HTML;
            }
            else {
                $html .= <<<HTML
                                <li class="page-item">
                                    <a class="page-link" href="$href?page=$i">$i</a>
                                </li>
                            HTML;
            }
        }

        if($currentPage < $totalPages) {
            $html .= <<<HTML
                            <li class="page-item">
                                <a class="page-link" href="$href?page=$nextPage">Next</a>
                            </li>
                        HTML;
        }
        else {
            $html .= <<<HTML
                            <li class="page-item disabled">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        HTML;
        }


        $html .= <<<HTML
                            </ul>
                        </nav>
                    </div>
                    HTML;


        return $html;
    }
?>