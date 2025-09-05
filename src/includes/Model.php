<?php
require_once __DIR__ . '/Database.php';
abstract class Model {
    protected $db;
    protected $table;
    protected $fillable = [];
    protected $orderBy = 'id'; // coluna padrão para ordenação

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function create(array $data) {
        $data = $this->filterData($data);
        $fields = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        
        $sql = "INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt) {
            $types = str_repeat('s', count($data));
            $stmt->bind_param($types, ...array_values($data));
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
        }
        
        return false;
    }

    public function update($id, array $data) {
        $data = $this->filterData($data);
        $fields = array_map(function($field) {
            return "{$field} = ?";
        }, array_keys($data));
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt) {
            $values = array_values($data);
            $values[] = $id;
            $types = str_repeat('s', count($data)) . 'i';
            $stmt->bind_param($types, ...$values);
            
            return $stmt->execute();
        }
        
        return false;
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('i', $id);
            return $stmt->execute();
        }
        
        return false;
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_assoc();
        }
        
        return null;
    }

    public function all() {
        $sql = "SELECT * FROM {$this->table} ORDER BY {$this->orderBy} DESC";
        $result = $this->db->query($sql);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }

    protected function filterData(array $data) {
        return array_intersect_key($data, array_flip($this->fillable));
    }

    public function where($field, $value) {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = ?";
        $stmt = $this->db->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('s', $value);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        
        return [];
    }

    public function findWhere(array $conditions) {
        $where = [];
        $values = [];
        $types = '';
        
        foreach ($conditions as $field => $value) {
            $where[] = "{$field} = ?";
            $values[] = $value;
            $types .= is_int($value) ? 'i' : 's';
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $where);
        $stmt = $this->db->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param($types, ...$values);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        
        return [];
    }
}
