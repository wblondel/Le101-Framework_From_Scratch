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
        $dbname = null,
        $dbuser = null,
        $dbpass = null,
        $dbhost = null
	) {
		$dbname = is_null($dbname) ? "mydb" : $dbname;
		$dbuser = is_null($dbuser) ? "root" : $dbuser;
		$dbpass = is_null($dbpass) ? "root" : $dbpass;
		$dbhost = is_null($dbhost) ? "127.0.0.1" : $dbhost;
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
