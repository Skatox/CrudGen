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
     * Builds an internal link array to simply code
     */
    function build_link($action){
        global $misc;
        
        return "plugin.php?plugin={$this->name}&amp;action={$action}"
               ."&amp;subject=crudgen&amp;{$misc->href}&amp;";
    }
    
    /**
     * Builds an external link array to simply code
     */
    function build_nav_link($url, $action, $content){
        return array (
		 		'attr'=> array ('href' => array(
                    'url' => $url,
                    'urlvars' => array (
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
     * Builds a plugin link array to simply code
     */
    function build_plugin_link($action, $content){
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
			'show_apps',
			'create_app',
            'edit_app',
            'save_app',
            'delete_app',
			'tree',
			'sub_tree'
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
						'subject' => 'crudgen', 
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
		
		if (isset($_REQUEST['subject'])) {
			$subject = $_REQUEST['subject'];
		}
        else {
            $subject = null;
        }

		if (in_array($subject, array('crudgen'))) {
			$url = array (
				'url' => 'plugin.php',
				'urlvars' => array (
					'plugin' => $this->name,
					'subject' => 'crudgen',
					'action' => 'show_apps'
				)
			);
			$trail['show_apps'] = array(
				'title' => $this->name,
				'text'  => $this->name,
				'url'   => $misc->printActionUrl($url, $_REQUEST, null, false),
				'icon' => array('plugin' => $this->name, 'image' => 'CrudGen')
			);
		}
	}

	/**
	 * Show a list of created apps
	 */
	function show_apps($msg = '') {
		global $lang, $misc;

		$misc->printHeader($lang['strdatabase']);
		$misc->printBody();
        $misc->printTrail($_REQUEST['subject']);
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
                'url' => "applications.php?{$misc->href}&amp;action=show&amp;",
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
                'url' => $this->build_link('wizard_app'),
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
            'select' => array(
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
           $rs = Application::getAppsOfDB($_REQUEST['database']);

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
        $misc->printTrail($_REQUEST['subject']);
        $misc->printTabs('schema','crudgen');
        
        //Loads tables and columns
        $tables = array();
        $tbltmp = $data->getTables();
        
        foreach ($tbltmp as $table)
            $tables[] = $table["relname"];

        if (count($tables) == 0) {
            //Print warning and offers a link for creating tables
            $misc->printMsg($lang['strnotables']);
            echo '<p>' . $this->lang['strerrnotbl'] . '</p>';
            
            $navlinks = array ( 
                            $this->build_nav_link('tables.php','create',$lang['strcreatetable']),
                            $this->build_nav_link('tables.php','createlike',$lang['strcreatetablelike']),
                        );
            $misc->printNavLinks($navlinks, 'create_app');
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
            echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n";
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
            $misc->printTrail($_REQUEST['subject']);
            $misc->printTabs('schema','crudgen');
            
            echo "\n\t<h2>{$lang['strdelete']}</h2>\n\t<p>{$this->lang['strconfdelapp']}</p>"
            . "\n\t<form method=\"post\">\n\t\t"
            . "\n\t\t<input type=\"hidden\" name=\"action\" value=\"delete_app\" />"
            . "\n\t\t<input type=\"hidden\" name=\"app_id\" value=\"{$_REQUEST["app_id"]}\" />"
            . "\n\t\t<input type=\"submit\" name=\"delete\" value=\"{$lang['strdelete']}\" />"
            . "\n\t\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />\n\t</form>";
            
            $misc->printFooter();
        } 
        else {
            $msg = (Application::delete($_REQUEST["app_id"]))? $this->lang['strerrdelapp'] : $this->lang['strdelapp'];
            $this->show_apps($msg);
        }
    }


	function tree() {
		global $misc;

		$reqvars = $misc->getRequestVars('show_schema_extension');
		$tabs = $misc->getNavTabs('show_schema_extension');
		$items = $misc->adjustTabsForTree($tabs);
		
		$attrs = array(
			'text'   => noEscape(field('title')),
			'icon'   => field('icon'),
			'action' => url(
				field('url'),
				$reqvars,
				field('urlvars', array())
			),
			'branch' => url('plugin.php',
				$reqvars,
				array (
					'action' => 'sub_tree',
					'plugin' => $this->name,
					'level' => field('level')
				)
			)
		);
		
		$misc->printTreeXML($items, $attrs);
		exit;
	}

	function sub_tree() {
		global $misc;

		$reqvars = $misc->getRequestVars($_REQUEST['level']);
		$tabs = $misc->getNavTabs($_REQUEST['level']);
		$items = $misc->adjustTabsForTree($tabs);
		$attrs = array(
			'text' => noEscape(field('title')),
			'icon' => field('icon'),
			'action' => url(
				field('url'),
				$reqvars,
				field('urlvars', array())
			),
		);
		$misc->printTreeXML($items, $attrs);
		exit;
	}
    
    /**
     * Prints options for a html combo-box and receives a value to select by default
     * @param $array an array with values for the combo box
     * @param $sel_value value of selected index
     * @return string with html code for options
     */
    function printSelOptions($array, $sel_value) {
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
    function checkAppDB() {
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
}
?>
