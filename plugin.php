<?php
require_once('classes/Plugin.php');
include_once('plugins/CrudGen/classes/Application.php');
include_once('plugins/CrudGen/classes/Fields.php');
include_once('plugins/CrudGen/classes/Pages.php');
include_once('plugins/CrudGen/classes/Generator.php');
include_once('plugins/CrudGen/classes/Theme.php');
include_once('plugins/CrudGen/classes/Security.php');

class CrudGen extends Plugin {

	/**
	 * Attributes
	 */
	protected $name = 'CrudGen';
	protected $lang;

	/**
	 * Constructor
	 */
	function __construct($language) {
		parent::__construct($language);
	}
    
     /**
     * Prints options for a html combo-box and receives a value to select by default
     * @param $array an array with values for the combo box
     * @param $sel_value value of selected index
     * @return string with html code for options
     */
    private function printSelOptions($array, $sel_value) {
        $html_code = "";
            foreach ($array as $value) {
                $html_code = $html_code . "\n\t\t\t\t<option";
                if ($value == $sel_value)
                    $html_code = $html_code . " selected=\"selected\"";
                $html_code = $html_code . ">{$value}</option>\n";
            }
        return $html_code;
    }
    
    /*
     * Function to check in DB if appgen's sql script was run,
     * so its  schema must be created
     */
     private function checkAppDB() {
        global $lang, $data, $misc;

        // Check to see if the ppa database exists
        $rs = $data->getDatabase("phppgadmin");
        
        if ($rs->recordCount() != 1)
            return false;
        else {
            // Create a new database access object.
            $driver = $misc->getDatabaseAccessor("phppgadmin");
            $schemas = $driver->getSchemas();
            
            // Reports database should have been created in public schema
            if (count($schemas)==0)
                return false;
            
            //Checks for appgen in the schemas array
            foreach ($schemas as $i)
                if ($i["nspname"] == 'crudgen')
                    return true;
        }
        

        return false;
    }
    /**
     * Prints HTML code to include plugin's js file
     * 
     * @return string HTML code of the included javascript
     */
    private function include_js(){
        return "<script type=\"text/javascript\" src=\"plugins/{$this->name}/js/crudgen.js\"></script>";
    }
    
    /**
     * Builds an internal link array to simply code
     */
     private function build_link($action){
        global $misc;
        
        return "plugin.php?plugin={$this->name}&amp;action={$action}"
               ."&amp;{$misc->href}&amp;";
    }
    
    /**
     * Builds an external link array to simply code
     */
     private function build_nav_link($url, $action, $content, $extra_vars = array()){
         $urlvars = array (
                        'action' => $action,
                        'server' => field('server'),
                        'subject' => field('subject'),
                        'database' => field('database'),
                        'schema' => field('schema'),
                    );
         
        return array (
		 		'attr'=> array ('href' => array( 'url' => $url, 'urlvars' => array_merge($urlvars, $extra_vars))),
		 		'content' => $content
                );
    }
    
    /**
     * Builds a plugin link array to simply code
     */
     private function build_plugin_link($action, $content){
        return array (
		 		'attr'=> array ('href' => array(
                    'url' => 'plugin.php',
						'urlvars' => array (
                            'plugin' => $this->name,
							'action' => $action,
							'server' => field('server'),
							'subject' => field('subject'),
							'database' => field('database'),
							'schema' => field('schema'),
						)
                )),
		 		'content' => $content
            );
    }
    
    /**
     * Frees all SESSION variables created by the wizard
     */
     private function cleanWizardVars() {
        unset($_SESSION['apptables']);
        unset($_SESSION['browse']);
        unset($_SESSION['insert']);
        unset($_SESSION['update']);
        unset($_SESSION['delete']);
    }
    
    /**
     * Prints a message when there are no tables and offer the user to create them
     */
     private function print_no_tables() {
        global $misc, $lang;
        
        $misc->printMsg($lang['strnotables']);
            echo '<p>' . $this->lang['strerrnotbl'] . '</p>';
            
        $navlinks = array ( 
                        $this->build_nav_link('tables.php','create',$lang['strcreatetable']),
                        $this->build_nav_link('tables.php','createlike',$lang['strcreatetablelike']),
                    );
        
        $misc->printNavLinks($navlinks, 'create_app');
    }

	/**
	 * This method returns the functions that will hook in the phpPgAdmin core.
	 *
	 * @return $hooks
	 */
	function get_hooks() {
		$hooks = array(
			'tabs' => array('add_plugin_tabs'),
			'trail' => array('add_plugin_trail'),
		);
		return $hooks;
	}

