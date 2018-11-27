<?php

$loader = new \Phalcon\Loader();

require BASE_PATH."/vendor/autoload.php";

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
    [
        $config->application->controllersDir,
        $config->application->APIDir,
        $config->application->modelsDir,
        $config->application->pluginsDir,
        $config->application->formsDir,
        $config->application->libraryDir,
        $config->application->modelsResponsesDir,
    ]
)->register();
