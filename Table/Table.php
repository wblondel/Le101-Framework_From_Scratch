<?php declare(strict_types=1);

namespace Core\Table;

use Core\Database\Database;

/**
 * Class Table
 * @package Core\Table
 * TODO: Make this Class compatible with multiple DBMS. It should use a Query Builder.
 */
class Table
{
    protected $table;
    protected $db;

    /**
     * Table constructor.
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
        if (is_null($this->table)) {
            $parts = explode('\\', get_class($this));
            $class_name = end($parts);
            $this->table = strtolower(str_replace('Table', '', $class_name)) . 's';
        }
    }

    /**
     * Return all the records from the Table.
     * @return mixed
     */
    public function all()
    {
        return $this->query('SELECT * FROM ' . $this->table);
    }

    /**
     * Return a record from the Table, selected with the given id.
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE id = ?", [$id], true);
    }

    /**
     * Update a record.
     * @param int $id
     * @param $fields
     * @return mixed
     */
    public function update(int $id, array $fields)
    {
        $sql_parts = [];
        $attributes = [];

        foreach ($fields as $k => $v) {
            $sql_parts[] = "$k = ?";
            $attributes[] = $v;
        }
        $attributes[] = $id;
        $sql_part = implode(', ', $sql_parts);
        return $this->query("UPDATE {$this->table} SET $sql_part WHERE id = ?", $attributes, true);
    }

    /**
     * Delete a record.
     * @param int $id
     * @return mixed
     */
    public function delete(int $id)
    {
        return $this->query("DELETE FROM {$this->table} WHERE id = ?", [$id], true);
    }

    /**
     * Create a record.
     * @param $fields
     * @return mixed
     */
    public function create($fields)
    {
        $sql_parts = [];
        $attributes = [];

        foreach ($fields as $k => $v) {
            $sql_parts[] = "$k = ?";
            $attributes[] = $v;
        }
        $sql_part = implode(', ', $sql_parts);
        return $this->query("INSERT INTO {$this->table} SET $sql_part", $attributes, true);
    }

    /**
     * @param $key
     * @param $value
     * @return array
     */
    public function list($key, $value)
    {
        $records = $this->all();
        $return = [];
        foreach ($records as $v) {
            $return[$v->$key] = $v->$value;
        }
        return $return;
    }

    /**
     * Execute a SQL query.
     * @param $statement
     * @param null|array $attributes
     * @param bool $one
     * @return mixed
     */
    public function query($statement, $attributes = null, $one = false)
    {
        if ($attributes) {
            return $this->db->prepare(
                $statement,
                $attributes,
                str_replace('Table', 'Entity', get_class($this)),
                $one
            );
        } else {
            return $this->db->query(
                $statement,
                str_replace('Table', 'Entity', get_class($this)),
                $one
            );
        }
    }
}