	/**
	 * This method returns the functions that will be used as actions.
	 *
	 * @return $actions
	 */
	function get_actions() {
		$actions = array(
            'show_app',
			'show_apps',
			'create_app',
            'edit_app',
            'save_app',
            'delete_app',
            'app_wizard',
			'tree'
		);
		return $actions;
	}

	/**
	 * Add plugin in the tabs
     * 
	 * @param $plugin_functions_parameters
	 */
	function add_plugin_tabs(&$plugin_functions_parameters) {
		global $misc;

		$tabs = &$plugin_functions_parameters['tabs'];

		switch ($plugin_functions_parameters['section']) {
			case 'schema':
				$tabs['crudgen'] = array (
					'title' => $this->lang['strdescription'],
					'url' => 'plugin.php',
					'urlvars' => array(
						'server' => $_REQUEST['server'],
						'database' => $_REQUEST['database'],
						'schema' => $_REQUEST['schema'],
						'action' => 'show_apps', 
						'plugin' => $this->name),
					'hide' => false,
					'icon' => array('plugin' => $this->name, 'image' => 'CrudGen')
				);
				break;
		}
	}

	/**
	 * Add plugin in the trail
	 * @param $plugin_functions_parameters
	 */
	function add_plugin_trail(&$plugin_functions_parameters) {
		global $misc;
        
		$trail = &$plugin_functions_parameters['trail'];
		
		if (isset($_REQUEST['app_id'])) {
			$name = Application::getAppNameFromDB($_REQUEST['app_id']);
		}
        else {
            $subject = null;
        }

		if (!empty($name)) {
			$url = array (
				'url' => 'plugin.php',
				'urlvars' => array (
					'plugin' => $this->name,
					'subject' => 'crudgen',
					'action' => 'show_apps'
				)
			);
			$trail['show_apps'] = array(
				'title' => 'View application\'s information',
				'text'  => $name,
				'url'   => $misc->printActionUrl($url, $_REQUEST, null, false),
				'icon' => array('plugin' => $this->name, 'image' => 'CrudGen')
			);
		}
	}
    /**
     * Show a single created app
     */
    function show_app(){
        global $lang, $misc;
        
        $misc->printHeader($lang['strdatabase']);
		$misc->printBody();
        $misc->printTrail('schema');
        
        $columns = array(
            'ID' => array(
                'title' => $this->lang['strid'],
                'field' => field('app_id'),
            ),
            'name' => array(
                'title' => $lang['strname'],
                'field' => field('app_name'),
            ),
            'descr' => array(
                'title' => $this->lang['strdescr'],
                'field' => field('descr'),
            ),
            'date' => array(
                'title' => $lang['strcreated'],
                'field' => field('date_created')
            ),
            'pages' => array(
                'title' => $this->lang['strpages'],
                'field' => field('pages')
            ),
            'pages_not_created' => array(
                'title' => $this->lang['strpagenotcreated'],
                'field' => field('pages_not_created'),
            ),
            'pages_created' => array(
                'title' => $this->lang['strpagecreated'],
                'field' => field('pages_created'),
            ),
            'actions' => array(
                'title' => "Actions",
            ),
        );
        $actions = array(
            'edit' => array(
                'title' => $lang['stredit'],
                'url' => $this->build_link('edit_app'),
                'vars' => array('app_id' => 'app_id'),
            ),
            'delete' => array(
                'title' => $lang['strdelete'],
                'url' => $this->build_link('delete_app'),
                'vars' => array('app_id' => 'app_id'),
            ),
        );
        
        $rs = Application::getApplication( $_REQUEST['app_id'] );
        $misc->printTable($rs, $columns, $actions, $this->lang['strnoapps']);        
        $extra_vars = array('app_id'=>$_REQUEST['app_id'], 'plugin'=> $this->name);
        
        $navlinks = array(
                        $this->build_nav_link('plugin.php','app_wizard', $this->lang['strappwizard'],$extra_vars),
                        $this->build_nav_link('plugin.php','add_page', $this->lang['straddpage'],$extra_vars),
                        $this->build_nav_link('plugin.php','edit_page', $this->lang['streditpages'],$extra_vars),
                        $this->build_nav_link('plugin.php','edit_app', $lang['stredit'],$extra_vars),
                        $this->build_nav_link('plugin.php','delete_app', $lang['strdelete'],$extra_vars),
                        $this->build_nav_link('plugin.php','generate_app', $this->lang['strgenerate'],$extra_vars),
                    );
		
        $misc->printNavLinks($navlinks, 'show_app');       
        $misc->printFooter();
    }

