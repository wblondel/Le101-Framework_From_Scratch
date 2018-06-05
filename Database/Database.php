<?php declare(strict_types=1);

namespace Core\Database;

use \PDO;

/**
 * Class Database
 * @package Core\Database
 */
abstract class Database
{
    private $dbms;
    private $dbname;
    private $dbuser;
    private $dbpass;
    private $dbhost;
    private $pdo;

    /**
     * Database constructor.
     * @param string $dbms
     * @param string $dbname
     * @param string $dbuser
     * @param string $dbpass
     * @param string $dbhost
     */
    public function __construct(
        string $dbms,
        string $dbname,
        string $dbuser,
        string $dbpass,
        string $dbhost
    ) {
        $this->dbms = $dbms;
        $this->dbname = $dbname;
        $this->dbuser = $dbuser;
        $this->dbpass = $dbpass;
        $this->dbhost = $dbhost;
    }

    /**
     * Return a database connection.
     * Create it if it doesn't exist.
     * @return PDO
     */
    protected function getPDO()
    {
        if ($this->pdo === null) {
            try {
                $pdo = new PDO(
                    $this->dbms . ':dbname=' . $this->dbname . ';host=' . $this->dbhost,
                    $this->dbuser,
                    $this->dbpass
                );
            } catch (\PDOException $e) {
                die('Connection failed : ' . $e->getMessage());
            }
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo = $pdo;
        }
        return $this->pdo;
    }

    /**
     * @param string $statement The query to execute.
     * @param string|null $class_name The name of the class to use to store the object
     * @param bool $one
     * @return array|bool|mixed|\PDOStatement
     */
    public function query(string $statement, string $class_name = null, bool $one = false)
    {
        $request = $this->getPDO()->query($statement);
        if ($this->isInsUpdDel($statement)) {
            return $request;
        }
        if ($class_name === null) {
            $request->setFetchMode(PDO::FETCH_OBJ);
        } else {
            $request->setFetchMode(PDO::FETCH_CLASS, $class_name);
        }
        if ($one) {
            $data = $request->fetch();
        } else {
            $data = $request->fetchAll();
        }
        return $data;
    }

    /**
     * @param string $statement The statement to prepare.
     * @param array $attributes
     * @param string|null $class_name The name of the class to use to store the object
     * @param bool $one
     * @return array|bool|mixed
     */
    public function prepare(string $statement, array $attributes, string $class_name = null, bool $one = false)
    {
        $request = $this->getPDO()->prepare($statement);
        $result = $request->execute($attributes);
        if ($this->isInsUpdDel($statement)) {
            return $result;
        }
        if ($class_name === null) {
            $request->setFetchMode(PDO::FETCH_OBJ);
        } else {
            $request->setFetchMode(PDO::FETCH_CLASS, $class_name);
        }
        if ($one) {
            $data = $request->fetch();
        } else {
            $data = $request->fetchAll();
        }
        return $data;
    }

    /**
     * Return the id of the last inserted element.
     * @return string
     */
    public function lastInsertId()
    {
        return $this->getPDO()->lastInsertId();
    }

    /**
     * Checks if the statement is an insert/update/delete statement.
     * @param string $statement
     * @return bool
     */
    abstract protected function isInsUpdDel(string $statement);
}
