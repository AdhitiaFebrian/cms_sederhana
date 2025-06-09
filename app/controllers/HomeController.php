<?php
namespace App\Controllers;

use Core\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $data = [
            'title' => 'Selamat Datang di CMS',
            'message' => 'Ini adalah halaman utama CMS'
        ];
        
        return $this->render('home/index', $data);
    }
} 