<?php
    function getNavbar($items, $searchEnabled) {
        $search = isset($_GET["search"]) ? $_GET["search"] : "";
        
        $itemsHTML = "";
        foreach ($items as $item) {
            $title = $item->title;
            $href = $item->href;
            $active = $item->active ? "active" : "";
            if(isset($item->form)) {
                $form = $item->form;
                $itemsHTML .= <<<HTML
                                    <li class="nav-item">
                                        <button type="submit" class="nav-link $active" aria-current="page" form="$form" style="border: none; outline: none; background: none;">$title</button>
                                    </li>
                                HTML;
            }
            else {
                $itemsHTML .= <<<HTML
                                    <li class="nav-item">
                                        <a class="nav-link $active" aria-current="page" href="$href">$title</a>
                                    </li>
                                HTML;
            }
        }

        $html = <<<HTML
                        <nav class="navbar navbar-expand-lg navbar-light bg-light">
                            <div class="container-fluid">
                                <a class="navbar-brand" href="#">Image Uploader</a>
                                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                                    <span class="navbar-toggler-icon"></span>
                                </button>
                                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                                        $itemsHTML
                                    </ul>
                    HTML;

        if ($searchEnabled) {
            $html .= <<<HTML
                            <form method="get" class="d-flex">
                                <div class="input-group">
                                    <input type="text" class="form-control me-2" name="search" placeholder="Search" aria-label="Search" value="$search" />
                                    <button class="input-group-text btn-success" type="submit">Search</button>
                                </div>
                            </form>
                        HTML;
        }

        $html .= <<<HTML
                                </div>
                            </div>
                        </nav>
                    HTML;

        return $html;
    }
?>