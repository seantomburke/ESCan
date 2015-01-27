<?php

/** A PHP class to access MySQL database with convenient methods
 * in an object oriented way, and with a powerful debug system.
 * @version  1.0
 * @author   Modified by Sean Burke
 * @author   Modified by Sean Burke
 */
class DB
{
	/** Put this variable to true if you want ALL queries to be debugged by default:
	 */
	public $defaultDebug = false;

	/** INTERNAL: The start time, in miliseconds.
	 */
	public $mtStart;
	/** INTERNAL: The number of executed queries.
	 */
	public $nbQueries;
	/** INTERNAL: The last result ressource of a query().
	 */
	public $lastResult;

	public $lastResultArray;

	public $error;

	public $affected_rows;

	public $num_rows;
	
	/** Connect to a MySQL database to be able to use the methods below.
	 */
	function DB()
	{	
		$this->mtStart    = $this->getMicroTime();
		$this->nbQueries  = 0;
		$this->lastResult = NULL;
		include_once 'inc/setup/_config.php';
		mysqli_connect(DBSERVER,  DBUSERNAME, DBPASSWORD, DBDATABASE) or die('You must first provide your MySQL Credentials in the _config.php and then run install.php. Please read the README.md file before proceeding');
		new MYSQLI();
	}

	function real_escape_string($string)
	{
		return mysqli_real_escape_string($string);
	}

