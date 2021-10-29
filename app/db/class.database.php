<?php

namespace App\Db;

require_once(__DIR__.'/../'.'class.log.php');
require_once(__DIR__.'/../'.'class.objects.php');

use App\Log as Log;
use App\Objects as Objects;
use PDO;
use PDOException;

/**
 * @author
 * Web Design Enterprise
 * Website: www.webdesignenterprise.com
 * E-mail: info@webdesignenterprise.com
 *
 * @copyright
 * This work is licensed under the Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 United States License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/3.0/legalcode
 *
 * Be aware, violating this license agreement could result in the prosecution and punishment of the infractor.
 *
 * @copyright 2002- date('Y') Web Design Enterprise Corp. All rights reserved.
 */

class Database extends Objects
{
    /**
     * The singleton instance
     *
     */
    private static $PDOInstance;
    /**
     * Creates a PDO instance representing a connection to a database and makes the instance available as a singleton
     *
     * @param string $dsn The full DSN, eg: mysql:host=localhost;dbname=testdb
     * @param string $username The user name for the DSN string. This parameter is optional for some PDO drivers.
     * @param string $password The password for the DSN string. This parameter is optional for some PDO drivers.
     * @param array $driver_options A key=>value array of driver-specific connection options
     *
     * @return PDO
     */
    public function __construct($dsn, $username = false, $password = false, $driver_options = false)
    {
        if (!self::$PDOInstance) {
            try {
                self::$PDOInstance = new PDO($dsn, $username, $password, $driver_options);
            } catch (PDOException $e) {
                die('PDO CONNECTION ERROR: ' .$e->getMessage());
            }
        }
    }

    /**
     * Initiates a transaction
     *
     * @return bool
     */
    public function begin_transaction()
    {
        return self::$PDOInstance->beginTransaction();
    }
        
    /**
     * Commits a transaction
     *
     * @return bool
     */
    public function commit()
    {
        return self::$PDOInstance->commit();
    }


    /**
     * Rolls back a transaction
     *
     * @return bool
     */
    public function roll_back()
    {
        return self::$PDOInstance->rollBack();
    }

    /**
     * Fetch the SQLSTATE associated with the last operation on the database handle
     *
     * @return string
     */
    public function error_code()
    {
        return self::$PDOInstance->errorCode();
    }
    
    /**
     * Fetch extended error information associated with the last operation on the database handle
     *
     * @return array
     */
    public function sql_error()
    {
        return self::$PDOInstance->errorInfo();
    }
    
    /**
     * Execute an SQL statement and return the number of affected rows
     *
     * @param string $statement
     */
    public function exec($statement)
    {
        return self::$PDOInstance->exec($statement);
    }

    /**
     * Return an array of available PDO drivers
     *
     * @return array
     */
    public function get_available_drivers()
    {
        return Self::$PDOInstance->getAvailableDrivers();
    }
    
    /**
     * Returns the ID of the last inserted row or sequence value
     *
     * @param string $name Name of the sequence object from which the ID should be returned.
     * @return int
     */
    public function sql_nextid($name = ''): int
    {
        return (int) self::$PDOInstance->lastInsertId($name);
    }

    /**
     * @param \Exception $e
     * @param string|null $query
     */
    public static function log_database_error(\Exception $e, string $query = null, $values = null): void
    {
        $message = "ERROR: {$e->getMessage()} ".PHP_EOL.
        "File: {$e->getFile()}:{$e->getLine()}".PHP_EOL.
        PHP_EOL.
        $e->getTraceAsString();

        if ($query) {
            $message .= PHP_EOL.PHP_EOL.trim($query);
        }
        if ($values) {
            $message .= PHP_EOL.PHP_EOL.'VALUES'.PHP_EOL.var_export($values, true);
        }

        new Log($message);

        if (_DUMP_DEBUG) {
            echo "<pre>{$message}</pre>";
        } else {
            echo 'There was an error. Sorry for the inconvenience.';
        }

        die();
    }

