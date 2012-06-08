<?php

class Columns
{
	/*** Attributes: ***/
	private $id;

	private $name;

	private $order;

	private $on_page = true;

	private $display_name;

	private $remote_table;

	private $remote_field;
	/**
	 * This is the class constructor, initializes some variables
	 */
	function Columns(){
		$this->order = -1;
		$this->on_page = true;
		$this->display_name="";
		$this->remote_table = "";
		$this->remote_field = "";
	}
	/**
	 * Function wich returns the foreign table of this field (if exists)
	 * @param $db name of database where the data is stored
	 * @param $schema current column's schema
	 * @param $table current column's table
	 * @return name of foreing table
	 * @return -1 if no table where detected
	 */
	function getFkTables($db,$schema,$table){
		global $misc;
		$driver = $misc->getDatabaseAccessor($db);
		
		$sql ="SELECT cc.table_name FROM information_schema.table_constraints tc,"
			."information_schema.constraint_column_usage cc, information_schema.key_column_usage kc"
			." WHERE tc.constraint_type='FOREIGN KEY' AND tc.table_schema='{$schema}'"
			." AND tc.table_name='{$table}' AND tc.constraint_name=cc.constraint_name"
			." AND kc.constraint_name = cc.constraint_name AND kc.table_schema='{$schema}'"
			." AND kc.table_name='{$table}' AND kc.column_name='{$this->name}'";
		
		return $driver->selectField($sql,"table_name");
	}
	/**
	 * Returns this field's db id
	 * @return int with this db's id
	 */
	function getId(){
		return $this->id;
	}
	/**
	 * Sets this field's database id
	 * @param $id database's id
	 */
	function setId($id){
		$this->id = $id;
	}
	/**
	 * Gets the display name of this field
	 * @return string with display name
	 */
	function getDisplayName(){
		return $this->display_name;
	}
	/**
	 * Sets the name that will be displayed for this field in the page
	 * @param $name name to be displayed in the page
	 */
	function setDisplayName($name){
		$this->display_name =$name;
	}
	/**
	 * Get this field name from the db
	 * @return string with this db's name
	 */
	function getName(){
		return $this->name;
	}
	/**
	 * Sets this field db's name
	 * @param $name name of this field in the database
	 */
	function setName($name){
		$this->name=$name;
	}
	/**
	 * Gets this field order, this is the order that will this field will
	 * be showed in the page
	 * @return int with this field order
	 */
	function getOrder(){
		return $this->order;
	}
	/**
	 * Set this field's display order for the page
	 * @param $order order of this field in the page
	 */
	function setOrder($order){
		$this->order=$order;
	}
	/**
	 * If this field is a foreing key, returns foreing column name
	 * @return string with foreing's column name
	 */
	function getRemoteField(){
		return $this->remote_field;
	}
	/**
	 * If this field is a fk, set foreign field
	 * @param $field foreign's field name
	 */
	function setRemoteField($field){
		$this->remote_field = $field;
	}
	/**
	 * If this field is a foreing key, returns foreing table
	 * @return string with foreing's table
	 */
	function getRemoteTable(){
		return $this->remote_table;
	}
	/**
	 * If this field is a fk sets foreign table
	 * @param $table table wich points this fk
	 */
	function setRemoteTable($table){
		$this->remote_table = $table;
	}
	/**
	 * Checks if this fields is a foreign key
	 * @return true if this field is a fk
	 * @return false means this field is not a fk
	 */
	function isFK(){
		return $this->remote_field != '' ;
	}
	/**
	 * Checks if this field should be displayed in the page
	 * @return bool if this field is displayed
	 */
	function isOnPage(){
            return $this->on_page != 'f';
	}

	/**
	 * Returns if a fields is on a page for storing in the DB
	 * @return string of $this->on_page
	 */
	function isOnPageAsString(){
		return $this->on_page ? 'true' : 'false';
	}

	/**
	 * Sets if this field should appear in the page
	 * @param $value bool of this field appearence
	 */
	function setOnPage($value){
		$this->on_page = $value;
	}


	/**
	 * This function stores a field in the DB,
	 *
	 * @return less than 0 means a problem inserting
	 * @access public
	 */
	public function save($table_id){
		global $misc;

		// Creates a new database access object.
		$driver = $misc->getDatabaseAccessor("phppgadmin");
		$sql = "INSERT INTO crudgen.page_columns (column_name, page_order,on_page,display_name, remote_table,remote_column,page_tables_id) "
		."VALUES ('{$this->name}',{$this->order},".$this->isOnPageAsString().",'{$this->display_name}','{$this->remote_table}','{$this->remote_field}',{$table_id})";

		$rs = $driver->execute($sql);

		return $rs;
	}
	/**
	 * Function to load all columns of a specific working table
	 * @param $array an array to store each field object loaded from DB
	 * @param $table_id table's database id
	 * @return nothing, all fields are stored in the $array array
	 */
	function load($table_id){
		global $misc;
        $array= array();
        
		// Creates a new database access object.
		$driver = $misc->getDatabaseAccessor("phppgadmin");
		$sql = "SELECT page_column_id,column_name, page_order, on_page, display_name, remote_table,remote_column FROM"
		." crudgen.page_columns WHERE page_tables_id={$table_id} ORDER BY page_order ASC";

		$rs = $driver->selectSet($sql);

		while(!$rs->EOF){
			$tmpfield = new Columns();
			$tmpfield->id = $rs->fields['page_column_id'];
			$tmpfield->name = $rs->fields['column_name'];
			$tmpfield->order =$rs->fields['page_order'];
			$tmpfield->on_page = $rs->fields['on_page'];
			$tmpfield->display_name = $rs->fields['display_name'];
			$tmpfield->remote_table= $rs->fields['remote_table'];
			$tmpfield->remote_field=$rs->fields['remote_column'];
			$array[] = $tmpfield;
			$rs->moveNext();
		}

        return $array;
	}
	/**
	 * Updates this object's information stored in the database
	 */
	public function update(){
		global $misc;

		// Creates a new database access object.
		$driver = $misc->getDatabaseAccessor("phppgadmin");
		$sql = "UPDATE crudgen.page_columns SET page_order={$this->order},on_page=".$this->isOnPageAsString().",display_name='{$this->display_name}',remote_table='{$this->remote_table}',"
		."remote_column='{$this->remote_field}' WHERE page_column_id={$this->id}";
		$driver->execute($sql);
	}

}
?>
