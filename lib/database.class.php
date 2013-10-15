<?php

class Database {

	// Prefix
	public static $prefix;

	// Database handler
	private $db;

	// Query handler
	private $qHandler;

	public function __construct($server, $dbname, $username, $password, $charset, $prefix)	{

		// Set prefix
		self::$prefix = $prefix;

		// Set DNS
		$dns = 'mysql:dbname='.$dbname.';host='. $server .';charset='.$charset.'';

		// Set options 
		$options = array(
            PDO::ATTR_PERSISTENT    => true,
            PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION
        );

		// Try establish db connection
		try {
			$this->db = new PDO($dns, $username, $password, $options);
		}

		// If error occurs save if to errors
		// TODO: WHAT HAPPENS THAN???
		catch (PDOException $e)	{
			web::$errors['db'] = $e->getMessage();
		}

	}

	/* Function to any kind of query
	 * @param $query query to database
	 */
	public function query($query) {
		$this->qHandler = $this->db->prepare($query);
	}

	/* Function to bind params to query
	 * @param $param name of param to bind
	 * @param $value value of param to bind
	 * @param $type specific type of param
	 */
	public function bind($param, $value, $type = null) {

		// If $type param wasn't defined
		if (is_null($type)) {
			switch(true) {
				
				// Set INT type
				case is_int($value):
					$type = PDO::PARAM_INT;
					break;

				// Set BOOL type 
				case is_bool($value):
					$type = PDO::PARAM_BOOL;
					break;

				// Set NULL type
				case is_null($value):
					$type = PDO::PARAM_NULL;
					break;

				// Default set STRING type
				default:
					$type = PDO::PARAM_STR;
					break;
			}
		}

		// Finally bind param to query
		$this->qHandler->bindValue($param, $value, $type);
	}

	/* Function to execute query
	 * @return result of execution
	 */
	public function execute() {
		return $this->qHandler->execute();
	}

	/* Function to get results
	 * @return array of results
	 */
	public function resultset() {
		$this->execute();
		return $this->qHandler->fetchAll(PDO::FETCH_ASSOC);
	}

	/* Function to get single result
	 * @return single result
	 */
	public function single() {
		$this->execute();
		return $this->qHandler->fetch(PDO::FETCH_ASSOC);
	}
	
	/* Function to get count of rows
	 * @return rows count
	 */
	public function rowCount() {
		return $this->qHandler->rowCount();
	}

	/* Function to get last insert id
	 * @return last insert id
	 */
	public function lastInsertid() {
		return $this->db->lastInsertid();
	}

	/* Function to start multiple transactions
	 * @return result of begin of transaction
	 */
	public function beginTransaction() {
		return $this->db->beginTransaction();
	}

	/* Function to commit transaction data to server
	 * @return result of commit
	 */
	public function endTransaction() {
		return $this->db->commit();
	}

	/* Function to make rollback
	 * @return result of rollback
	 */
	public function cancelTransaction() {
		return $this->db->rollBack();
	}

	/* Function to get info about prepared params
	 * @return result of debugging function
	 */
	public function debugDumpParams() {
		return $this->qHandler->debugDumpParams();
	}

}

?>