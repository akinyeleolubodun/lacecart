<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Db;

/**
 * SQL class
 *
 * @category   Pop
 * @package    Pop_Db
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2015 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.0.0
 */
class Sql
{

    /**
     * Constant for MYSQL database type
     * @var int
     */
    const MYSQL = 'MYSQL';

    /**
     * Constant for Oracle database type
     * @var int
     */
    const ORACLE = 'ORACLE';

    /**
     * Constant for PGSQL database type
     * @var int
     */
    const PGSQL = 'PGSQL';

    /**
     * Constant for SQLITE database type
     * @var int
     */
    const SQLITE = 'SQLITE';

    /**
     * Constant for SQLSRV database type
     * @var int
     */
    const SQLSRV = 'SQLSRV';

    /**
     * Constant for backtick quote id type
     * @var int
     */
    const BACKTICK = 'BACKTICK';

    /**
     * Constant for bracket quote id type
     * @var string
     */
    const BRACKET = 'BRACKET';

    /**
     * Constant for double quote id type
     * @var string
     */
    const DOUBLE_QUOTE = 'DOUBLE_QUOTE';

    /**
     * Constant for double quote id type
     * @var string
     */
    const NO_QUOTE = 'NO_QUOTE';

    /**
     * Database object
     * @var Adapter\AdapterInterface
     */
    protected $db = null;

    /**
     * Database type
     * @var int
     */
    protected $dbType = null;

    /**
     * ID quote type
     * @var string
     */
    protected $quoteIdType = 'NO_QUOTE';

    /**
     * Current table
     * @var string
     */
    protected $table = null;

    /**
     * Alias name for sub-queries
     * @var string
     */
    protected $alias = null;

    /**
     * SQL clause object
     * @var Sql\AbstractSql
     */
    protected $clause = null;

    /**
     * SQL statement
     * @var string
     */
    protected $sql = null;

    /**
     * SQL placeholder
     * @var string
     */
    protected $placeholder = '?';

    /**
     * Constructor
     *
     * Instantiate the SQL object.
     *
     * @param  Adapter\AdapterInterface $db
     * @param  mixed                    $table
     * @param  string                   $alias
     * @return Sql
     */
    public function __construct(Adapter\AdapterInterface $db, $table = null, $alias = null)
    {
        $this->setDb($db);
        $this->setTable($table);
        $this->setAlias($alias);
    }

    /**
     * Set the database adapter object
     *
     * @param  Adapter\AdapterInterface $db
     * @return Sql
     */
    public function setDb(Adapter\AdapterInterface $db)
    {
        $this->db = $db;
        $adapter  = strtolower(get_class($db));

        if (strpos($adapter, 'mysql') !== false) {
            $this->dbType      = self::MYSQL;
            $this->quoteIdType = self::BACKTICK;
            $this->placeholder = '?';
        } else if (strpos($adapter, 'oracle') !== false) {
            $this->dbType      = self::ORACLE;
            $this->quoteIdType = self::DOUBLE_QUOTE;
            $this->placeholder = '?';
        } else if (strpos($adapter, 'pgsql') !== false) {
            $this->dbType      = self::PGSQL;
            $this->quoteIdType = self::DOUBLE_QUOTE;
            $this->placeholder = '$';
        } else if (strpos($adapter, 'sqlite') !== false) {
            $this->dbType      = self::SQLITE;
            $this->quoteIdType = self::DOUBLE_QUOTE;
            $this->placeholder = ':';
        } else if (strpos($adapter, 'sqlsrv') !== false) {
            $this->dbType      = self::SQLSRV;
            $this->quoteIdType = self::BRACKET;
            $this->placeholder = '?';
        }

        if ($this->db->isPdo()) {
            $this->placeholder = ':';
            if ($this->db->getDbtype() == 'sqlite') {
                $this->dbType      = self::SQLITE;
                $this->quoteIdType = self::DOUBLE_QUOTE;
            } else if ($this->db->getDbtype() == 'pgsql') {
                $this->dbType      = self::PGSQL;
                $this->quoteIdType = self::DOUBLE_QUOTE;
            } else if ($this->db->getDbtype() == 'mysql') {
                $this->dbType      = self::MYSQL;
                $this->quoteIdType = self::BACKTICK;
            }
        }

        return $this;
    }

    /**
     * Set the quote ID type
     *
     * @param  string $type
     * @return Sql
     */
    public function setQuoteId($type = Sql::NO_QUOTE)
    {
        $this->quoteIdType = $type;
        return $this;
    }

    /**
     * Set current table to operate on.
     *
     * @param  mixed $table
     * @return Sql
     */
    public function setTable($table = null)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set current table to operate on. (alias for setTable)
     *
     * @param  mixed $table
     * @return Sql
     */
    public function from($table = null)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set current table to operate on. (alias for setTable)
     *
     * @param  mixed $table
     * @return Sql
     */
    public function into($table = null)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set alias name
     *
     * @param  string $alias
     * @return Sql
     */
    public function setAlias($alias = null)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     * Determine if the Sql object has a table set
     *
     * @return boolean
     */
    public function hasTable()
    {
        return ($this->table != null);
    }