    /**
     * Prepares a statement for execution and returns a statement object
     *
     * @param string $statement A valid SQL statement for the target database server
     * @param array $driver_options Array of one or more key=>value pairs to set attribute values for the PDOStatement obj returned
     * @return PDOStatement
     */
    public function prepare($statement, $driver_options = false): \PDOStatement
    {
        if (!$driver_options) {
            $driver_options = [];
        }
        
        return self::$PDOInstance->prepare($statement, $driver_options);
    }
    
    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     *
     * @param string $statement
     * @return PDOStatement
     */
    public function sql_query($statement)
    {
        return self::$PDOInstance->query($statement);
    }
    
    /**
     * Execute query and return all rows in assoc array
     *
     * @param string $statement
     * @return array
     */
    public function sql_fetchrow($statement)
    {
        return self::$PDOInstance->query($statement)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Execute query and return one row in assoc array
     *
     * @param string $statement
     * @return array
     */
    public function sql_fetchone($statement)
    {
        return self::$PDOInstance->query($statement)->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Execute query and select one column only
     *
     * @param string $statement
     * @return mixed
     */
    public function sql_fieldname($statement)
    {
        return self::$PDOInstance->query($statement)->fetchColumn();
    }

    public function create_tmp_table($table = '', $from_table = '')
    {
        if (!empty($table) && !empty($from_table)) {
            $_query = "
                CREATE TEMPORARY TABLE "._DB_PREFIX.'_'.trim($table)." LIKE "._DB_PREFIX.'_'.trim($from_table)."
            ";

            return self::sql_query($_query);
        }
        
        return false;
    }
    /**
     * Check if given table exists in database
     *
     * @param  string $table table name to check
     * @return boolean
     */
    public function table_exists($table = '')
    {
        $_query = "
            SHOW TABLES LIKE '"._DB_PREFIX.'_'.trim($table)."'
        ";
        
        $_rows = self::sql_fetchone($_query);

        return (@is_array($_rows)) ? true : false;
    }
    /**
     * Get table field names, good for comparison
     *
     * @param  string $table table name to collect info from
     * @return array
     */
    public function get_fields_names($table = '')
    {
        try {
            $_query = "
                DESCRIBE "._DB_PREFIX.'_'.trim($table)."
            ";

            foreach (self::sql_fetchrow($_query) as $info) {
                $return[] = $info['Field'];
            }

            return $return;
        } catch (Exception $e) {
            self::log_database_error($e);
        }
    }
    /**
     * Get table field full information, good for comparison
     *
     * @param  string $table table name to collect info from
     * @return array
     */
    public function get_fields_info($table = '')
    {
        try {
            $_query = "
                SHOW FULL COLUMNS FROM "._DB_PREFIX."_".trim($table)."
            ";

            foreach (self::sql_fetchrow($_query) as $info) {
                $return[] = $info;
            }

            return $return;
        } catch (Exception $e) {
            self::log_database_error($e);
        }
    }

    public function clean_data_fields($table = '', $data = [])
    {
        $_available_fields = $this->get_fields_names($table);

        if (!empty($_available_fields) && !empty($data)) {
            foreach ($data as $key => $value) {
                if (!in_array($key, $_available_fields)) {
                    unset($data[$key]);
                }
            }
        }

        return $data;
    }

    /**
     * Count rows on database
     * @param string $table table to read from
     * @param string|array $where conditions
     * @return int
     */
    public function sql_count($table = '', $where = '')
    {
        try {
            $_query = "
                SELECT COUNT(*) AS CNT
                FROM "._DB_PREFIX.'_'.trim($table)."
            ";

            $values = [];

            if (!empty($where)) {
                if (is_array($where) && count($where) > 0) {
                    $where_fields = [];

                    foreach ($where as $key => $value) {
                        $where_fields[] = $key;
                        $values[] = $value;
                    }

                    $_query .= "
                        WHERE ".implode(' = ? AND ', $where_fields)." = ?
                    ";
                } elseif (is_numeric($where)) {
                    $values[] = (int) $where;

                    $_query .= "
                        WHERE id = ?
                    ";
                } elseif (is_string($where)) {
                    $_query .= "
                        WHERE ".$where."
                    ";
                }
            }

             $return = $this->prepare($_query);
            $return->execute($values);
            $count = $return->fetch(PDO::FETCH_ASSOC);

            return (int) $count['CNT'];
        } catch (PDOException $e) {
            self::log_database_error($e, $_query);
        }
    }

    /**
     * Select from database
     * @param  string $table   table to read from
     * @param  array  $columns Fields to collect info from
     * @param  array  $where   where statement
     * @param  array  $order   order statement
     * @param  array  $limit   limit statement
     * @return array
     */
    public function sql_get($table = '', $columns = '', $where = '', $order = '', $limit = [])
    {
        try {
            $_query = 'SELECT ';

            if (!empty($columns)) {
                if (is_array($columns) && count($columns) > 0) {
                    $_query .= ' ' .implode(', ', $columns). ' ';
                } elseif (is_string($columns)) {
                    $_query .= " $columns ";
                }
            } else {
                $_query .= ' * ';
            }

            $_query .= ' FROM ' . _DB_PREFIX . '_' . trim($table) . ' ';

            $values = [];

            if (!empty($where)) {
                if (is_array($where) && count($where) > 0) {
                    $where_fields = [];

                    foreach ($where as $key => $value) {
                        $where_fields[] = $key;
                        $values[] = $value;
                    }

                    $_query .= ' WHERE `' . implode('` = ? AND `', $where_fields). '`  = ? ';
                } elseif (is_numeric($where)) {
                    $values[] = (int) $where;

                    $_query .= ' WHERE id = ? ';
                } elseif (is_string($where)) {
                    $_query .= ' WHERE ' .$where. ' ';
                }
            }

            if (!empty($order)) {
                $_query .= ' ORDER BY ' .$order. ' ';
            }

            if (is_array($limit) && count($limit) > 0) {
                foreach ($limit as $value) {
                    $limit_fields[] = $value;
                }

                $_query .= ' LIMIT ' .implode(', ', $limit_fields). ' ';
            }

            $return = self::prepare($_query);
            $return->execute($values);
            $results = $return->fetchAll(PDO::FETCH_ASSOC);

            return $results;
        } catch (PDOException $e) {
            self::log_database_error($e, $_query);
        }
    }

    /**
     * Select one row from database
     * @param  string $table   table to read from
     * @param  array  $where   where statement can receive numeric(row id) array(different clauses)
     * @param  array  $order   order statement
     * @param mixed $columns
     * @return array
     */
    public function sql_get_one($table = '', $columns = '', $where = '', $order = '')
    {
        return $this->sql_get($table, $columns, $where, $order, [1])[0];
    }

    /**
     * Update or insert data into table depending on data
     *
     * @param string $table table to update
     * @param array $data array data to be set
     * @return integral field id that had being updated
     */
    public function sql_save($table = '', $data = [])
    {
        try {
            if (array_key_exists('id', $data)) {
                $id = (int) $data['id'];
                unset($data['id']);

                self::sql_update($table, $data, $id);

                return $id;
            }
            
            return self::sql_insert($table, $data);
        } catch (PDOException $e) {
            self::log_database_error($e);
        }
    }

    /**
     * Update table
     *
     * @param string $table table to update
     * @param array $data array data to be set
     * @param mixed $where
     * @return bool
     */
    public function sql_update($table = '', $data = [], $where = '')
    {
        try {
            if (isset($data['id'])) {
                unset($data['id']);
            }
            
            $table = trim($table);
            $_table_fields_names = $this->get_fields_names($table);
            $fields = [];
            $values = [];

            foreach ($data as $key => $value) {
                if (in_array($key, $_table_fields_names)) {
                    $fields[] = $key;
                    $values[] = $value;
                }
            }

            $_query = "
                UPDATE "._DB_PREFIX.'_'.$table."
                SET ".implode(' = ?, ', $fields)." = ?
            ";

            if (!empty($where)) {
                if (is_array($where) && count($where) > 0) {
                    $where_fields = [];

                    foreach ($where as $key => $value) {
                        $where_fields[] = $key;
                        $values[] = $value;
                    }

                    $_query .= "
                        WHERE ".implode(' = ? AND ', $where_fields)." = ?
                    ";
                } elseif (is_numeric($where)) {
                    $values[] = (int) $where;

                    $_query .= "
                        WHERE id = ?
                    ";
                } elseif (is_string($where)) {
                    $_query .= "
                        WHERE ".$where."
                    ";
                }
            }

            return $this->prepare($_query)->execute($values);
        } catch (PDOException $e) {
            self::log_database_error($e, $_query, $values);

            return false;
        }
    }
    
    /**
     * Insert data into table
     *
     * @param string $table table to update
     * @param array $data array data to be set
     * @return int
     */
    public function sql_insert($table = '', $data = [])
    {
        try {
            $table = trim($table);
            $_table_fields_names = $this->get_fields_names($table);
            $fields = [];
            $values = [];

            if (isset($data['date'])) {
                if (empty($data['date']) || is_numeric($data['date'])) {
                    $data['date'] = date('Y-m-d H:i:s');
                }
            }
            
            foreach ($data as $key => $value) {
                if (in_array($key, $_table_fields_names)) {
                    $fields[] = $key;
                    $values[] = $value;
                } else {
                    unset($data[$key]);
                }
            }

            # Set place holders for prepared query
            $place_holders = array_fill(0, count($data), '?');

            $_query =
                ' INSERT INTO ' ._DB_PREFIX.'_'.$table.
                ' (' .implode(", ", $fields). ') ' .
                ' VALUES ' .
                ' (' .implode(", ", $place_holders). ')';

            $this->prepare($_query)->execute($values);
            return $this->sql_nextid();

        } catch (PDOException $e) {
            self::log_database_error($e, $_query, $values);
        }
    }

    /**
     * Set Active (1) / Deactive (2) rows as needed
     * @param  string  $table table to update
     * @param  integer $id    item id to be updated
     * @return boolean
     */
    public function sql_activate($table = '', $id = 0)
    {
        try {
            $_query = "
                UPDATE "._DB_PREFIX.'_'.trim($table)." 
                SET active = 1 
                WHERE id = ?
            ";

            return self::prepare($_query)->execute([$id]);
        } catch (PDOException $e) {
            self::log_database_error($e, $_query);
        }
    }
    
    /**
     * Set 1 or 0 given field as needed
     * @param string  $table table to update
     * @param integer $id    item id to be updated
     * @param string  $field field name to be updated
     * @param mixed $where
     * @return boolean
     */
    public function sql_set($table = '', $where = '', $field = '')
    {
        try {
            $_query = "
                UPDATE "._DB_PREFIX.'_'.trim($table)." 
                SET ".$field." = IF(".$field." = 1, 0, 1) 
            ";

            $values = [];

            if (!empty($where)) {
                if (is_array($where) && count($where) > 0) {
                    $where_fields = [];

                    foreach ($where as $key => $value) {
                        $where_fields[] = $key;
                        $values[] = $value;
                    }

                    $_query .= "
                        WHERE ".implode(' = ? AND ', $where_fields)." = ?
                    ";
                } elseif (is_numeric($where)) {
                    $values[] = (int) $where;

                    $_query .= "
                        WHERE id = ?
                    ";
                } elseif (is_string($where)) {
                    $_query .= "
                        WHERE ".$where."
                    ";
                }
            }

            return self::prepare($_query)->execute($values);
        } catch (PDOException $e) {
            self::log_database_error($e, $_query);
        }
    }

    /**
     * Sum to integral already in database
     * @param string  $table table to update
     * @param integer $id    item id to be updated
     * @param string  $field field name to be updated
     * @param mixed $where
     * @return boolean
     */
    public function sql_sum($table = '', $field = [], $where = '')
    {
        try {
            if (is_array($field) && count($field) > 0) {
                foreach ($field as $key => $value) {
                    $colunm = $key;
                    $colunm_values = $value;
                }
            }

            $_query = "
                UPDATE "._DB_PREFIX.'_'.trim($table)." 
                SET ".$colunm." = ".$colunm." + ".$colunm_values." 
            ";

            $values = [];

            if (!empty($where)) {
                if (is_array($where) && count($where) > 0) {
                    $where_fields = [];

                    foreach ($where as $key => $value) {
                        $where_fields[] = $key;
                        $values[] = $value;
                    }

                    $_query .= "
                        WHERE ".implode(' = ? AND ', $where_fields)." = ?
                    ";
                } elseif (is_numeric($where)) {
                    $values[] = (int) $where;

                    $_query .= "
                        WHERE id = ?
                    ";
                } elseif (is_string($where)) {
                    $_query .= "
                        WHERE ".$where."
                    ";
                }
            }
            
            return self::prepare($_query)->execute($values);
        } catch (PDOException $e) {
            self::log_database_error($e, $_query);
        }
    }

    /**
    * Subtract to integral already in database
    * @param string  $table table to update
    * @param integer $id    item id to be updated
    * @param string  $field field name to be updated
    * @param mixed $where
    * @return boolean
    */
    public function sql_substract($table = '', $field = [], $where = '')
    {
        try {
            if (is_array($field) && count($field) > 0) {
                foreach ($field as $key => $value) {
                    $colunm = $key;
                    $colunm_values = $value;
                }
            }

            $_query = "
                UPDATE "._DB_PREFIX.'_'.trim($table)." 
                SET ".$colunm." = ".$colunm." - ".$colunm_values." 
            ";

            $values = [];

            if (!empty($where)) {
                if (is_array($where) && count($where) > 0) {
                    $where_fields = [];

                    foreach ($where as $key => $value) {
                        $where_fields[] = $key;
                        $values[] = $value;
                    }

                    $_query .= "
                        WHERE ".implode(' = ? AND ', $where_fields)." = ?
                    ";
                } elseif (is_numeric($where)) {
                    $values[] = (int) $where;

                    $_query .= "
                        WHERE id = ?
                    ";
                } elseif (is_string($where)) {
                    $_query .= "
                        WHERE ".$where."
                    ";
                }
            }

            return self::prepare($_query)->execute($values);
        } catch (PDOException $e) {
            self::log_database_error($e, $_query);
        }
    }

    /**
     * Delete from database
     * @param  string $table table name to delete from
     * @param  array  $where array containing all applicable where statements
     * @param mixed $limit
     * @return boolean
     */
    public function sql_delete($table = '', $where = '', $limit = [])
    {
        try {
            $_query = "
                DELETE
                FROM "._DB_PREFIX.'_'.trim($table)." 
            ";

            if (!empty($where)) {
                if (is_array($where) && count($where) > 0) {
                    $where_fields = [];

                    foreach ($where as $key => $value) {
                        $where_fields[] = $key;
                        $values[] = $value;
                    }

                    $_query .= "
                        WHERE ".implode(' = ? AND ', $where_fields)." = ?
                    ";
                } elseif (is_numeric($where)) {
                    $values[] = (int) $where;

                    $_query .= "
                        WHERE id = ?
                    ";
                } elseif (is_string($where)) {
                    $_query .= "
                        WHERE ".$where."
                    ";
                }
            }

            if (is_array($limit) && count($limit) > 0) {
                foreach ($limit as $value) {
                    $limit_fields[] = $value;
                }

                $_query .= "
                    LIMIT ".implode(', ', $limit_fields)."
                ";
            }
        
            return self::prepare($_query)->execute($values);
        } catch (PDOException $e) {
            self::log_database_error($e, $_query);
        }
    }

    /**
     * Creates an empty row and return its newly created id
     *
     * @param string $table
     * @return int ID of the inserted row
     */
    public function sql_insert_empty($table = '')
    {
        if (!empty($table)) {
            try {
                $table = trim($table);

                $_query = "
                    INSERT INTO "._DB_PREFIX.'_'.$table."
                    (id) VALUES (NULL)
                ";

                self::prepare($_query)->execute($values);
                return self::sql_nextid();
            } catch (PDOException $e) {
                self::log_database_error($e, $_query);
            }
        }
    }

    /**
     * Quotes a string for use in a query
     *
     * @param string $input
     * @param int $parameter_type
     * @return string
     */
    public function quote($input, $parameter_type = 0)
    {
        return self::$PDOInstance->quote($input, $parameter_type);
    }

    
    /**
     * Set an attribute
     *
     * @param int $attribute
     * @param mixed $value
     * @return bool
     */
    public function set_attribute($attribute, $value)
    {
        return self::$PDOInstance->setAttribute($attribute, $value);
    }

    /**
     * Retrieve a database connection attribute
     *
     * @param int $attribute
     * @return mixed
     */
    public function get_attribute($attribute)
    {
        return self::$PDOInstance->getAttribute($attribute);
    }
}