	/**
	 * Show a list of created apps
	 */
	function show_apps($msg = '') {
		global $lang, $misc;
        
        unset($_REQUEST['app_id']);

		$misc->printHeader($lang['strdatabase']);
		$misc->printBody();
        $misc->printTrail('schema');
        $misc->printTabs('schema','crudgen');

		
        unset($_SESSION["appid"]);

        $columns = array(
            'ID' => array(
                'title' => $this->lang['strid'],
                'field' => field('app_id'),
            ),
            'name' => array(
                'title' => $lang['strname'],
                'field' => field('app_name'),
                'url' => $this->build_link('show_app'),
                'vars' => array('app_id' => 'app_id'),
            ),
            'descr' => array(
                'title' => $this->lang['strdescr'],
                'field' => field('descr'),
            ),
            'date' => array(
                'title' => $lang['strcreated'],
                'field' => field('date_created')
            ),
            'pages_not_created' => array(
                'title' => $this->lang['strpagesnotcreated'],
                'field' => field('pages_not_created'),
            ),
            'pages_created' => array(
                'title' => $this->lang['strpagescreated'],
                'field' => field('pages_created'),
            ),
            'actions' => array(
                'title' => "Actions",
            ),
        );
        $actions = array(
            'wizard' => array(
                'title' => $this->lang['strappwizard'],
                'url' => $this->build_link('app_wizard'),
                'vars' => array('app_id' => 'app_id'),
            ),
            'list' => array(
                'title' => $this->lang['strmanagepage'],
                'url' => $this->build_link('list_pages'),
                'vars' => array('app_id' => 'app_id'),
            ),
            'edit' => array(
                'title' => $lang['stredit'],
                'url' => $this->build_link('edit_app'),
                'vars' => array('app_id' => 'app_id'),
            ),
            'delete' => array(
                'title' => $lang['strdelete'],
                'url' => $this->build_link('delete_app'),
                'vars' => array('app_id' => 'app_id'),
            ),
            'generate' => array(
                'title' => $this->lang['strgenerate'],
                'url' => $this->build_link('generate_app'),
                'vars' => array('app_id' => 'app_id'),
            ),
        );
                
        if ($this->checkAppDB()) {     //Checks if appgen db was installed
           $rs = Application::getAppsOfDB($_REQUEST['database'],$_REQUEST['schema']);

            if (!empty($msg))
                $misc->printMsg($msg);

            $misc->printTable($rs, $columns, $actions,'', $this->lang['strnoapps']);
        }
        else
            $misc->printMsg($this->lang['strnocrudgendb']);
        
        $navlinks = array(
                        $this->build_plugin_link('create_app', $this->lang['strcreateapp'])
                    );
		$misc->printNavLinks($navlinks, 'show_apps');
		$misc->printFooter();
	}
    
