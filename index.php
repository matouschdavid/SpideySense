<?php
// === register autoloader
spl_autoload_register(function ($class) {
    $file = __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
    if (file_exists($file)) {
        require_once($file);
    }
});
// configure service provider
$sp = new ServiceProvider();
//Application
$sp->register(\Application\TitleQuery::class);

//Presentation
$sp->register(\Presentation\MVC\MVC::class, function() {
    return new \Presentation\MVC\MVC();
}, true);

//Controllers
$sp->register(Presentation\Controllers\Home::class);

// TODO: handle request
$sp->resolve(\Presentation\MVC\MVC::class)->handleRequest($sp);