<?php
namespace App\Models;

use Core\Model;

class User extends Model
{
    protected $table = 'users';

    public function findByUsername($username)
    {
        return $this->findBy('username', $username);
    }

    public function findByEmail($email)
    {
        return $this->findBy('email', $email);
    }

    public function createUser($data)
    {
        // Hash password sebelum disimpan
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $this->create($data);
    }

    public function updateUser($id, $data)
    {
        // Hash password jika diupdate
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        return $this->update($id, $data);
    }
} 