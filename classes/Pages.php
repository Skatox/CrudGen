<?php

class Pages {
    /*     * * Attributes: ** */

    private $page_id;
    private $page_filename;
    private $page_title;
    private $operation;
    private $descr;
    private $completed = false;
    private $date_created;
    private $in_main_menu;
    private $table;
    private $page_text;
    public $fields = array();

    public function _construct() {
        global $lang;
        $this->page_title = $lang['strnone'];
        $this->completed = false;
    }

    /**
     * This function adds a new field object to $fields_array
     *
     * @param $page the field/column to be added to this page object
     * @access public
     */
    public function addField(Fields $page) {
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
                $num = $num + 1;
        }

        return $num;
    }

    /**
     * This function sorts this page's fields by its order
     * @return bool if operation was a success
     */
    public function sortFieldsByOrder() {
        $index = array();
        foreach ($this->fields as $field) {
            $index[] = $field->getOrder();
        }
        return array_multisort($index, $this->fields, 0);
    }

    /**
     * Returns this page's description
     *
     * @return string this page's description
     */
    public function getDescription() {
        return $this->descr;
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
        $field_names = array();
        foreach ($this->fields as $field) {
            $field_names[] = $field->getName();
        }
        return $field_names;
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
     * Returns the operation of this page
     * @return string of this page's operation
     */
    public function getOperation() {
        return $this->operation;
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
     * Returns the text of this page, this text describe the process or the info in this page
     * wich is entered by the application's owner
     *
     * @return string of text of this page
     */
    public function getPageText() {
        return $this->page_text;
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
     * Gets this page's title
     * @return string with this page's title
     */
    public function getTitle() {
        return $this->page_title;
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
    public function isInMainMenu() {
        switch ($this->in_main_menu) {
            case "f": return false;
            case "t": return true;
        }
    }

    /**
     * Returns if a page should be show in the main menu for storing in the DB
     * @return string of $this->in_main_menu
     */
    public function isInMainMenuAsString() {
        switch ($this->in_main_menu) {
            case "f": return "false";
            case "t": return "true";
        }
    }

    /**
     * Checks if this page was completed or not
     * @return bool true if this page was completed, otherwise returns false
     */
    public function isCompleted() {
        switch ($this->completed) {
            case "f": return false;
            case "t": return true;
        }
    }

    /**
     * Set if this page should appear in the application's main menu
     *
     * @param $value boolean value if it should appear or not
     */
    public function setInMainMenu($value) {
        if ($value)
            $this->in_main_menu = "t";
        else
            $this->in_main_menu = "f";
    }

    /**
     * Gets first page not completed from current application
     *
     * @param $appid current application's database id
     * @return int with uncompleted page's database id
     */
    public function getPageNotcompleted($appid, $page_id = "") {
        global $misc, $lang;
        // Creates a new database access object.
        $driver = $misc->getDatabaseAccessor("phppgadmin");
        $sql = "SELECT page_id FROM crudgen.pages WHERE completed=false AND app_id={$appid}";
        if ($page_id != "")
            $sql = $sql . " AND page_id={$page_id}";
        $page_id = $driver->selectField($sql, "page_id");

        if ($page_id < 0) {
            $misc->printMsg($lang['strnopagescompleted']);
            echo "\n\t\t<a href=\"tbloperations.php?{$misc->href}\">{$lang['strclicaddpages']}</a>";
        }

        return $page_id;
    }

    /**
     * This function adds into the DB all fields from this page
     *
     * @param $tables_id wich stores this page's table databases id
     */
    public function saveFields($table_id) {
        foreach ($this->fields as $field) {
            $field->save($table_id);
        }
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

        $sql = "INSERT INTO crudgen.page_tables (table_name, pages_page_id) "
                . " VALUES ('{$this->table}',{$page_id}) RETURNING page_tables_id";

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
        $columns_id = array();

        // Creates a new database access object.
        $driver = $misc->getDatabaseAccessor("phppgadmin");

        $sql = "INSERT INTO crudgen.pages (page_filename, page_title, operation,page_text,completed,app_id)"
                . "VALUES ('{$this->page_filename}','{$this->page_title}','" . substr($this->operation, 0, 1) . "','{$this->page_text}',false,{$app_id}) RETURNING page_id";

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
        $sql = "SELECT page_filename, page_title,descr, completed,date_created,operation,in_main_menu,page_text "
                . "FROM crudgen.pages WHERE page_id={$page_id}";
        $rs = $driver->selectSet($sql);

        //Saves page related info in this object
        $this->page_id = $page_id;
        $this->page_filename = $rs->fields['page_filename'];
        $this->page_title = $rs->fields['page_title'];
        $this->operation = $rs->fields['operation'];
        $this->descr = $rs->fields['descr'];
        $this->date_created = $rs->fields['date_created'];
        $this->in_main_menu = $rs->fields['in_main_menu'];
        $this->completed = $rs->fields['completed'];
        $this->page_text = $rs->fields['page_text'];

        //fix operation variable 'cause DB only stores first character
        switch ($this->operation) {
            case "c": $this->operation = "report";
                break;
            case "r": $this->operation = "create";
                break;
            case "u": $this->operation = "update";
                break;
            case "d": $this->operation = "delete";
                break;
        }

        //Retreives table's name
        $sql = "SELECT page_tables_id, table_name FROM crudgen.page_tables WHERE pages_page_id={$page_id}";
        $rs = $driver->selectSet($sql);
        $this->table = $rs->fields['table_name'];
        $table_id = $rs->fields['page_tables_id'];

        //Loads all fields from this page
        $objField = new Fields();
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
        $sql = "UPDATE crudgen.pages SET page_filename='{$this->page_filename}',page_title='{$this->page_title}',page_text='{$this->page_text}',descr='{$this->descr}',"
                . "in_main_menu=" . $this->isInMainMenuAsString() . ",completed=true WHERE page_id={$this->page_id}";

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
        $sql = "DELETE FROM crudgen.pages WHERE page_id={$page_id}";
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

    /**
     * Updates some POST variable with this page information, so it can be used
     * in the generation process
     */
    public function updatePagePostInfo() {
        $_POST['page_title'] = $this->getTitle();
        $_POST['page_filename'] = $this->getFilename();
        $_POST["page_descr"] = $this->getDescription();
        $_POST["page_text"] = $this->setPageText();

        if ($this->in_main_menu)
            $_POST['on_main_menu'] = true;
        else
            $_POST['on_main_menu'] = null;
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
     * Returns an array of Pages, depending of $completed param returns completed
     * or uncompleted pages
     * @param $completed bool for asking if completed or not (true completed pages)
     * @return Pages array with all pages not completed
     */
    public static function getPages($completed = 'all') {
        $genpages = array();
        if ($completed !== 'all') {
            foreach ($this->pages as $page) {
                if ($page->isCompleted() == $completed)
                    $genpages[] = $page;
            }
            return $genpages;
        }
        else
            foreach ($this->pages as $page)
                $genpages[] = $page;
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
                . "WHEN operation='u' THEN '{$lang['strupdate']}' ELSE '{$lang['strreport']}' END AS operation, "
                . "CASE WHEN completed='f' THEN '{$lang['strno']}' ELSE '{$lang['stryes']}' END AS completed "
                . "FROM crudgen.pages WHERE app_id={$app_id} ORDER BY operation";
        $rs = $driver->selectSet($sql);
        return $rs;
    }

    /**
     * Builds POST array used in forms 
     */
    public function buildPost() {
        if (!isset($_POST['page_title']))
            $_POST['page_title'] = $this->getTitle();

        if (!isset($_POST['page_filename']))
            $_POST['page_filename'] = $this->getFilename();

        if (!isset($_POST['on_main_menu']['selected']))
            $_POST['on_main_menu']['selected'] = $this->isInMainMenu() ? "selected" : null;

        if (!isset($_POST["page_descr"]))
            $_POST["page_descr"] = $this->getDescription();

        if (!isset($_POST["page_text"]))
            $_POST["page_text"] = $this->getPageText();
    }

}

?>
