<?php
namespace Core;

/**
 * Class Middleware untuk menangani middleware
 */
abstract class Middleware
{
    abstract public function handle();
}

class AuthMiddleware extends Middleware
{
    public function handle()
    {
        if (!Auth::check()) {
            Session::flash('error', 'Silakan login terlebih dahulu');
            header('Location: ' . View::url('login'));
            exit;
        }
    }
}

class AdminMiddleware extends Middleware
{
    public function handle()
    {
        if (!Auth::isAdmin()) {
            Session::flash('error', 'Akses ditolak. Anda tidak memiliki hak akses admin');
            header('Location: ' . View::url());
            exit;
        }
    }
}

class GuestMiddleware extends Middleware
{
    public function handle()
    {
        if (Auth::check()) {
            header('Location: ' . View::url());
            exit;
        }
    }
} 