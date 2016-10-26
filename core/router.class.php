<?php

class Router
{
    public static $uri;
    public static $service;
    public static $params;

    public static function self()
    {
        return PATH_SERVER . "/" . implode("/", self::$uri);
    }
}

$tmp = explode("?", $_SERVER["REQUEST_URI"]);

Router::$uri = array_slice(
        explode(
                "/",
                reset(
                        $tmp
                )
        ),
        1
);
if (!end(Router::$uri)) {
    Router::$uri = array_slice(Router::$uri, 0, -1);
}

// if home site
if (empty(Router::$uri[0])) {
    Router::$service = 'koszonto';
    Router::$params = array();
} // if rewrite engine is off
else {
    if (Router::$uri[0] == 'router.php') {
        Router::$service = isset(Router::$uri[1]) ? Router::$uri[1] : 'home';
        Router::$params = array_slice(explode("/", $_SERVER["REQUEST_URI"]), 3);
    } else {
        
		Router::$service = isset(Router::$uri[0]) ? Router::$uri[0] : 'home';
        Router::$params = array_slice(explode("/", $_SERVER["REQUEST_URI"]), 2);
    }
}
