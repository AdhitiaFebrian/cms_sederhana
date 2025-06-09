<?php
namespace Core;

/**
 * Class View untuk menangani rendering view
 */
class View
{
    private $layout = 'default';
    private $view;
    private $data = [];

    public function __construct($view, $data = [])
    {
        $this->view = $view;
        $this->data = $data;
    }

    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }

    public function render()
    {
        // Extract data ke variabel
        extract($this->data);

        // Start output buffering
        ob_start();
        
        // Load view file
        $viewFile = APP_PATH . '/views/' . $this->view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            throw new \Exception("View {$this->view} tidak ditemukan");
        }
        
        // Get view content
        $content = ob_get_clean();

        // Load layout
        $layoutFile = APP_PATH . '/views/layouts/' . $this->layout . '.php';
        if (file_exists($layoutFile)) {
            require_once $layoutFile;
        } else {
            throw new \Exception("Layout {$this->layout} tidak ditemukan");
        }
    }

    public static function partial($name, $data = [])
    {
        extract($data);
        $file = APP_PATH . '/views/partials/' . $name . '.php';
        if (file_exists($file)) {
            require_once $file;
        } else {
            throw new \Exception("Partial {$name} tidak ditemukan");
        }
    }

    public static function asset($path)
    {
        return '/public/assets/' . ltrim($path, '/');
    }

    public static function url($path = '')
    {
        return '/' . ltrim($path, '/');
    }
} 