<?php

class Page {
    /*     * * Attributes: ** */

    private $app_id;
    private $page_id;
    private $page_filename;
    private $date_created;
    private $in_main_menu;
    private $table;
    public $page_title;
    public $page_text;
    public $descr;
    public $operation;
    public $fields = array();

    public function _construct() {
        $this->descr = '';
        $this->page_text = '';
    }

    /**
     * This function adds a new field object to $fields_array
     *
     * @param $page the field/column to be added to this page object
     * @access public
     */
    public function addField(Columns $page) {
        $this->fields[] = $page;
    }

    /**
     * Count how many fields are going to be showed in this page
     * @return int with number of fields to show
     */
    public function countShowFields() {
        $num = 0;
        foreach ($this->fields as $field) {
            if ($field->isOnPage())
                $num++;
        }

        return $num;
    }

    /**
     * This function sorts this page's fields by its order
     * @return bool if operation was a success
     */
    public function sortFields() {
        $index = array();
        foreach ($this->fields as $field) {
            $index[] = $field->getOrder();
        }
        return array_multisort($index, $this->fields, 0);
    }

    /**
     * This function sets this objects id
     * @param $id The id stored in the DB
     */
    public function setId($id) {
        $this->page_id = $id;
    }

    /**
     * This function inserts a description in this object
     * @param $description the string with description content
     */
    public function setDescription($description) {
        $this->descr = $description;
    }

    /**
     * Gets the date when this page was created
     * @return string with page's created date
     */
    public function getCreated() {
        return $this->date_created;
    }

    /**
     * This functions creates an array with all column's name for this page
     * @return array of string with column's name from this page
     */
    public function getFieldsName() {
        $names = array();
        $tables = 0;

        foreach ($this->fields as $field){
            if($field->isFK()){
                $remote_column = 'a'. $tables .'.'.$field->getRemoteField();
                $names[$remote_column] = $field->getDisplayName();
                $tables++;
            }
            else
                $names['a.'. $field->getName()] = $field->getDisplayName();
        }
            
        
        return $names;
    }

    /**
     * Returns the operation of this page
     *
     * @return string of this page's operation
     */
    public function getFilename() {
        return $this->page_filename;
    }

    /**
     * Sets this page's filename
     * @param $name string with name of desired page's filename
     */
    public function setFilename($name) {
        $this->page_filename = $name;
    }

    /**
     * Sets this page's main database operation
     * @param $op operation
     */
    public function setOperation($op) {
        $this->operation = $op;
    }

    /**
     * Returns this page's database id
     * @return int with current page's database id
     */
    public function getId() {
        return $this->page_id;
    }

    /**
     * Sets this page text, this text will be show inside the page
     * @param $page_text desired text to show in the page
     */
    public function setPageText($page_text) {
        $this->page_text = $page_text;
    }

    /**
     * Returns the name of working database table
     *
     * @return string of this working database table
     */
    public function getTable() {
        return $this->table;
    }

    /**
     * Sets this page's working table
     * @param $name string with table's name
     */
    public function setTable($name) {
        $this->table = $name;
    }

    /**
     * Sets this page's title
     * @param $title text with page's title
     */
    public function setTitle($title) {
        $this->page_title = $title;
    }

    /**
     * This functions returns if this page should appear on application's main menu
     *
     * @return bool  showing if page is on main menu;
     */
    public function inMainMenu() {
        return $this->in_main_menu == 't' ?  true : false;
    }

    /**
     * Returns if a page should be show in the main menu for storing in the DB
     * @return string of $this->in_main_menu
     */
    public function inMainMenuAsString() {
        switch ($this->in_main_menu) {
            case "f": return "false";
            case "t": return "true";
        }
    }

    /**
     * Set if this page should appear in the application's main menu
     *
     * @param $value boolean value if it should appear or not
     */
    public function setInMainMenu($value) {
        $this->in_main_menu = $value ? "t" : "f";
    }

    /**
     * This function adds into the DB all fields from this page
     *
     * @param $tables_id wich stores this page's table databases id
     */
    public function saveColumns($table_id) {
        foreach ($this->fields as $column)
            $column->save($table_id);
    }

    /**
     * This function stores the table information at the DB,
     *
     * @return nothing
     * @access public
     */
    public function saveTable($page_id) {
        global $misc;

        // Creates a new database access object.
        $driver = $misc->getDatabaseAccessor("phppgadmin");

        $sql = sprintf("INSERT INTO crudgen.page_tables (table_name, pages_page_id) "
                . " VALUES ('%s',%d) RETURNING page_tables_id", $this->table, $page_id);

        return $driver->selectField($sql, "page_tables_id");
    }

    /**
     * This function stores this page into the DB,
     *
     * @return Inserter page's id
     * @access public
     */
    public function insert($app_id) {
        global $misc;

        // Creates a new database access object.
        $driver = $misc->getDatabaseAccessor("phppgadmin");

        $sql = sprintf("INSERT INTO crudgen.pages (page_filename, page_title, operation,page_text,app_id) "
                . "VALUES ('%s','%s','" . substr($this->operation, 0, 1) . "','%s', %d) RETURNING page_id", $this->page_filename, $this->page_title, $this->page_text, $app_id);

        $this->page_id = $driver->selectField($sql, "page_id");
        return $this->page_id;
    }