    /**
     * This functions prints a form to create an application
     * 
     * @param string $msg text of the notification message
     */
    function create_app($msg = ''){
        global $data, $misc, $lang;
        
        $misc->printHeader($lang['strdatabase']);
		$misc->printBody();
        $misc->printTrail('schema');
        $misc->printTabs('schema','crudgen');
        
        //Loads tables and columns
        $tables = array();
        $tbltmp = $data->getTables();
        
        foreach ($tbltmp as $table)
            $tables[] = $table["relname"];

        if (count($tables) == 0) {
            //Print warning and offers a link for creating tables
            $this->print_no_tables();
        } 
        else {
            $app = new Application();
            $columns = array();
            $server_info = $misc->getServerInfo();

            if (!isset($_REQUEST['name']))  $_REQUEST['name'] = '';
            if (!isset($_REQUEST['descr'])) $_REQUEST['descr'] = '';
            if (!isset($_REQUEST['theme'])) $_REQUEST['theme'] = 'default';
            if (!isset($_REQUEST['db_host']))   $_REQUEST['db_host'] = '127.0.0.1';
            if (!isset($_REQUEST['db_name']))   $_REQUEST['db_name'] = '';
            if (!isset($_REQUEST['db_port']))   $_REQUEST['db_port'] = '5432';
            if (!isset($_REQUEST['db_user']))   $_REQUEST['db_user'] = $server_info["username"];
            if (!isset($_REQUEST['db_pass']))   $_REQUEST['db_pass'] = '';
            if (!isset($_REQUEST['auth_method']))   $_REQUEST['auth_method'] = 'none';
            if (!isset($_REQUEST['auth_table']))    $_REQUEST['auth_table'] = $tables[0];
            if (!isset($_REQUEST['auth_user_col'])) $_REQUEST['auth_user_col'] = '';
            if (!isset($_REQUEST['auth_pass_col'])) $_REQUEST['auth_pass_col'] = '';

            //Loads columns
            $coltmp = $data->getTableAttributes($_REQUEST["auth_table"]);
            
            if ($coltmp->recordCount() > 0){
                while (!$coltmp->EOF) {
                    $columns[] = $coltmp->fields['attname'];
                    $coltmp->moveNext();
                }
            }
            
            if(empty($_REQUEST['app_id']))
                $misc->printTitle($this->lang['strcreateapp']);
            else
                $misc->printTitle($this->lang['streditapp']);
            
            if (!empty($msg))
                $misc->printMsg($msg);

            echo "<form id=\"createappform\" method=\"post\">\n";
            echo "<table>\n";
            echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strname']}</th>\n";
            echo "\t\t<td class=\"data\"><input type=\"text\"  name=\"name\" size=\"33\" maxlength=\"63\" value=\"";
            echo htmlspecialchars($_REQUEST['name']) . "\" /> *</td>\n\t</tr>\n";
            echo "\t<tr>\n\t\t<th class=\"data left\">{$this->lang['strdescr']}</th>\n";
            echo "\t\t<td class=\"data\"><textarea name=\"descr\" rows=\"3\" cols=\"33\" style=\"overflow:auto;\">";
            echo "{$_REQUEST["descr"]}</textarea></td>\n\t</tr>";
            echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strhost']}</th>\n";
            echo "\t\t<td class=\"data\"><input type=\"text\"  name=\"db_host\" size=\"33\" maxlength=\"255\" value=\"";
            echo htmlspecialchars($_REQUEST['db_host']) . "\" /> *</td>\n\t</tr>\n";
            echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strport']}</th>\n";
            echo "\t\t<td class=\"data\"><input type=\"text\"  name=\"db_port\" size=\"5\" maxlength=\"5\" value=\"";
            echo htmlspecialchars($_REQUEST['db_port']) . "\" /> *</td>\n\t</tr>\n";
            echo "\t<tr>\n\t\t<th class=\"data left required\">{$lang['strusername']}</th>\n";
            echo "\t\t<td class=\"data\"><input type=\"text\"  name=\"db_user\" size=\"33\" maxlength=\"255\" value=\"";
            echo htmlspecialchars($_REQUEST['db_user']) . "\" /> *</td>\n\t</tr>\n";
            echo "\t<tr>\n\t\t<th class=\"data left\">{$lang['strpassword']}</th>\n";
            echo "\t\t<td class=\"data\"><input type=\"password\" name=\"db_pass\" size=\"33\" maxlength=\"255\" value=\"";
            echo htmlspecialchars($_REQUEST['db_pass']) . "\" /></td>\n\t</tr>\n";

            //Security options
            echo "\n\t<tr>\n\t\t<th class=\"data left required\">{$this->lang['strsecaccess']}</th>\n\t\t";
            echo "<td><select name=\"auth_method\" id=\"auth_method\" style=\"width:100%;\" onchange=\"updateSecurityTable()\"><option value=\"none\"";
            if ($_REQUEST['auth_method'] == 'none')
                echo " selected=\"selected\"";
            echo ">{$this->lang['strnosecurity']}</option><option value=\"dbuser\"";
            if ($_REQUEST['auth_method'] == 'dbuser')
                echo " selected=\"selected\"";
            echo ">{$this->lang['strsecdbuser']}</option><option value=\"dbtable\"";
            if ($_REQUEST['auth_method'] == 'dbtable')
                echo " selected=\"selected\"";
            echo ">{$this->lang['strsecdbstored']}</option></select></td></tr>";
            
            //Security parameters
            echo "\t<tr id=\"table-row\" ";
            if ($_REQUEST['auth_method'] != 'dbtable')
                echo "style=\"display:none\"";
            echo">\n\t\t<th class=\"data left required\">{$lang['strtable']}</th>\n";
            echo "<td><select name=\"auth_table\" onchange=\"updateColumns()\" >";
            echo "<option value=\"0\">&#45;&#45;{$this->lang['plseltable']}&#45;&#45;</option>";
            echo $this->printSelOptions($tables, $_REQUEST['auth_table']);
            echo "</select></td></tr>";
            echo "\t<tr id=\"user-row\" ";
            if ($_REQUEST['auth_method'] != 'dbtable')
                echo "style=\"display:none\"";
            echo">\n\t\t<th class=\"data left required\">{$lang['strusername']}</th>\n";
            echo "<td><select id=\"auth_user_col\" name=\"auth_user_col\">";
            echo "<option value=\"0\">&#45;&#45;{$lang['plselcol']}&#45;&#45;</option>";
            echo $this->printSelOptions($columns, $_REQUEST['auth_user_col']);
            echo "</select></td></tr>";
            echo "\t<tr id=\"pass-row\" ";
            if ($_REQUEST['auth_method'] != 'dbtable')
                echo "style=\"display:none\"";
            echo">\n\t\t<th class=\"data left required\">{$lang['strpassword']}</th>\n";
            echo "<td><select id=\"auth_pass_col\" name=\"auth_pass_col\">";
            echo "<option value=\"0\">&#45;&#45;{$lang['plselcol']}&#45;&#45;</option>";
            echo $this->printSelOptions($columns, $_REQUEST['auth_pass_col']);
            echo "</select></td></tr>";

            /*
             * Still no support for creating apps in another language
             *
             * echo "\n\t<tr>\n\t\t<th class=\"data left required\">{$lang['strlanguage']}</th>";
             * echo "\t\t<td class=\"data\"><select name=\"db_user\">";
             * echo "<option ";
             * if($_REQUEST["lang"]=="en")echo " selected=\"selected\"";
             * echo ">en</option><opti
             * on";
             * if($_REQUEST["lang"]=="es")echo " selected=\"selected\"";
             * echo ">es</option></select></td></tr>";
             */

            echo "</table>\n";
            echo "<p> * Required fields</p>";
            echo "<p><input type=\"hidden\" id=\"action-input\" name=\"action\" value=\"save_app\" />\n";
            if (isset($_REQUEST['app_id'])) {
                echo "<input type=\"hidden\" name=\"app_id\" value=\"{$_REQUEST['app_id']}\" />\n";
                $submit_caption = $lang['strupdate'];
            }
            else
                $submit_caption=$lang['strcreate'];

            echo "<input type=\"submit\" name=\"vacuum\" value=\"{$submit_caption}\" />\n";
            echo "<input type=\"button\" name=\"cancel\" value=\"{$lang['strcancel']}\" onclick=\"history.back();\" />\n";
            echo "</p></form>\n";
            echo $this->include_js();
        }
    }
    
