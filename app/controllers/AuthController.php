<?php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Session;

class AuthController extends Controller
{
    public function __construct()
    {
        // Terapkan GuestMiddleware untuk method login dan register
        $this->middleware('guest', ['login', 'register']);
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $this->getPost('username');
            $password = $this->getPost('password');

            if (Auth::login($username, $password)) {
                Session::flash('success', 'Login berhasil');
                return $this->redirect(View::url());
            }

            Session::flash('error', 'Username atau password salah');
            return $this->redirect(View::url('login'));
        }

        return $this->render('auth/login', [
            'title' => 'Login'
        ]);
    }

    public function logout()
    {
        Auth::logout();
        Session::flash('success', 'Logout berhasil');
        return $this->redirect(View::url('login'));
    }

    private function middleware($type, $methods = [])
    {
        $currentMethod = debug_backtrace()[1]['function'];
        if (in_array($currentMethod, $methods)) {
            $middleware = "\\Core\\{$type}Middleware";
            (new $middleware())->handle();
        }
    }
} 