<?php

use Phalcon\Mvc\Router\Group;
use Phalcon\Mvc\Router;

$router = new Router(false);
$router->setDefaults([
    'controller' => 'tests',
    'action'     => 'index'
]);
$router->removeExtraSlashes(true);
$prefix = '/' . VERSION . '/';

//tests
$tests = new Group(['controller' => 'tests']);
$tests->setPrefix($prefix . 'tests');
$tests->addGet('', ['action' => 'index']);
$tests->addGet('/{id:[0-9]+}', ['action' => 'view']);
$tests->addPost('/new', ['action' => 'new']);
$tests->addPut('/{id:[0-9]+}', ['action' => 'update']);

$images = new Group(['controller' => 'images']);
$images->setPrefix($prefix . 'images');
$images->addGet('', ['action' => 'index']);
$images->addPost('/describe', ['action' => 'describe']);
$images->addPost('/analyze', ['action' => 'analyze']);



//mount
$router->mount($tests);
$router->mount($images);

return $router;
