<?php

/**
 * class Application
 * Class wich represent a total aplication, it interacs with AppGen database for
 * store application information
 */
class Application
{
    private $app_id;
    private $descr;
    private $date_created;
    private $app_owner;
    private $db_host;
    private $db_port;
    private $db_name;
    private $db_schema;
    private $db_user;
    private $db_pass;
    private $auth_method;
    private $auth_table;
    private $auth_user_col;
    private $auth_pass_col;
    public $name;
    public $folder;
    public $library;
    public $theme;
    public $lang;
    public $pages = array(); //object to represent pages in a application

    /**
     * Adds a new page to this application
     *
     * @param unknown $page Page object to add
     */
    public function addPage( Page $page )
    {
        $this->pages[] = $page;
    }

    /**
     * Deletes a page of this application
     *
     * @param unknown $id Page's database id to delete
     * @return bool if operation was a success
     */
    public function deletePage( $id )
    {
        foreach ( $this->pages as $page ) {
            if ( $page->getPageID() == $id ){
                return $page->delete();
            }
        }

        return false;
    }

    /**
     * Checks if there's another application with the same name
     *
     * @param unknown $name name of application
     * @return bool if there's an app in the db with same name,
     */
    public function checkIfExists( $name )
    {
        global $misc;

        $driver = $misc->getDatabaseAccessor( "phppgadmin" );
        $sql = sprintf( "SELECT app_name FROM crudgen.application "
            . "WHERE app_name='%s'", $name );
        $rs = $driver->selectField( $sql, "app_name" );

        return $rs != -1;
    }

    /**
     * This functions creates a dbuser for the app
     *
     * @return unknown_type
     */
    public function createAppDbUser()
    {
        global $data, $lang;

        $this->db_user = str_replace( " ", "_", trim( $this->name, "!$#@\"\\/" ) ); //application's name as base
        $this->db_pass = crypt( $this->name ); //Generates a password for this user
    }

    /**
     * Deletes application's information from DB
     */
    public static function delete( $app_id )
    {
        global $misc;

        $driver = $misc->getDatabaseAccessor( "phppgadmin" );
        $sql = sprintf( "DELETE FROM crudgen.application WHERE app_id=%s", $app_id );
        $rs = $driver->execute( $sql );

        return $rs > 0;
    }

    /**
     * Function for deleting all files of an application
     *
     * @param unknown $path application's path, if its null, it will try with current objects' path
     * @return bool with this operation results
     */
    function deleteAppFiles( $path = null )
    {
        if ( !$path ) {
            $path = $this->app_main_path;
        }

        if ( !file_exists( $path ) ) {
            return false;
        }

        $origipath = $path;
        $handler = opendir( $path );

        while ( true )
        {
            $file = readdir( $handler );
            if ( $file == "." or $file == ".." )
            {
                continue;
            } elseif ( gettype( $file ) == "boolean" )
            {
                closedir( $handler );
                if ( !@rmdir( $path ) ) {
                    return false;
                }

                if ( $path == $origipath ) {
                    return true;
                }

                $path = substr( $path, 0, strrpos( $path, DIRECTORY_SEPARATOR ) );
                $handler = opendir( $path );
            } elseif ( is_dir( $path . DIRECTORY_SEPARATOR . $file ) ) {
                closedir( $handler );
                $path = $path . DIRECTORY_SEPARATOR . $file;
                $handler = opendir( $path );
            } else {
                unlink( $path . DIRECTORY_SEPARATOR . $file );
            }
        }
    }