    /**
     * Edits a created application
     */
    function edit_app() {
        
        if (is_numeric($_REQUEST['app_id'])) {
            $app = new Application();
            $app->load($_REQUEST['app_id']);
            $app->buildRequest();
            $this->create_app();
        }
        else
            $this->show_apps();
    }
    
    /**
     * Check application's input data and stores it on the DB
     */
    function save_app() {
        global $data, $misc, $lang;
        
        if(!empty($_REQUEST['cancel']))
            return $this->show_apps();
        
        $msg = array();
        $app = new Application();

        //Validates input data
        if ((!isset($_REQUEST['name'])) || ($_REQUEST['name'] == ''))
            return $this->create_app($this->lang['strnoappname']);

        if (empty($_REQUEST['db_host']))
            return $this->create_app($this->lang['strnohost']);

        if (!is_numeric($_REQUEST['db_port']))
            return $this->create_app($this->lang['strnoport']);

        if ((!isset($_REQUEST['db_user'])) || ($_REQUEST['db_user'] == ''))
            return $this->create_app($this->lang['strnousername']);

        if ($_REQUEST['auth_method'] == "dbtable") {
            
            if (empty($_REQUEST['auth_table']))
                return $this->create_app($this->lang['strnotablecol']);

            if (empty($_REQUEST['auth_user_col']))
                return $this->create_app($this->lang['strnousercol']);

            if (empty($_REQUEST['auth_pass_col'])) 
                return $this->create_app($this->lang['strnopasscol']);
        }
        
        if (!isset($_REQUEST['auth_method'])){
            $_REQUEST['auth_method'] = 'none';
        }
        elseif ($_REQUEST['auth_method'] != 'dbtable') {
            $_REQUEST['auth_table'] = '';
            $_REQUEST['auth_user_col'] = '';
            $_REQUEST['auth_pass_col'] = '';
        }
        
        $app->setAttributes();
        
        if($app->save()){
            $msg = (empty($_REQUEST['app_id']))? $this->lang['strappsaved'] : $this->lang['strappedited'];
        }
        else
            $this->lang['strappnotsaved'];

        $this->show_apps($msg);
    }
    
