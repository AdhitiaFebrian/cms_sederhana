<?php
namespace Core;

/**
 * Class Application sebagai class utama aplikasi
 */
class Application
{
    private Router $router;

    public function __construct()
    {
        $this->router = new Router();
        $this->registerRoutes();
    }

    private function registerRoutes()
    {
        // Register routes di sini
        $this->router->get('/', 'HomeController', 'index');
    }

    public function run()
    {
        $this->router->dispatch();
    }
} 