    /**
     * Generates all files of this application
     */
    public function generate()
    {
        global $misc;
        $pagesToGenerate = false;

        //validates if there are any page to generate
        foreach ( $this->pages as $page )
        {
            if ( !empty( $page->page_title ) ) {
                $pagesToGenerate = true;
                break;
            }
        }
        if ( $pagesToGenerate ) 
        {
            $generator = new CodeGenerator( $this );
            $this->theme = isset( $_REQUEST['app_theme'] ) ? $_REQUEST['app_theme'] : 'default';
            $this->library = isset( $_REQUEST['app_library'] ) ? $_REQUEST['app_library'] : 'pgsql';

            //Search for the temporary folder, the following code was made to be multiple platform
            if ( function_exists( 'sys_get_temp_dir' ) )
            {
                $this->folder = sys_get_temp_dir();
            } else {
                if ( !empty( $_ENV['TMP'] ) )  {
                    $this->folder = $_ENV['TMP'];
                } else if ( !empty( $_ENV['TMPDIR'] ) )  {
                    $this->folder = $_ENV['TMPDIR'];
                } else if ( !empty( $_ENV['TEMP'] ) )  {
                    $this->folder = $_ENV['TEMP'];
                }
            }

            $this->folder .= DIRECTORY_SEPARATOR . $this->getFolderName();


            CodeGenerator::recursive_copy( "plugins/CrudGen/themes/" . $this->theme, $this->folder );
            $generator->writeCommonFile();

            if ( !$generator->generatePages() )
                $misc->printMsg( "{$this->lang['strerrpagegen']} {$page->getFilename()}." );

            unlink( $this->folder . DIRECTORY_SEPARATOR . 'index.php' );

            if ( $generator->createZipFile() )
            {
                $zip_folder = $this->folder . '.zip';
                $filename = $this->getFolderName() . '.zip';

                if ( file_exists( $zip_folder ) )
                {
                    header( 'Content-type: application/zip' );
                    header( "Content-Disposition: attachment; filename={$filename}" );
                    readfile( $zip_folder );
                    unlink( $zip_folder );
                } else {
                    $misc->printMsg( "{$this->lang['strerrpagegen']}" );
                }
            } else {
                $misc->printMsg( "{$this->lang['strerrpagegen']}" );
            }
        }
        return $pagesToGenerate;
    }

    /**
     * Gets the Authentication method
     *
     * @return string with autenthication method
     */
    public function getAuthMethod()
    {
        return $this->auth_method;
    }

    /**
     * Sets the Authentication method
     *
     * @param unknown $method string with autenthication method
     */
    public function setAuthMethod( $method )
    {
        $this->auth_method = $method;
    }

    /**
     * Gets the Authentication's table
     *
     * @return string with the name of authentication table
     */
    public function getAuthTable()
    {
        return $this->auth_table;
    }

    /**
     * Sets the Authentication method
     *
     * @param unknown $name string with the name of authentication table
     */
    public function setAuthTable( $name )
    {
        $this->auth_table = $name;
    }

    /**
     * Gets the Authentication column where user data is stored
     *
     * @return string with name of the column name
     */
    public function getAuthUser()
    {
        return $this->auth_user_col;
    }

    /**
     * Sets the name of the column where user data is stored
     *
     * @param unknown $name string with name of the column name
     */
    public function setAuthUser( $name )
    {
        $this->auth_user_col = $name;
    }

    /**
     * Gets the name of the column where password data is stored
     *
     * @return string with name of the column name
     */
    public function getAuthPassword()
    {
        return $this->auth_pass_col;
    }

    /**
     * Sets the name of the column where password data is stored
     *
     * @param unknown $name string with name of the column name
     */
    public function setAuthPassword( $name )
    {
        $this->auth_pass_col = $name;
    }

    /**
     * Gets this application's description
     *
     * @return string with description
     */
    public function getDescription()
    {
        return $this->descr;
    }

    /**
     * Sets this application's  description
     *
     * @param unknown $description text with the description
     */
    public function setDescription( $description )
    {
        $this->descr = $description;
    }

    /**
     * Gets this application's name
     *
     * @return string with application name
     */
    public function getFolderName()
    {
        $filename = $this->name;
        $filename = str_replace( '/', '', $filename );
        $filename = str_replace( ' ', '_', $filename );
        $filename = str_replace( '\\', '', $filename );

        return $filename;
    }

    /**
     * Gets this application's owner name
     *
     * @return string with owner name
     */
    public function getOwner()
    {
        return $this->app_owner;
    }

    /**
     * Sets this application's name
     *
     * @param unknown $name desired application's name
     */
    public function setName( $name )
    {
        $this->name = $name;
    }

    /**
     * Returns the date when this application was created
     *
     * @return string with the creation date
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Returns this application's working database
     *
     * @return string with DB name
     */
    public function getDBName()
    {
        return $this->db_name;
    }

    /**
     * Sets this application's database
     *
     * @param unknown $name string with the name of working database
     */
    public function setDBName( $name )
    {
        $this->db_name = $name;
    }