	/** Query the database.
	 * @param $query The query.
	 * @param $debug If true, it output the query and the resulting table.
	 * @return The result of the query, to use with fetchNextObject().
	 */
	function query($query, $debug = -1)
	{
		$this->nbQueries++;
		$this->lastResult = mysqli_query($query) or $this->debugAndDie($query);
		$this->debug($debug, $query, $this->lastResult);
		$this->affected_rows = mysqli_affected_rows();
		$this->num_rows = $this->numRows();
		return $this->lastResult;
	}
	/** Do the same as query() but do not return nor store result.\n
	 * Should be used for INSERT, UPDATE, DELETE...
	 * @param $query The query.
	 * @param $debug If true, it output the query and the resulting table.
	 */
	function execute($query, $debug = -1)
	{
		$this->nbQueries++;
		mysqli_query($query) or $this->debugAndDie($query);
		$this->debug($debug, $query);
		$this->affected_rows = mysqli_affected_rows();
		$this->num_rows = $this->numRows();
	}
	/** Convenient method for mysqli_fetch_object().
	 * @param $result The ressource returned by query(). If NULL, the last result returned by query() will be used.
	 * @return An object representing a data row.
	 */
	function fetchNextObject($result = NULL)
	{
		if ($result == NULL)
		$result = $this->lastResult;

		if ($result == NULL || mysqli_num_rows($result) < 1)
		return NULL;
		else
		return mysqli_fetch_object($result);
	}
	/** Get the number of rows of a query.
	 * @param $result The ressource returned by query(). If NULL, the last result returned by query() will be used.
	 * @return The number of rows of the query (0 or more).
	 */
	function numRows($result = NULL)
	{
		if ($result != NULL)
		return mysqli_num_rows($result);
		else
		{
			if($this->lastResult != NULL)
			return @mysqli_num_rows($this->lastResult);
			else
			return 0;
		}
	}
	/** Get the result of the query as an object. The query should return a unique row.\n
	 * Note: no need to add "LIMIT 1" at the end of your query because
	 * the method will add that (for optimisation purpose).
	 * @param $query The query.
	 * @param $debug If true, it output the query and the resulting row.
	 * @return An object representing a data row (or NULL if result is empty).
	 */
	function queryUniqueObject($query, $debug = -1)
	{
		$query = "$query LIMIT 1";

		$this->nbQueries++;
		$result = mysqli_query($query) or $this->debugAndDie($query);

		$this->debug($debug, $query, $result);
		$this->affected_rows = mysqli_affected_rows();
		$this->num_rows = $this->numRows();

		return mysqli_fetch_object($result);
	}
	/** Get the result of the query as value. The query should return a unique cell.\n
	 * Note: no need to add "LIMIT 1" at the end of your query because
	 * the method will add that (for optimisation purpose).
	 * @param $query The query.
	 * @param $debug If true, it output the query and the resulting value.
	 * @return A value representing a data cell (or NULL if result is empty).
	 */
	function queryUniqueValue($query, $debug = -1)
	{
		$query = "$query LIMIT 1";

		$this->nbQueries++;
		$result = mysqli_query($query) or $this->debugAndDie($query);
		$line = mysqli_fetch_row($result);
		$this->affected_rows = mysqli_affected_rows();
		$this->num_rows = $this->numRows();

		$this->debug($debug, $query, $result);

		return $line[0];
	}
	/** Get the maximum value of a column in a table, with a condition.
	 * @param $column The column where to compute the maximum.
	 * @param $table The table where to compute the maximum.
	 * @param $where The condition before to compute the maximum.
	 * @return The maximum value (or NULL if result is empty).
	 */
	function maxOf($column, $table, $where)
	{
		return $this->queryUniqueValue("SELECT MAX(`$column`) FROM `$table` WHERE $where");
	}
	/** Get the maximum value of a column in a table.
	 * @param $column The column where to compute the maximum.
	 * @param $table The table where to compute the maximum.
	 * @return The maximum value (or NULL if result is empty).
	 */
	function maxOfAll($column, $table)
	{
		return $this->queryUniqueValue("SELECT MAX(`$column`) FROM `$table`");
	}
	/** Get the count of rows in a table, with a condition.
	 * @param $table The table where to compute the number of rows.
	 * @param $where The condition before to compute the number or rows.
	 * @return The number of rows (0 or more).
	 */
	function countOf($table, $where = '', $debug = -1)
	{
		if($where == '')
			return $this->countOfAll($table);
		else
			return $this->queryUniqueValue("SELECT COUNT(*) FROM `$table` WHERE $where", $debug);
	}
	/** Get the count of rows in a table.
	 * @param $table The table where to compute the number of rows.
	 * @return The number of rows (0 or more).
	 */
	function countOfAll($table)
	{
		return $this->queryUniqueValue("SELECT COUNT(*) FROM `$table`");
	}
	/** Internal function to debug when MySQL encountered an error,
	 * even if debug is set to Off.
	 * @param $query The SQL query to echo before diying.
	 */
	function debugAndDie($query)
	{
		$this->debugQuery($query, "Error");
		die("<p style=\"margin: 2px;\">".mysqli_error()."</p></div>");
	}
	/** Internal function to debug a MySQL query.\n
	 * Show the query and output the resulting table if not NULL.
	 * @param $debug The parameter passed to query() functions. Can be boolean or -1 (default).
	 * @param $query The SQL query to debug.
	 * @param $result The resulting table of the query, if available.
	 */
	function debug($debug, $query, $result = NULL)
	{
		if ($debug === -1 && $this->defaultDebug === false)
		return;
		if ($debug === false)
		return;

		$reason = ($debug === -1 ? "Default Debug" : "Debug");
		$this->debugQuery($query, $reason);
		if ($result == NULL)
		echo "<p style=\"margin: 2px;\">Number of affected rows: ".mysqli_affected_rows()."</p></div>";
		else
		$this->debugResult($result);
	}
	/** Internal function to output a query for debug purpose.\n
	 * Should be followed by a call to debugResult() or an echo of "</div>".
	 * @param $query The SQL query to debug.
	 * @param $reason The reason why this function is called: "Default Debug", "Debug" or "Error".
	 */
	function debugQuery($query, $reason = "Debug")
	{
		$color = ($reason == "Error" ? "red" : "orange");
		echo "<div style=\"border: solid $color 1px; margin: 2px;\">".
           "<p style=\"margin: 0 0 2px 0; padding: 0; background-color: #DDF;\">".
           "<strong style=\"padding: 0 3px; background-color: $color; color: white;\">$reason:</strong> ".
           "<span style=\"font-family: monospace;\">".htmlentities($query)."</span></p>";
	}
	/** Internal function to output a table representing the result of a query, for debug purpose.\n
	 * Should be preceded by a call to debugQuery().
	 * @param $result The resulting table of the query.
	 */
	function debugResult($result)
	{
		echo "<table border=\"1\" style=\"margin: 2px;\">".
           "<thead style=\"font-size: 80%\">";
		$numFields = mysqli_num_fields($result);
		// BEGIN HEADER
		$tables    = array();
		$nbTables  = -1;
		$lastTable = "";
		$fields    = array();
		$nbFields  = -1;
		while ($column = mysqli_fetch_field($result)) {
			if ($column->table != $lastTable) {
				$nbTables++;
				$tables[$nbTables] = array("name" => $column->table, "count" => 1);
			} else
			$tables[$nbTables]["count"]++;
			$lastTable = $column->table;
			$nbFields++;
			$fields[$nbFields] = $column->name;
		}
		for ($i = 0; $i <= $nbTables; $i++)
		echo "<th colspan=".$tables[$i]["count"].">".$tables[$i]["name"]."</th>";
		echo "</thead>";
		echo "<thead style=\"font-size: 80%\">";
		for ($i = 0; $i <= $nbFields; $i++)
		echo "<th>".$fields[$i]."</th>";
		echo "</thead>";
		// END HEADER
		while ($row = mysqli_fetch_array($result)) {
			echo "<tr>";
			for ($i = 0; $i < $numFields; $i++)
			echo "<td>".htmlentities($row[$i])."</td>";
			echo "</tr>";
		}
		echo "</table></div>";
		$this->resetFetch($result);
	}
	/** Get how many time the script took from the begin of this object.
	 * @return The script execution time in seconds since the
	 * creation of this object.
	 */
	function getExecTime()
	{
		return round(($this->getMicroTime() - $this->mtStart) * 1000) / 1000;
	}
	/** Get the number of queries executed from the begin of this object.
	 * @return The number of queries executed on the database server since the
	 * creation of this object.
	 */
	function getQueriesCount()
	{
		return $this->nbQueries;
	}
	/** Go back to the first element of the result line.
	 * @param $result The resssource returned by a query() function.
	 */
	function resetFetch($result)
	{
		if (mysqli_num_rows($result) > 0)
		mysqli_data_seek($result, 0);
	}
	/** Get the id of the very last inserted row.
	 * @return The id of the very last inserted row (in any table).
	 */
	function lastInsertedId()
	{
		return mysqli_insert_id();
	}
	/** Close the connection with the database server.\n
	 * It's usually unneeded since PHP do it automatically at script end.
	 */
	function close()
	{
		mysqli_close();
	}

