<?php

    function getErrorMessage($errorCode) {
        switch($errorCode) {
            case 1:
                return "Image not found.";
            default:
                return "Something went wrong.";
        };
    }