    /**
     * Function to delete selected application
     * @param $confirm bool for asking confirmation of deletion process
     */
    function delete_app() {
        global $lang, $misc;
        
        if(!empty($_REQUEST['cancel']))
            return $this->show_apps();

        if (!isset($_POST['delete'])) {
            $misc->printHeader($lang['strdatabase']);
            $misc->printBody();
            $misc->printTrail('schema');
            $misc->printTabs('schema','crudgen');
            
            echo "\n\t<h2>{$lang['strdelete']}</h2>\n\t<p>{$this->lang['strconfdelapp']}</p>"
            . "\n\t<form method=\"post\">\n\t\t"
            . "\n\t\t<input type=\"hidden\" name=\"action\" value=\"delete_app\" />"
            . "\n\t\t<input type=\"hidden\" name=\"app_id\" value=\"{$_REQUEST["app_id"]}\" />"
            . "\n\t\t<input type=\"submit\" name=\"delete\" value=\"{$lang['strdelete']}\" />"
            . "\n\t\t<input type=\"button\" name=\"cancel\" value=\"{$lang['strcancel']}\" onclick=\"history.back();\" />\n\t</form>";
            
            $misc->printFooter();
        } 
        else {
            $msg = (Application::delete($_REQUEST["app_id"]))? $this->lang['strerrdelapp'] : $this->lang['strdelapp'];
            $this->show_apps($msg);
        }
    }
    