	/** Internal method to get the current time.
	 * @return The current time in seconds with microseconds (in float format).
	 */
	function getMicroTime()
	{
		list($msec, $sec) = explode(' ', microtime());
		return floor($sec / 1000) + $msec;
	}

	/** create array from result
	 *
	 */
	function resultToMakeArray($key, $value, $initial = ''){
		if($initial != '')
		{
			$res_array[0] = $initial;
		}
		$temp_array = $this->resultToArray();
		if(is_array($temp_array))
			foreach ($temp_array as $row) {
				$temp_key = $row[$key];
				$temp_value = $row[$value];
				$res_array[$temp_key] = $temp_value;
			}
		else {
			$this->error = 'Cannot make array';
			return false;
		}
		$this->lastResultArray = $res_array;
		return $this->lastResultArray;
	}
	
	function resultToArray(){
		for ($count=0; $row = @mysqli_fetch_array($this->lastResult); $count++)
		$res_array[$count] = $row;
			
		$this->lastResultArray = $res_array;
		return $this->lastResultArray;
	}
	function resultToSingleArray(){
		$this->lastResultArray = @mysqli_fetch_array($this->lastResult);
		return $this->lastResultArray;
	}

	function lastArray()
	{
		return $this->lastResultArray;
	}

	function getError(){
		return mysqli_error();
	}

	function isEmpty(){
		if($this->num_rows == 0)
		return true;
		else
		return false;
	}

} // class DB
?>
