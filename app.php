<?php
    require "load.php";
    chdir("pages");

    require "partial/header.php";

    $page = $_GET["page"] ?? "upload";

    switch($page) {
        case "upload":
            require "upload.php";
            break;
        case "edit":
            require "edit.php";
            break;
        default:
            require "upload.php";
            break;
    }
    
    require "partial/footer.php";
?>