    /**
     * Loads a page from the database to this object, including table and fields/columns information
     *
     * @param $page_id the desired page's database id
     */
    public function load($page_id) {
        global $misc;

        $driver = $misc->getDatabaseAccessor("phppgadmin");
        $sql = sprintf("SELECT page_filename, page_title,descr, date_created,operation,in_main_menu,page_text,app_id "
                . "FROM crudgen.pages WHERE page_id=%d", $page_id);
        $rs = $driver->selectSet($sql);

        //Saves page related info in this object
        $this->page_id = $page_id;
        $this->app_id = $rs->fields['app_id'];
        $this->page_filename = $rs->fields['page_filename'];
        $this->page_title = $rs->fields['page_title'];
        $this->operation = $rs->fields['operation'];
        $this->descr = $rs->fields['descr'];
        $this->date_created = $rs->fields['date_created'];
        $this->in_main_menu = $rs->fields['in_main_menu'];
        $this->page_text = $rs->fields['page_text'];


        //fix operation variable 'cause DB only stores first character
        switch ($this->operation) {
            case "c":
                $this->operation = "create";
                break;
            case "r":
                $this->operation = "report";
                break;
            case "u":
                $this->operation = "update";
                break;
            case "d":
                $this->operation = "delete";
                break;
        }

        //Retreives table's name
        $sql = sprintf("SELECT page_tables_id, table_name FROM crudgen.page_tables WHERE pages_page_id=%d", $page_id);
        $rs = $driver->selectSet($sql);
        $this->table = $rs->fields['table_name'];
        $table_id = $rs->fields['page_tables_id'];

        //Loads all fields from this page
        $objField = new Columns();
        $this->fields = $objField->load($table_id);
    }

    /**
     * This functions updates this object information in the database
     * @return  0 if everything went ok
     * @return -1 if something went wrong
     */
    public function update() {
        global $misc;

        // Creates a new database access object.
        $driver = $misc->getDatabaseAccessor("phppgadmin");
        $sql = sprintf("UPDATE crudgen.pages SET page_filename='%s',page_title='%s', page_text='%s',descr='%s',"
                . "in_main_menu=" . $this->inMainMenuAsString() . " WHERE page_id = %d", $this->page_filename, $this->page_title, $this->page_text, $this->descr, $this->page_id);

        return $driver->execute($sql);
    }

    /**
     * This functions deletes this object information in the database
     * @return  0 if everything went ok
     * @return -1 if something went wrong
     */
    public static function delete($page_id) {
        global $misc;

        // Creates a new database access object.
        $driver = $misc->getDatabaseAccessor("phppgadmin");
        $sql = sprintf("DELETE FROM crudgen.pages WHERE page_id=%d", $page_id);
        return $driver->execute($sql);
    }

    /**
     * Function to store this page object into the database,
     * including fields and table information
     *
     * @access public
     */
    public function save() {
        $rs = false;
        //first saves app data
        if (empty($this->page_id)) {
            $rs = $this->insert();
            if ($rs < 1)
                return false;
            else
                $this->app_id = $rs;
        }
        else {
            $rs = $this->update();
            return $rs;
        }
    }

    /*
     * Validates if this object's attributes have correct values to let this object
     * be stored at the database
     * @return string the name of the wrong attribute
     * @return true if everything is ok
     */

    public function validate($lang) {
        if (empty($this->page_title))
            return $lang['strnopagetitle'];
        if (empty($this->page_filename))
            return $lang['strnopagefilename'];

        //Checks if page's filename doesn't have extension
        if (substr($this->page_filename, -4) != ".php")
            $this->page_filename .= '.php';

        return true;
    }

    /**
     * Returns an array of Pages
     * @return Page array with all pages
     */
    public static function getPages() {
        $genpages = array();

        foreach ($this->pages as $page)
            $genpages[] = $page;

        return $genpages;
    }

    /**
     * Returns all this applications pages results
     * @return result from database query
     */
    public static function getApplicationPages($app_id, $lang) {
        global $misc;

        $driver = $misc->getDatabaseAccessor("phppgadmin");
        $sql = "SELECT page_id,page_title,date_created,page_filename,"
                . "CASE WHEN operation='d' THEN '{$lang['strdelete']}' WHEN operation='c' THEN '{$lang['strcreate']}' "
                . "WHEN operation='u' THEN '{$lang['strupdate']}' ELSE '{$lang['strreport']}' END AS operation "
                . "FROM crudgen.pages WHERE app_id={$app_id} ORDER BY operation";
        $rs = $driver->selectSet($sql);

        return $rs;
    }

    /**
     * Builds POST array used in forms 
     */
    public function buildPost() {
        if (!isset($_POST['page_title']))
            $_POST['page_title'] = $this->page_title;

        if (!isset($_POST['page_filename']))
            $_POST['page_filename'] = $this->page_filename;

        if (!isset($_POST['on_main_menu']))
            $_POST['on_main_menu'] = $this->inMainMenu() ? "selected" : null;

        if (!isset($_POST["page_descr"]))
            $_POST["page_descr"] = $this->descr;

        if (!isset($_POST["page_text"]))
            $_POST["page_text"] = $this->page_text;
    }
}

?>
