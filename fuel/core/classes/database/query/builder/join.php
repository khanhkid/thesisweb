<?php
/**
 * Part of the Fuel framework.
 *
 * @package    Fuel
 * @version    1.8
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
 * @copyright  2008 - 2009 Kohana Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Core;

class Database_Query_Builder_Join extends \Database_Query_Builder
{
	/**
	 * @var string  $_type  join type
	 */
	protected $_type;

	/**
	 * @var string  $_table  join table
	 */
	protected $_table;

	/**
	 * @var array  $_on  ON clauses
	 */
	protected $_on = array();

	/**
	 * Creates a new JOIN statement for a table. Optionally, the type of JOIN
	 * can be specified as the second parameter.
	 *
	 * @param   mixed  $table column name or array($column, $alias) or object
	 * @param   string $type  type of JOIN: INNER, RIGHT, LEFT, etc
	 */
	public function __construct($table, $type = null)
	{
		// Set the table to JOIN on
		$this->_table = $table;

		if ($type !== null)
		{
			// Set the JOIN type
			$this->_type = (string) $type;
		}
	}

	/**
	 * Adds a new OR condition for joining.
	 *
	 * @param   mixed   $c1  column name or array($column, $alias) or object
	 * @param   string  $op  logic operator
	 * @param   mixed   $c2  column name or array($column, $alias) or object
	 *
	 * @return  $this
	 */
	public function or_on($c1, $op, $c2)
	{
		$this->_on[] = array($c1, $op, $c2, 'OR');

		return $this;
	}

	/**
	 * Adds a new AND condition for joining.
	 *
	 * @param   mixed   $c1  column name or array($column, $alias) or object
	 * @param   string  $op  logic operator
	 * @param   mixed   $c2  column name or array($column, $alias) or object
	 *
	 * @return  $this
	 */
	public function on($c1, $op, $c2)
	{
		$this->_on[] = array($c1, $op, $c2, 'AND');

		return $this;
	}

	/**
	 * Adds a new AND condition for joining.
	 *
	 * @param   mixed   $c1  column name or array($column, $alias) or object
	 * @param   string  $op  logic operator
	 * @param   mixed   $c2  column name or array($column, $alias) or object
	 *
	 * @return  $this
	 */
	public function and_on($c1, $op, $c2)
	{
		return $this->on($c1, $op, $c2);
	}

	/**
	 * Compile the SQL partial for a JOIN statement and return it.
	 *
	 * @param   mixed  $db  Database_Connection instance or instance name
	 *
	 * @return  string
	 */
	public function compile($db = null)
	{
		if ( ! $db instanceof \Database_Connection)
		{
			// Get the database instance
			$db = \Database_Connection::instance($db);
		}

		if ($this->_type)
		{
			$sql = strtoupper($this->_type).' JOIN';
		}
		else
		{
			$sql = 'JOIN';
		}

		// Quote the table name that is being joined
		$sql .= ' '.$db->quote_table($this->_table);

		$conditions = array();

		foreach ($this->_on as $condition)
		{
			// Split the condition
			list($c1, $op, $c2, $chaining) = $condition;

			// Add chain type
			$conditions[] = ' '.$chaining.' ';

			if ($op)
			{
				// Make the operator uppercase and spaced
				$op = ' '.strtoupper($op);
			}

			// Quote each of the identifiers used for the condition
			$conditions[] = $db->quote_identifier($c1).$op.' '.(is_null($c2) ? 'NULL' : $db->quote_identifier($c2));
		}

		// remove the first chain type
		array_shift($conditions);

		// if there are conditions, concat the conditions "... AND ..." and glue them on...
		empty($conditions) or $sql .= ' ON ('.implode('', $conditions).')';

		return $sql;
	}

	/**
	 * Resets the join values.
	 *
	 * @return  $this
	 */
	public function reset()
	{
		$this->_type =
		$this->_table = NULL;
		$this->_on = array();
	}
}