    /*
     * This prints the multi-step form to add as many pages as the operations requires
     */
    function app_wizard() {
        global $data, $misc, $lang;

        //Unset some $_SESSIONs variables (selected operations for tables)
        $tbltmp = $data->getTables();
        
        $misc->printHeader($lang['strdatabase']);
        $misc->printBody();
        $misc->printTrail($_REQUEST['subject']);

        //check if current schema has tables, if not prints a message
        if ($tbltmp->recordCount() > 0 && isset($_REQUEST['app_id'])) {

            if ($_REQUEST['step'] == 0) {
                cleanWizardVars();
                $_REQUEST['step']++;
            }

            echo $misc->printTitle("{$lang['strstep']} {$_REQUEST['step']}");
            $nextstep = $_REQUEST['step'] + 1;
            echo "<script type=\"text/javascript\" src=\"pages.js\"></script>";
            echo "\n<form id=\"pages\" name=\"pages\" action=\"pages.php?{$misc->href}\" method=\"post\">";

            if ($_REQUEST['step'] == 1) {
                echo "<p>{$lang['strtbldetecttxt']}<br />";
                echo "<span style=\"font-style:italic;\">({$lang['stratbldetectwarn']})</span></p>";
                foreach ($tbltmp as $i) {
                    $attrs = $data->getTableAttributes($i['relname']);
                    /*
                     * Here  prints a list of tables from current schema,
                     * the user selects wich tables are going to be used by the
                     * application, then it sends the information to the operation page
                     * for processing
                     */
                    if ($attrs->recordCount() > 0) {
                        echo "\n\t\t\t<div  id=\"{$i['relname']}\" class=\"trail\" style=\"float:left;margin:5px;\">\n\t\t\t\t<h3>{$i['relname']}</h3>";
                        while (!$attrs->EOF) {
                            /*
                             * checks if can't be null so must be selected, due to problems with HTML
                             * i need to create a hidden value to send the value
                             */
                            echo "\n\t\t\t\t<input type=\"checkbox\" ";
                            if ($attrs->fields['attnotnull'] == 't') {
                                echo "name=\"chk-{$i['relname']}\" checked=\"checked\" disabled=\"disabled\"/>";
                                echo "\n\t\t\t\t<input type=\"hidden\"";
                            }
                            echo "name=\"field[{$i['relname']}][]\" value=\"{$attrs->fields['attname']}\"";
                            if (isset($_REQUEST['field'][$i['relname']])) {
                                foreach ($_REQUEST['field'][$i['relname']] as $column)
                                    if ($column == $attrs->fields['attname'])
                                        echo "checked=\"checked\"";
                            }
                            echo "/>&nbsp;";
                            echo $attrs->fields['attname'] . '<br />';
                            $attrs->moveNext();
                        }
                        echo "<br />{$lang['strselect']} <a href=\"#\" onclick=\"checkAllCheckboxes('{$i['relname']}', true); return false;\">{$lang['strselectall']}</a>";
                        echo " / <a href=\"#\" onclick=\"checkAllCheckboxes('{$i['relname']}', false); return false;\">{$lang['strunselectall']}</a>";
                        echo "\n\t\t\t</div>";
                    }
                }
            }
            /**
             * Prints the main table for selecting operations to columns
             */
            if ($_REQUEST['step'] == 2) {
                $first = false;
                $count = "0";
                $_SESSION['apptables'] = $_POST['field'];

                echo "<br />{$lang['stroperationtxt']}<br /><br />";
                foreach ($_REQUEST['field'] as $table_name => $table) {
                    if (count($table) > 0) {
                        //if receives a field from a new table prints a new table
                        if ($first == true) {
                            echo "<br />";
                            $first = true;
                        }
                        else
                            $first=true;
                        echo "<div style=\"width:60%;\" id=\"{$table_name}\" >";
                        echo "<table border=\"1\" width=\"100%\">\n\t<tr>\n\t\t<th class=\"data\">" . str_replace("'", "", $table_name) . "</th>\n\t</tr>\n\t<tr>\n\t\t<td>";
                        echo "\n\t\t\t<table width=\"100%\">\n\t\t\t\t<tr><th class=\"data\">{$lang['strname']}</th><th class=\"data\">{$lang['strreport']}</th><th class=\"data\">{$lang['strinsert']}</th>";
                        echo "<th class=\"data\">{$lang['strupdate']}</th><th class=\"data\">{$lang['strdelete']}</th>\n\t\t\t\t</tr>";

                        foreach ($table as $column) {
                            echo "\n\t\t\t\t<tr>\n\t\t\t\t\t<td " . rowClass($count) . ">{$column}\n\t\t\t\t\t</td>";
                            printOperationTableCell("browse", $count, $table_name, $column);
                            printOperationTableCell("insert", $count, $table_name, $column);
                            printOperationTableCell("update", $count, $table_name, $column);
                            printOperationTableCell("delete", $count, $table_name, $column);
                            echo "</tr>";
                            $count++;
                        }
                        echo "\n\t\t\t</table>\n\t\t</td>\n\t</tr>\n</table>";
                        echo "<p style=\"text-align:right;font-size:smaller;\">{$lang['strselect']} <a href=\"#js\" onclick=\"checkAllCheckboxes('{$table_name}', true); \">{$lang['strselectall']}</a>";
                        echo " / <a href=\"#js\" onclick=\"checkAllCheckboxes('{$table_name}', false);\">{$lang['strunselectall']}</a></p>";
                        echo "</div>";
                    }
                }
            }
            /**
             * 	Here it prints a list of detected pages  and ask the user
             *  to confirm this information o go back and make some changes
             */
            if ($_REQUEST['step'] == 3) {

                echo "<table><tr><td style=\"text-align:left;\" >";
                echo "<br />{$lang['strpagesdetected']}<br /><br />";

                //Saves selected operations in to a saession variable
                if (isset($_POST['browse'])
                    )$_SESSION['browse'] = $_POST['browse'];
                if (isset($_POST['insert'])
                    )$_SESSION['insert'] = $_POST['insert'];
                if (isset($_POST['update'])
                    )$_SESSION['update'] = $_POST['update'];
                if (isset($_POST['delete'])
                    )$_SESSION['delete'] = $_POST['delete'];

                //Adds external dependencies (like FK dependencies)
                addDependencies();

                //Prints a list of detected pages
                printDetectedPages("browse");
                printDetectedPages("insert");
                printDetectedPages("update");
                printDetectedPages("delete");
                echo "</td></tr></table>";
            }
            /*
             * Here stores detected pages into the DB
             */
            if ($_REQUEST['step'] == 4) {
                $app = new Application();
                $app->load($_REQUEST['app_id']);

                $operations = array("browse", "insert", "update", "delete");
                foreach ($operations as $operation) {
                    //Creates filename prefix
                    switch ($operation) {
                        case "browse": $prefix = "report_";
                            break;
                        case "insert": $prefix = "insert_";
                            break;
                        case "update": $prefix = "update_";
                            break;
                        case "delete": $prefix = "delete_";
                            break;
                    }

                    if (isset($_SESSION[$operation])) {
                        foreach ($_SESSION[$operation] as $table_name => $table) {
                            if (count($_SESSION[$operation]) > 0) {
                                $page_obj = new Pages();
                                $filename = $prefix . trim(str_replace("'", "", $table_name)) . ".php";

                                //Write generated code to file
                                if ($app->checkIfPageExists($filename)) {
                                    $num = 1;
                                    $or_filename = substr($filename, 0, -4);
                                    $filename = $or_filename . "-" . $num . ".php";
                                    while ($app->checkIfPageExists($filename)) {
                                        $num = $num + 1;
                                        $filename = $or_filename . "-" . $num . ".php";
                                    }
                                }

                                //Builds page object
                                $page_obj->setFilename($filename);
                                $page_obj->setOperation($operation);
                                $page_obj->setTitle($lang['strnone']);
                                $page_obj->setDescription('');
                                $page_obj->setTable(str_replace("'", "", $table_name));
                                $page_obj->setPageText('');

                                foreach ($table as $column) {
                                    //creates a field object
                                    $field_obj = new Fields();
                                    $field_obj->setName($column);
                                    $page_obj->addField($field_obj);
                                }

                                //Adds a page to application object, then creates a new Page object
                                $page_id = $page_obj->insert($_REQUEST['app_id']);
                                if ($page_id < 0) {
                                    $misc->printMsg($lang['strerrsavedb']);
                                    exit(1);
                                }
                                $table_id = $page_obj->saveTable($page_id);
                                if ($table_id < 0) {
                                    $misc->printMsg($lang['strerrsavedb']);
                                    exit(1);
                                }
                                $page_obj->saveFields($table_id);
                            }
                        }
                    }
                }
                $misc->printMsg($lang['strsaveappsuccessful']);
                echo "<p>{$lang['strappdatatxt']}</p>";
                echo "<ul class=\"navlink\">";
                echo "\n\t<li><a href=\"pages.php?action=list&amp;app_id={$_REQUEST['app_id']}&amp;{$misc->href}\">{$lang['strmanagepage']}</a></li>\n";
                echo "</ul>\n";
                cleanWizardVars();
            }

            echo "\n\t\t\t\t<input type=\"hidden\" name=\"app_id\" value=\"{$_REQUEST['app_id']}\" />";
            echo "\n\t\t\t\t<input type=\"hidden\" name=\"action\" value=\"wizard\" />";
            echo "\n\t\t\t\t<input type=\"hidden\" id=\"step\" name=\"step\" value=\"{$nextstep}\" />";
            echo "\n\t\t\t\t<div style=\"clear:both\"></div>";

            if (($_REQUEST['step'] > 1) && ($_REQUEST['step'] < 4)) {
                echo "\n\t\t\t<button onclick=\"goPreviousStep()\"> {$lang['strprev']}</button>\n";
                if (isset($_REQUEST['field'])) {
                    foreach ($_REQUEST['field'] as $table_name => $table) {
                        foreach ($table as $column)
                            echo "\n\t\t\t\t<input type=\"hidden\" name=\"field[{$table_name}][]\" value=\"{$column}\" />";
                    }
                }
            } //end printing input hidden fields*/
            if ($_REQUEST['step'] < 4)
                echo "\n\t\t\t<input type=\"submit\" value=\"{$lang['strcontinue']}\" />\n\t</form>\n";
        }
        else {
            //Print warning and offers a link for creating tables
            $this->print_no_tables();
        }
        
        $misc->printFooter();
    }


	function tree() {     
        
        global $misc, $data;

		$applications = Application::getAppsOfDB($_REQUEST['database'],$_REQUEST['schema']); 
		$reqvars = $misc->getRequestVars('crudgen');
        
        $url = url(
                    'plugin.php',
                    $reqvars,
                    array (
                        'plugin' => $this->name,
                        'action' => 'show_app',
                        'app_id' => field('app_id')
                    )
                );

		$attrs = array(
			'text'   => field('app_name'),
            'hide' => false,
			'icon'   =>  array('plugin' => $this->name, 'image' => 'CrudGen'),
			'iconAction' => $url,
			'toolTip'=> field('relcomment'),
			'action' => $url,
		);
        
		$misc->printTreeXML($applications, $attrs);
		exit;
	}
}
?>
