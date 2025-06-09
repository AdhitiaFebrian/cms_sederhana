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
        // Inisialisasi session dan auth
        Session::start();
        Auth::init();

        $this->router = new Router();
        $this->registerRoutes();
    }

    private function registerRoutes()
    {
        // Auth routes
        $this->router->get('/login', 'AuthController', 'login');
        $this->router->post('/login', 'AuthController', 'login');
        $this->router->get('/logout', 'AuthController', 'logout');

        // Home route
        $this->router->get('/', 'HomeController', 'index');
    }

    public function run()
    {
        $this->router->dispatch();
    }
} 