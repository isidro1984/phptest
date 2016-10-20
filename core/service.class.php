<?php

class Service
{

    public $file;
    public $api;

    public static $variableStack = array();

    public function __construct($file)
    {
        $this->file = $file . ".php";

        $this->logged = 0;
    }

    public function renderPage()
    {

        return $this->run("services/" . $this->file);
    }

    public function run($file, $_variables = null)
    {
        self::$variableStack[] = $_variables;

        if (is_array($_variables)) {
            foreach ($_variables as $name => $value) {
                ${$name} = $value;
            }
        }

        if (file_exists(PATH_BASE . $file)) {
            include(PATH_BASE . $file);

            return true;
        }

        array_pop(self::$variableStack);

        return false;
    }

}