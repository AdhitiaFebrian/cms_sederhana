<?php
namespace Core;

/**
 * Class Model sebagai base class untuk semua model
 */
abstract class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function findAll()
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->fetchAll($sql);
    }

    public function findBy($field, $value)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = ?";
        return $this->db->fetch($sql, [$value]);
    }

    public function findAllBy($field, $value)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = ?";
        return $this->db->fetchAll($sql, [$value]);
    }

    public function create($data)
    {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data)
    {
        return $this->db->update(
            $this->table,
            $data,
            "{$this->primaryKey} = ?",
            [$id]
        );
    }

    public function delete($id)
    {
        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = ?",
            [$id]
        );
    }

    public function query($sql, $params = [])
    {
        return $this->db->query($sql, $params);
    }
} 