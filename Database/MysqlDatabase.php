<?php declare(strict_types=1);

namespace Core\Database;

/**
 * Class Database
 * @package App
 */
class MysqlDatabase extends Database
{
    /**
     * Database constructor.
     * @param string $dbname
     * @param string $dbuser
     * @param string $dbpass
     * @param string $dbhost
     */
    public function __construct(
        string $dbname,
        string $dbuser = 'root',
        string $dbpass = 'root',
        string $dbhost = 'localhost'
    ) {
        parent::__construct('mysql', $dbname, $dbuser, $dbpass, $dbhost);
    }

    /**
     * Checks if the statement is an insert/update/delete statement.
     * @param string $statement
     * @return bool
     */
    protected function isInsUpdDel(string $statement)
    {
        if (strpos($statement, 'UPDATE') === 0 ||
            strpos($statement, 'INSERT') === 0 ||
            strpos($statement, 'DELETE') === 0
        ) {
            return true;
        }
        return false;
    }
}
