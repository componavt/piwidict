<?php namespace piwidict\sql;

use mysqli;

class DB extends mysqli {
	private $result;
	private $row;
	private $row_count;
	public $error;

	/* Connection to database */
	public function __construct($dbhost, $dbuser, $dbpass, $dbname) {
            // print "\n<BR> DB:new($dbhost, $dbuser, $dbpass, $dbname);";
            @parent::__construct(
                $dbhost,
                $dbuser, 
                $dbpass,
                $dbname
            );
            if ($this->connect_error) {
    		printf("Connect failed: %s\n", $this->connect_error);
    		exit();
	    }

	    if (!$this->set_charset("binary")) {
    		printf("Error loading character set binary: %s\n", $this->error);
	    } 
	}

	/** Outdated (use query_err): Executes database query.
         * query_err($query, __FILE__, __LINE__, __METHOD__);
         * @param string $q SQL request
         * @param string $err_string output if request failed
         * @return link on result table of request to DB
         */
	public function query_e(string $q, string $err_string='')  {
	    $this->result = @parent::query($q);

	    if (!$this->result)	{
                if (!$err_string) 
                    $err_string = "SQL error: <b>$q</b>";

		die("<div style=\"color:#666666;\">$err_string</div>: ".$this->error." ($q)"); // 
	    }
	    return $this->result;
	}

	/** Executes database query and finished with message about error in
         * $line of $file in $method of some class.
         * @param string $q SQL-request
         * @param string $file filename which calls this method
         * @param string $line line in filename which calls this method
         * @param string $method method which calls this method
         * @return link on result table of request to DB
         */
	public function query_err(string $q, 
                                  string $file='', 
                                  string $line='', 
                                  string $method='')  
        {
	    $this->result = @parent::query($q);

	    if ($this->result)	{
                return $this->result;
            }
                
	    if (!$file) {
		$err_string = "SQL error: <b>$q</b>";
	    } else {
                $err_string = "Query failed";
                if ($method) {
                    $err_string .= "in $method";
                }
                $err_string .= " in file <b>$file</b>";
                if ($line) {
                    $err_string .= ", string <b>$line</b>";
                }
            }
            
            die("<div style=\"color:#666666;\">$err_string</div>"); // : ".$this->error." ($q)
	}

	/* Determine number of rows result set */
	public function query_count($result) {
	    $this->row_count = $result->num_rows;
	    return $this->row_count;
	}
/*
	// Transform an entire query in a two-dimensional array 
	public function fetch_all($result) {
	    $rows = array();	
	    while ($fetch = $result->fetch_assoc()) {
		$rows[]=$fetch;
	    }
	    return $rows;
	}

	// Delete a record from $table by $id_field = $id 
	public function delete_record($table, $id_field, $id) {
		$q="DELETE FROM $table WHERE $id_field='$id'";
		$delete_record=$this->query($q);
		return $delete_record;
	}

	public function delete_set($table, $set) {
		$q="DELETE FROM $table WHERE $set";
		echo "<div style=\"font-size:10px; color:#CCCCCC;\">".$q."</div>";
		$delete_record = $this->query($q);
		return $delete_record;
	}

	// Insert set of data $set in table $table 
	public function insert_record($table, $set) {
		$q="INSERT INTO $table SET $set";
		$this->query($q);
	}

	public function update_record($table, $set, $id_field, $id) {
		$q="UPDATE $table SET $set WHERE $id_field='$id'";
		$this->query($q);
	}

	public function get_last_id($table) {
		$q="SELECT LAST_INSERT_ID() as last_id FROM $table";
		$last = $this->fetch_assoc($this->query($q));
		return $last['last_id'];
	}
*/
}
?>