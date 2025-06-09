<?php
namespace Core;

/**
 * Class Auth untuk menangani autentikasi
 */
class Auth
{
    private static $user = null;

    public static function init()
    {
        Session::start();
        if (Session::has('user_id')) {
            $userModel = new \App\Models\User();
            self::$user = $userModel->find(Session::get('user_id'));
        }
    }

    public static function login($username, $password)
    {
        $userModel = new \App\Models\User();
        $user = $userModel->findByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            Session::set('user_id', $user['id']);
            self::$user = $user;
            return true;
        }

        return false;
    }

    public static function logout()
    {
        Session::remove('user_id');
        self::$user = null;
        Session::destroy();
    }

    public static function user()
    {
        return self::$user;
    }

    public static function check()
    {
        return self::$user !== null;
    }

    public static function id()
    {
        return self::$user ? self::$user['id'] : null;
    }

    public static function isAdmin()
    {
        return self::$user && self::$user['role'] === 'admin';
    }
} 