    /**
     * Returns application database's hostname
     *
     * @return string with DB name
     */
    public function getDBHost()
    {
        return $this->db_host;
    }

    /**
     * Sets this application database's hostname
     *
     * @param unknown $hostname string with the name of database's hostname
     */
    public function setDBHost( $hostname )
    {
        $this->db_host = $hostname;
    }

    /**
     * Returns this applications database's port
     *
     * @return string with DB port
     */
    public function getDBPort()
    {
        return $this->db_port;
    }

    /**
     * Sets this applications database's port
     *
     * @param unknown $port number of database's port
     */
    public function setDBPort( $port )
    {
        $this->db_port = $port;
    }

    /**
     * Returns application database's username
     *
     * @return string with DB username
     */
    public function getDBUser()
    {
        return $this->db_user;
    }

    /**
     * Sets this application database's username
     *
     * @param unknown $username string with the name of database's username
     */
    public function setDBUser( $username )
    {
        $this->db_user = $username;
    }

    /**
     * Returns this applications database's password
     *
     * @return string with DB password
     */
    public function getDBPass()
    {
        return $this->db_pass;
    }

    /**
     * Sets this applications database's password
     *
     * @param unknown $password string of database's password
     */
    public function setDBPass( $password )
    {
        $this->db_pass = $password;
    }

    /**
     * Returns this application's primary key
     *
     * @return int with this app's db id
     */
    public function getId()
    {
        return $this->app_id;
    }

    /**
     * Sets this application's primary key
     */
    public function setId( $id )
    {
        $this->app_id = $id;
    }

    /**
     * Sets all application's attributes from REQUEST variable
     */
    public function setAttributes()
    {

        if ( isset( $_REQUEST['app_id'] ) )
            $this->setId( $_REQUEST['app_id'] );

        $this->setName( $_REQUEST['name'] );
        $this->setDBHost( $_REQUEST['db_host'] );
        $this->setDBPort( $_REQUEST['db_port'] );
        $this->setDBUser( $_REQUEST['db_user'] );
        $this->setDBPass( $_REQUEST['db_pass'] );
        $this->setAuthTable( $_REQUEST['auth_table'] );
        $this->setAuthMethod( $_REQUEST['auth_method'] );
        $this->setAuthUser( $_REQUEST['auth_user_col'] );
        $this->setAuthPassword( $_REQUEST['auth_pass_col'] );
        $this->setDescription( $_REQUEST['descr'] );
        $this->setSchema( $_REQUEST['schema'] );
        $this->setDBName( $_REQUEST['database'] );
    }

    /**
     * Sets REQUEST object from current object's attributes
     */
    public function buildRequest()
    {
        $_REQUEST['name'] = $this->name;
        $_REQUEST['db_host'] = $this->getDBHost();
        $_REQUEST['db_name'] = $this->getDBName();
        $_REQUEST['db_port'] = $this->getDBPort();
        $_REQUEST['db_user'] = $this->getDBUser();
        $_REQUEST['db_pass'] = $this->getDBPass();
        $_REQUEST['descr'] = $this->getDescription();
        $_REQUEST['auth_table'] = $this->getAuthTable();
        $_REQUEST['auth_user_col'] = $this->getAuthUser();
        $_REQUEST['auth_method'] = $this->getAuthMethod();
        $_REQUEST['auth_pass_col'] = $this->getAuthPassword();
    }

    /**
     * This function generates the html code for the main menu of the application
     *
     * @return string with the html code for the main menu
     */
    public function getMenu()
    {
        $menu_code = "";

        foreach ( $this->pages as $page )
        {
            if ( $page->inMainMenu() )
                $menu_code.="\n\t\techo '<li>"
                    . "<a href=\"{$page->getFilename()}\" class=\"menu-link\">"
                . htmlspecialchars( $page->page_title ) . "</a></li>';";
        }

        if ( !empty( $menu_code ) )
            $menu_code = "\t\techo '<ul class=\"main-menu nav navbar-nav\">';{$menu_code}\n\t\techo '</ul>';";

        return $menu_code;
    }

    /**
     * Gets this application's working schema
     *
     * @return string with working db schema
     */
    public function getSchema()
    {
        return $this->db_schema;
    }

    /**
     * Sets this application's working schema
     *
     * @param unknown $schema name of the working schema
     */
    public function setSchema( $schema )
    {
        $this->db_schema = $schema;
    }

