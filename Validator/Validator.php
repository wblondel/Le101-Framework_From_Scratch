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
     * @return bool
     */
    public function isAlphaNum(string $field, string $errorMsg)
    {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $this->getField($field))) {
            $this->errors[$field] = $errorMsg;
            return false;
        }
        return true;
    }

    /**
     * Check if a password meets the following requirements:
     * - it must contain at least 1 lowercase alphabetical character
     * - it must contain at least 1 uppercase alphabetical character
     * - it must contain at least 1 numeric character
     * - it must contain at least one special character
     * - it must be 8 characters or longer
     *
     * @param string $field
     * @param string $errorMsg
     * @return bool
     */
    public function isPasswordStrong(string $field, string $errorMsg)
    {
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#\$%\^&\*])(?=.{8,})/', $this->getField($field))) {
            $this->errors[$field] = $errorMsg;
            return false;
        }
        return true;
    }

    /**
     * @param string $field
     * @param Database $database
     * @param string $table
     * @param string $errorMsg
     * @return bool
     */
    public function isUnique(string $field, Database $database, string $table, string $errorMsg = '')
    {
        $record = $database->prepare("SELECT id FROM $table WHERE $field = ?", [$this->getField($field)], null, true);
        if ($record) {
            $this->errors[$field] = $errorMsg;
            return false;
        }
        return true;
    }

    /**
     * @param string $field
     * @param string $errorMsg
     * @return bool
     */
    public function isEmail(string $field, string $errorMsg = '')
    {
        if (!filter_var($this->getField($field), FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $errorMsg;
            return false;
        }
        return true;
    }

    /**
     * @param $field
     * @param $errorMsg
     * @return bool
     */
    public function isConfirmed(string $field, string $errorMsg = '')
    {
        $value = $this->getField($field);
        if (empty($value) || $value != $this->getField($field . '_confirm')) {
            $this->errors[$field] = $errorMsg;
            return false;
        }
        return true;
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
