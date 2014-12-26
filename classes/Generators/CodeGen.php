<?php
	/**
	 *  Abstract class to specify what methods should have a
	 *  code generator class
	 */
abstract class CodeGen
{
	/**
	 * Constructor
	 * @param array $lang The language file for printing text
	 */
	abstract function __construct($lang);

	/**
     * Gets connections string
     *
     * @param string 	$user     (optional)
     * @param string 	$password (optional)
     * @return string 	code to connect to the database
     */	
	abstract public function getConnection($user='DB_USER', $password='DB_PASS');

	/**
     * Gets authentication via a Postgres user
     *
     * @return string code for authorization via pg user
     */
	abstract public function getLoginByDbUser();


	/**
     * This function generates code for security trough an 
     * username and password stored in the database
     *
     * @param  string   $schema         Schema to use
     * @param  string   $table          Table to use for inserting data
     * @param  string   $authUser       Name of the column where user is stored
     * @param  string   $authPassword   Name of the column where pass is stored
     * @return string Code for security through information at database 
     */
	abstract public function getLoginByDbTable($schema, $table, $authUser, $authPassword);


	/**
     * Prints a Foreign key's select
     *
     * @return string options of the fk values
     */
    abstract public function printFkOptions();

    /**
     * PHP code for loading a record from database,
     * it just need load a single line from the database
     * into the row variable
     * 
     * @param  string $sql Query for loading the object
     * @return string      code for loading the record
     */
    abstract public function getLoadRecord($sql);


    /**
     * PHP code for inserting information to the database
     * 
     * @param  string 	$schema 	Schema to use
     * @param  string 	$table 		Table to use for inserting data
     * @param  array 	$columns 	Columns to insert values
     * @param  string 	$clearVars 	Code for cleaning variables
     * @return string      			code for report information
     */
    abstract public function getCreateCode($schema, $table, $columns, $clearVars);


    /**
     * PHP code for reporting information from the database
     * 
     * @param  array 	$selects 	Name of the columns to query
     * @param  string 	$from 		Table name
     * @param  array 	$joins 		join associations
     * @return string      			code for report information
     */
    abstract public function getReportCode($selects, $from, $joins);

    /**
     * Function to get code for fetching results when querying
     * @return string 	Code for fetching stuff inside a row variable
     */
    abstract public function getFetchCode();


    /**
     * PHP code for updating information at the database
     * 
     * @param  string $sql 		Query for updating information
     * @param  string $columns 	Columns to update
     * @return string      		code for update the record
     */
    abstract public function getUpdateCode($sql, $columns);

    /**
     * Function to generate deletion code
     *
     * @param  string 	$schema schema to use
     * @param  string 	$table 	page's table
     * @param  string 	$pk 	primary key of the table
     * @return string 	html 	code for deletion
     */
    abstract public function getDeleteCode($schema, $table, $pk);

}
?>