    /**
     * Returns a query of a single application given its id and database
     *
     * @param unknown $database name of the database where are the applications stored
     */
    public static function get( $app_id )
    {
        global $data, $misc;

        $server_info = $misc->getServerInfo();
        $driver = $misc->getDatabaseAccessor( "phppgadmin" );
        $sql = sprintf( "SELECT a.app_id,a.app_name,a.descr,"
            . "a.date_created,a.db_schema,a.db_name,"
            . "(SELECT count(*) FROM crudgen.pages p WHERE p.app_id=a.app_id) as pages "
            . "FROM crudgen.application a "
            . "WHERE app_owner='{$server_info["username"]}' AND a.app_id=%d "
            . "ORDER BY a.date_created DESC", $app_id );

        $rs = $driver->selectSet( $sql );
        return $rs;
    }

    /**
     * Returns application name from DB
     *
     * @param unknown $database name of the database where are the applications stored
     */
    public static function getNameFromDB( $app_id )
    {
        global $data, $misc;

        $server_info = $misc->getServerInfo();
        $driver = $misc->getDatabaseAccessor( "phppgadmin" );
        $sql = sprintf( "SELECT a.app_name "
            . "FROM crudgen.application a "
            . "WHERE app_owner='{$server_info["username"]}' "
            . "AND a.app_id=%d", $app_id );

        return $driver->selectField( $sql, 'app_name' );
    }

    /**
     * Returns a query of all detected applications for current schema and current user
     *
     * @param unknown $database name of the database where are the applications stored
     */
    public static function getAll( $database, $schema )
    {
        global $data, $misc;

        $server_info = $misc->getServerInfo();
        $driver = $misc->getDatabaseAccessor( "phppgadmin" );
        $sql = sprintf( "SELECT a.app_id,a.app_name,a.descr,"
            . "a.date_created,a.db_schema,a.db_name,"
            . "(SELECT count(*) FROM crudgen.pages p WHERE p.app_id=a.app_id) as pages "
            . "FROM crudgen.application a "
            . "WHERE app_owner='%s' AND db_name='%s' AND db_schema='%s' "
            . "ORDER BY a.date_created DESC",$server_info["username"], $database, $schema );
        $rs = $driver->selectSet( $sql );

        return $rs;
    }

    /**
     * This function loads application information into current object
     *
     * @param unknown $app_id desired application id from DB for load information
     */
    public function load( $app_id )
    {
        global $misc;

        $driver = $misc->getDatabaseAccessor( "phppgadmin" );
        $sql = sprintf( "SELECT app_name, descr,date_created,app_owner,db_name,db_schema, "
            . "db_user,db_pass, db_host, db_port,auth_method, "
            . "auth_table,auth_user_col,auth_pass_col "
            . "FROM crudgen.application WHERE app_id=%d", $app_id );
        $rs = $driver->selectSet( $sql );

        //Saves application information in this object
        $this->app_id = $app_id;
        $this->name = $rs->fields['app_name'];
        $this->descr = $rs->fields['descr'];
        $this->date_created = $rs->fields['date_created'];
        $this->app_owner = $rs->fields['app_owner'];
        $this->db_name = $rs->fields['db_name'];
        $this->db_schema = $rs->fields['db_schema'];
        $this->db_host = $rs->fields['db_host'];
        $this->db_port = $rs->fields['db_port'];
        $this->db_user = $rs->fields['db_user'];
        $this->db_pass = $rs->fields['db_pass'];
        $this->auth_method = $rs->fields['auth_method'];
        $this->auth_table = $rs->fields['auth_table'];
        $this->auth_user_col = $rs->fields['auth_user_col'];
        $this->auth_pass_col = $rs->fields['auth_pass_col'];

        //Here it loads the pages of this application
        $sql = sprintf( "SELECT page_id FROM crudgen.pages WHERE app_id=%d", $this->app_id );
        $rs = $driver->selectSet( $sql );
        while ( !$rs->EOF )
        {
            $page = new Page();
            $page->load( $rs->fields["page_id"] );
            $this->addPage( $page );
            $rs->movenext();
        }
    }

