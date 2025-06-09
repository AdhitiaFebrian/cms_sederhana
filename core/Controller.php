<?php
namespace Core;

/**
 * Class Controller sebagai base class untuk semua controller
 */
abstract class Controller
{
    protected function render($view, $data = [])
    {
        // Extract data ke variabel
        extract($data);
        
        // Load view file
        $viewFile = APP_PATH . '/views/' . $view . '.php';
        if (file_exists($viewFile)) {
            require_once $viewFile;
        } else {
            throw new \Exception("View {$view} tidak ditemukan");
        }
    }

    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }

    protected function json($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    protected function getPost($key = null)
    {
        if ($key === null) {
            return $_POST;
        }
        return $_POST[$key] ?? null;
    }

    protected function getQuery($key = null)
    {
        if ($key === null) {
            return $_GET;
        }
        return $_GET[$key] ?? null;
    }
} 