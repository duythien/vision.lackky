<?php
use Phalcon\Mvc\Application;

defined('ROOT_DIR') || define('ROOT_DIR', dirname(dirname(__FILE__)));

require ROOT_DIR . '/config/loader.php';

try {
    require ROOT_DIR . '/config/service.php';

    $router = $di->getRouter();
    $router->handle();

    // Pass the processed router parameters to the dispatcher
    $dispatcher = $di->getDispatcher();
    $dispatcher->setControllerName($router->getControllerName());
    $dispatcher->setActionName($router->getActionName());
    $dispatcher->setParams($router->getParams());
    $dispatcher->dispatch();

    // Get the returned value by the last executed action
    $response = $dispatcher->getReturnedValue();
    // Check if the action returned is a 'response' object
    if ($response instanceof Phalcon\Http\ResponseInterface) {
        // Send the response
        $response->send();
    }
} catch (Exception $e) {
    echo $e->getMessage();
    dd($e->getTraceAsString());
}


