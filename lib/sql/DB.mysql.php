<?php
class DB {
	protected $dbname = NAME_DB;
	protected $dbuser = 'javawiki';
	protected $dbhost = 'localhost';
	protected $dbpass = '';

	private $_connect;
	private $db_select;
	public $row;

	/* Подключение к базе данных */
	public function __construct() {
	  $this->_connect=@mysql_connect ($this->dbhost, $this->dbuser, $this->dbpass);
	  if (!$this->_connect) {
   		exit ("В настоящий момент сервер базы данных недоступен");
   	  }
	  mysql_query('set names binary');
	  $this->db_select=mysql_select_db ($this->dbname, $this->_connect);
	  if (!$this->db_select) {
     		exit('В настоящий момент база данных недоступна');
      	  }
	  return $this->db_select;
	}

	/* Запрос к базе данных */
	public function query($q,$war_string) {
		if (!$war_string) $war_string = "SQL error: <b>$q</b>";
		$this->result=mysql_query($q, $this->_connect);
//print mysql_num_rows($this->result);
		if (!$this->result)	{
			die("<div style=\"font-size:10px; color:#666666;\">$war_string: ".mysql_error()."</div>");
		}
		return $this->result;
	}

	/* Количество строк в запросе */
	public function query_count($result) {
		$this->row_count=mysql_num_rows($this->result);
		return $this->row_count;
	}

	/* Перевести строку в ассоциативный массив */
	public function fetch_assoc($result) {
		$this->fetch=mysql_fetch_assoc($result);
	  	if (!$this->fetch)
			return NULL;
		return $this->fetch;
	}


	public function fetch_object($result) {
		$this->fetch=mysql_fetch_object($result);
	  	if (!$this->fetch)
			return NULL;
		return $this->fetch;
	}

	/* Перевести весь запрос в ассоциативный массив */
	public function fetch_all($result) {
		while ($fetch=mysql_fetch_assoc($result)) {
			$rows[]=$fetch;
		}
		return $rows;
	}

	public function transform_date($dat) {
		$data['day']=substr($dat, 8, 2);
		$data['month']=substr($dat, 5, 2);
		$data['year']=substr($dat, 0, 4);
		return $data;
	}

	/* Удалить запись из таблицы $table по полю $id_field равному $id */
	public function delete_record($table, $id_field, $id) {
		$q="DELETE FROM $table WHERE $id_field='$id'";
		$delete_record=$this->query($q);
		return $delete_record;
	}

	public function delete_set($table, $set) {
		$q="DELETE FROM $table WHERE $set";
		echo "<div style=\"font-size:10px; color:#CCCCCC;\">".$q."</div>";
		$delete_record=$this->query($q);
		return $delete_record;
	}

	/* Вставить набор данных $set в таблицу $table */
	public function insert_record($table, $set) {
		$q="INSERT INTO $table SET $set";
		//echo "<div style=\"font-size:10px; color:#CCCCCC;\">".$q."</div>";
		$insert_record=$this->query($q);
	}

	public function update_record($table, $set, $id_field, $id) {
		$q="UPDATE $table SET $set WHERE $id_field='$id'";
		//echo "<div style=\"font-size:10px; color:#CCCCCC;\">".$q."</div>";
		$update_record=$this->query($q);
	}

	public function get_last_id($table) {
		$q="SELECT LAST_INSERT_ID() FROM $table";
		$last=$this->fetch_assoc($this->query($q));
		$last_id=$last['LAST_INSERT_ID()'];
		return $last_id;
	}
}
?>