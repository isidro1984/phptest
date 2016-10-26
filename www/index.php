<?php
include "../core/basics.php";

if (file_exists(PATH_SERVICES . Router::$params[1] . ".php")) {
    CORE::$SERVICE = new Service(Router::$params[1]);
} else {
    CORE::$SERVICE = new SERVICE("404");
}

CORE::$SERVICE->renderPage();