    /**
     * Determine if the Sql object has an alias name
     *
     * @return boolean
     */
    public function hasAlias()
    {
        return ($this->alias != null);
    }

    /**
     * Get the current database adapter object.
     *
     * @return Adapter\AdapterInterface
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Get the current database adapter object (alias method.)
     *
     * @return Adapter\AdapterInterface
     */
    public function db()
    {
        return $this->db;
    }

    /**
     * Get the current database type.
     *
     * @return int
     */
    public function getDbType()
    {
        return $this->dbType;
    }

    /**
     * Get the quote ID type
     *
     * @return int
     */
    public function getQuoteId()
    {
        return $this->quoteIdType;
    }

    /**
     * Get the current table.
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Get the alias name.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * Get the current SQL statement string.
     *
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Get the SQL placeholder.
     *
     * @return string
     */
    public function getPlaceholder()
    {
        return $this->placeholder;
    }

    /**
     * Quote the value with the quoted identifier
     *
     * @param  string $value
     * @return string
     */
    public function quoteId($value)
    {
        $quotedValue = null;
        $startQuote  = null;
        $endQuote    = null;

        switch ($this->quoteIdType) {
            case self::BACKTICK:
                $startQuote = '`';
                $endQuote   = '`';
                break;
            case self::BRACKET:
                $startQuote = '[';
                $endQuote   = ']';
                break;
            case self::DOUBLE_QUOTE:
                $startQuote = '"';
                $endQuote   = '"';
                break;
        }

        if (strpos($value, '.') !== false) {
            $valueAry = explode('.', $value);
            foreach ($valueAry as $key => $val) {
                $valueAry[$key] = $startQuote . $val . $endQuote;
            }
            $quotedValue = implode('.', $valueAry);
        } else {
            $quotedValue = $startQuote . $value . $endQuote;
        }

        return $quotedValue;
    }

    /**
     * Quote the value with single quotes
     *
     * @param  string $value
     * @return string
     */
    public function quote($value)
    {
        if (($value != '?') && (substr($value, 0, 1) != ':') &&
            (preg_match('/^\$\d*\d$/', $value) == 0) && (!is_int($value)) && (!is_float($value))) {
            $value = "'" . $this->db->escape($value) . "'";
        }
        return $value;
    }

    /**
     * Create a select statement
     *
     * @param  mixed $columns
     * @return Sql\Select
     */
    public function select($columns = null)
    {
        if ($columns instanceof Sql\Select) {
            $this->clause = $columns;
        }
        if ((null === $this->clause) || !($this->clause instanceof Sql\Select)) {
            $this->clause = new Sql\Select($this, $columns);
        }

        return $this->clause;
    }

    /**
     * Create a insert statement
     *
     * @param  mixed $columns
     * @throws Exception
     * @return Sql\Insert
     */
    public function insert($columns = null)
    {
        if ($columns instanceof Sql\Insert) {
            $this->clause = $columns;
        }
        if ((null === $this->clause) || !($this->clause instanceof Sql\Insert)) {
            if (null === $columns) {
                throw new Exception('Error: The columns parameter cannot be null for a new INSERT clause.');
            }
            $this->clause = new Sql\Insert($this, $columns);
        }

        return $this->clause;
    }

    /**
     * Create a update statement
     *
     * @param  mixed $columns
     * @throws Exception
     * @return Sql\Update
     */
    public function update($columns = null)
    {
        if ($columns instanceof Sql\Update) {
            $this->clause = $columns;
        }
        if ((null === $this->clause) || !($this->clause instanceof Sql\Update)) {
            if (null === $columns) {
                throw new Exception('Error: The columns parameter cannot be null for a new UPDATE clause.');
            }
            $this->clause = new Sql\Update($this, $columns);
        }

        return $this->clause;
    }

    /**
     * Create a delete statement
     *
     * @param  mixed $delete
     * @param  Sql\Delete
     * @return Sql\Update
     */
    public function delete($delete = null)
    {
        if (null !== $delete) {
            $this->clause = $delete;
        }
        if ((null === $this->clause) || !($this->clause instanceof Sql\Delete)) {
            $this->clause = new Sql\Delete($this);
        }

        return $this->clause;
    }

    /**
     * Render SQL string
     *
     * @param  boolean $ret
     * @throws Exception
     * @return mixed
     */
    public function render($ret = false)
    {
        if (null === $this->clause) {
            throw new Exception('Error: No SQL clause has been created yet.');
        }

        $this->sql = $this->clause->render();

        // Reset clause
        $this->clause = null;

        if ($ret) {
            return $this->sql;
        } else {
            echo $this->sql;
        }
    }

    /**
     * Return the SQL as a string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render(true);
    }

}
