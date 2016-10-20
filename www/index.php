<?php
include "../core/basics.php";

if (file_exists(PATH_SERVICES . Router::$service . ".php")) {
    CORE::$SERVICE = new Service(Router::$service);
} else {
    CORE::$SERVICE = new SERVICE("404");
}

CORE::$SERVICE->renderPage();
