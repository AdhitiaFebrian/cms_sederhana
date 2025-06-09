<?php
namespace Core;

/**
 * Class Controller sebagai base class untuk semua controller
 */
abstract class Controller
{
    protected function render($view, $data = [])
    {
        $view = new View($view, $data);
        return $view->render();
    }

    protected function renderPartial($name, $data = [])
    {
        return View::partial($name, $data);
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