    /**
     * This function stores only the application data on the DB,
     *
     * @return Inserted application's id
     * @access public
     */
    public function insert()
    {
        global $misc;

        // Create a new database access object.
        $driver = $misc->getDatabaseAccessor( "phppgadmin" );
        $sql = sprintf( "INSERT INTO crudgen.application (app_name, descr,db_name, "
            . "db_schema, db_user,db_pass, db_host, db_port, "
            . "auth_method, auth_table,auth_user_col,auth_pass_col) "
            . "VALUES ('%s','%s','%s','%s','%s','%s','%s',%d,'%s','%s','%s','%s'"
            . ") RETURNING app_id", $this->name, $this->descr, $this->db_name,
            $this->db_schema, $this->db_user, $this->db_pass, $this->db_host,
            $this->db_port, $this->auth_method, $this->auth_table,
            $this->auth_user_col, $this->auth_pass_col );

        $app_id = $driver->selectField( $sql, "app_id" );
        $this->app_id = $app_id;
        return $app_id;
    }

    /**
     * Updates the application data on the DB,
     *
     * @return bool if operation was a succes
     * @access public
     */
    public function update()
    {
        global $misc;

        //If this object doesn't have an id return false
        if ( empty( $this->app_id ) )
            return false;

        // Create a new database access object.
        $driver = $misc->getDatabaseAccessor( "phppgadmin" );

        $sql = "UPDATE crudgen.application SET app_name='{$this->name}',"
            . "descr='{$this->descr}',db_name='{$this->db_name}',"
            . "db_schema='{$this->db_schema}',"
            . "db_user='{$this->db_user}',db_pass='{$this->db_pass}', "
            . "db_host='{$this->db_host}', db_port='{$this->db_port}',"
            . "auth_method='{$this->auth_method}', auth_table='{$this->auth_table}',"
            . "auth_user_col='{$this->auth_user_col}',"
            . "auth_pass_col='{$this->auth_pass_col}' "
            . "WHERE app_id={$this->app_id}";

        $rs = $driver->execute( $sql );
        return ( $rs < 0 ) ? false : true;
    }

    /**
     * Validates if this app has a unique name
     */
    public function hasUniqueName()
    {
        global $misc;

        $driver = $misc->getDatabaseAccessor( "phppgadmin" );

        //Validates if it has a unique application name
        $sql = sprintf( "SELECT app_name FROM crudgen.application "
            . "WHERE app_name='%s'", $this->name );

        if ( !empty( $this->app_id ) )
            $sql .= "AND app_id <> {$this->app_id}";

        $name = $driver->selectField( $sql, "app_name" );

        return ( $name == -1 ) ? true : false;
    }

    /**
     * Function to store this application object in the database,
     * including application, pages, tables and field data.
     *
     * @access public
     */
    public function save()
    {
        $rs = false;

        //first saves app data
        if ( empty( $this->app_id ) )
        {
            $rs = $this->insert();

            if ( $rs < 1 ) {
                return false;
            } else {
                $this->app_id = $rs;
            }
        }
        else
        {
            $rs = $this->update();
        }

        //saves all fields in each page object and the saves the page
        if ( $rs )
        {
            foreach ( $this->pages as $page )
            {
                if ( ( $page_id = $page->insertPageAtDB( $app_id ) ) >= 0 )
                {
                    $table_id = $page->insertTableAtDB( $page_id );
                    $page->insertFieldsAtDB( $table_id );
                }
                else {
                    return false;
                }
            }
        }

        return $rs;
    }

    /**
     * Checks if there's another page  with the same name in this application
     *
     * @param unknown $page_name name of the page
     * @return bool if there's an app in the db with same name,
     */
    public function checkIfPageExists( $page_name )
    {
        global $misc;

        $driver = $misc->getDatabaseAccessor( "phppgadmin" );
        $sql = sprintf( "SELECT p.page_filename "
            . "FROM crudgen.pages p, crudgen.application a "
            . "WHERE a.app_id=%d AND p.page_filename='%s'",
            $this->app_id, $page_name );

        $rs = $driver->selectField( $sql, "page_filename" );

        return $rs != -1;
    }

    /**
     * This function checks if 2 pages have the same filename
     *
     * @param filename the name to check if already exists for this application
     */
    public function isUniqueFilename( $page_id, $filename )
    {
        foreach ( $this->pages as $page )
        {
            if ( ( $page->getFilename() == $filename ) &&
                ( $page->getId() != $page_id ) )
                return false;
        }

        return true;
    }
}
?>
