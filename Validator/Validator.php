<?php declare(strict_types=1);

namespace Core\Validator;

use Core\Database\Database;

/**
 * Class Validator
 * @package Core\Validator
 */
class Validator
{
    private $data;
    private $errors = [];

    /**
     * Validator constructor.
     * @param $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }


    /**
     * @param string $field
     * @return null|mixed
     */
    private function getField(string $field)
    {
        if (!isset($this->data[$field])) {
            return null;
        }
        return $this->data[$field];
    }


    /**
     * @param string $field
     * @param string $errorMsg
     */
    public function isAlphaNum(string $field, string $errorMsg)
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $this->getField($field))) {
            $this->errors[$field] = $errorMsg;
        }
    }


    /**
     * @param string $field
     * @param Database $database
     * @param string $table
     * @param string $errorMsg
     */
    public function isUnique(string $field, Database $database, string $table, string $errorMsg = '')
    {
        $record = $database->prepare("SELECT id FROM $table WHERE $field = ?", [$this->getField($field)], null, one);
        if ($record) {
            $this->errors[$field] = $errorMsg;
        }
    }

    /**
     * @param string $field
     * @param string $errorMsg
     */
    public function isEmail(string $field, string $errorMsg = '')
    {
        if (!filter_var($this->getField($field), FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $errorMsg;
        }
    }

    /**
     * @param $field
     * @param $errorMsg
     */
    public function isConfirmed(string $field, string $errorMsg = '')
    {
        $value = $this->getField($field);
        if (empty($value) || $value != $this->getField($field . '_confirm')) {
            $this->errors[$field] = $errorMsg;
        }
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return empty($this->errors);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
