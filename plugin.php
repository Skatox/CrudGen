<?php

require_once('classes/Plugin.php');
include_once('plugins/CrudGen/classes/Application.php');
include_once('plugins/CrudGen/classes/Columns.php');
include_once('plugins/CrudGen/classes/Pages.php');
include_once('plugins/CrudGen/classes/Generator.php');

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
            if (count($schemas) == 0)
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
    private function include_js() {
        return "<script type=\"text/javascript\" src=\"plugins/{$this->name}/js/crudgen.js\"></script>";
    }

    /**
     * Builds an internal link array to simply code
     */
    private function build_link($action, $extra_vars = array()) {
        global $misc;

        $link = "plugin.php?plugin={$this->name}&amp;action={$action}"
                . "&amp;{$misc->href}&amp;";

        if (count($extra_vars))
            foreach ($extra_vars as $key => $val)
                $link .= "{$key}={$val}&amp;";

        return $link;
    }

    /**
     * Builds an external link array to simply code
     */
    private function build_nav_link($url, $action, $content, $extra_vars = array()) {
        $urlvars = array(
            'action' => $action,
            'server' => field('server'),
            'subject' => field('subject'),
            'database' => field('database'),
            'schema' => field('schema'),
        );

        return array(
            'attr' => array('href' => array('url' => $url, 'urlvars' => array_merge($urlvars, $extra_vars))),
            'content' => $content
        );
    }

    /**
     * Builds a plugin link array to simply code
     */
    private function build_plugin_link($action, $content) {
        return array(
            'attr' => array('href' => array(
                    'url' => 'plugin.php',
                    'urlvars' => array(
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
        unset($_SESSION['crudgen_apptables']);
        unset($_SESSION['crudgen_report']);
        unset($_SESSION['crudgen_create']);
        unset($_SESSION['crudgen_update']);
        unset($_SESSION['crudgen_delete']);
    }

    /**
     * Prints a message when there are no tables and offer the user to create them
     */
    private function print_no_tables() {
        global $misc, $lang;

        $misc->printMsg($lang['strnotables']);
        echo '<p>' . $this->lang['strerrnotbl'] . '</p>';

        $navlinks = array(
            $this->build_nav_link('tables.php', 'create', $lang['strcreatetable']),
            $this->build_nav_link('tables.php', 'createlike', $lang['strcreatetablelike']),
        );

        $misc->printNavLinks($navlinks, 'create_app');
    }

    /**
     * Prints a table cell with a checkbox for selecting an operation
     *
     * @param $operation the operation for this cell
     * @param $rowClass row's class name
     * @param $table_name name of the table where the field is
     * @param $field name of the column where the operation will be applied to
     * @param $selected variable to see if the checkbox is selected or not
     */
    private function printOperationTableCell($operation, $rowClass, $table_name, $field) {

        echo "\n\t\t\t\t\t<td class=\"{$rowClass}\" style=\"text-align: center;\">";
        echo "\n\t\t\t\t\t\t<input type=\"checkbox\" name=\"{$operation}[{$table_name}][]\" value=\"{$field}\"";
        if (isset($_SESSION[$operation]))
            if (isset($_SESSION[$operation][$table_name])) {
                if (count($_SESSION[$operation][$table_name]) > 0) {
                    foreach ($_SESSION[$operation][$table_name] as $operation) {
                        if ($operation == $field)
                            echo " checked";
                    }
                }
            }
        echo "/></td>";
    }

    /**
     * Print comboxes depending on how many fields works with this page
     * @param $page page to print its fields
     * @param $selected_field index of selected field
     */
    private function printOptionsField(Pages $page, $selected_field) {
        $fields_count = count($page->fields);

        for ($i = 1; $i <= $fields_count; $i+= 1) {
            echo "\n\t\t\t\t<option value=\"{$i}\"";
            if ($selected_field == $i)
                echo " selected=\"selected\"";
            echo">{$i}</option>";
        }
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
            'list_pages',
            'edit_page',
            'update_page',
            'delete_page',
            'generate_app',
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
                $tabs['crudgen'] = array(
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
        } else {
            $subject = null;
        }

        if (!empty($name)) {
            $url = array(
                'url' => 'plugin.php',
                'urlvars' => array(
                    'plugin' => $this->name,
                    'subject' => 'crudgen',
                    'action' => 'show_app',
                    'app_id' => $_REQUEST['app_id']
                )
            );
            $trail['show_app'] = array(
                'title' => 'View application\'s information',
                'text' => $name,
                'url' => $misc->printActionUrl($url, $_REQUEST, null, false),
                'icon' => array('plugin' => $this->name, 'image' => 'CrudGen')
            );

            //Changes schema's link
            $url = array(
                'url' => 'plugin.php',
                'urlvars' => array(
                    'plugin' => $this->name,
                    'subject' => 'schema',
                    'action' => 'show_apps',
                )
            );
            $trail['schema']['url'] = $misc->printActionUrl($url, $_REQUEST, null, false);
        }
    }

    /**
     * Show a single created app
     */
    function show_app($msg = '') {
        global $lang, $misc;

        $misc->printHeader($lang['strdatabase']);
        $misc->printBody();
        $misc->printTrail('schema');

        if (!empty($msg))
            $misc->printMsg($msg);

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
            'actions' => array(
                'title' => "Actions",
            ),
        );
        $actions = array(
            'list_pages' => array(
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
        );

        $rs = Application::getApplication($_REQUEST['app_id']);
        $misc->printTable($rs, $columns, $actions, null, $this->lang['strnoapps']);
        $extra_vars = array('app_id' => $_REQUEST['app_id'], 'plugin' => $this->name);

        $navlinks = array(
            $this->build_nav_link('plugin.php', 'app_wizard', $this->lang['straddpages'], $extra_vars),
            $this->build_nav_link('plugin.php', 'list_pages', $this->lang['strmanagepage'], $extra_vars),
            $this->build_nav_link('plugin.php', 'edit_app', $lang['stredit'], $extra_vars),
            $this->build_nav_link('plugin.php', 'delete_app', $lang['strdelete'], $extra_vars),
            $this->build_nav_link('plugin.php', 'generate_app', $this->lang['strgenerate'], $extra_vars),
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
        $misc->printTabs('schema', 'crudgen');


        unset($_SESSION["appid"]);

        $columns = array(
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
            'pages' => array(
                'title' => $this->lang['strpages'],
                'field' => field('pages'),
            ),
            'actions' => array(
                'title' => "Actions",
            ),
        );
        $actions = array(
            'multiactions' => array(
                'keycols' => array('app_id' => 'app_id'),
                'url' => $this->build_link('show_apps'),
                'default' => 'delete'
            ),
            'wizard' => array(
                'title' => $this->lang['straddpages'],
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
                'multiaction' => 'delete_app'
            ),
            'generate' => array(
                'title' => $this->lang['strgenerate'],
                'url' => $this->build_link('generate_app'),
                'vars' => array('app_id' => 'app_id'),
            ),
        );

        if ($this->checkAppDB()) {     //Checks if appgen db was installed
            $rs = Application::getApps($_REQUEST['database'], $_REQUEST['schema']);

            if (!empty($msg))
                $misc->printMsg($msg);

            $misc->printTable($rs, $columns, $actions, null, $this->lang['strnoapps']);
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
    function create_app($msg = '') {
        global $data, $misc, $lang;

        $misc->printHeader($lang['strdatabase']);
        $misc->printBody();
        $misc->printTrail('schema');
        $misc->printTabs('schema', 'crudgen');

        //Loads tables and columns
        $tables = array();
        $tbltmp = $data->getTables();

        foreach ($tbltmp as $table)
            $tables[] = $table["relname"];

        if (count($tables) == 0) {
            //Print warning and offers a link for creating tables
            $this->print_no_tables();
        } else {
            $app = new Application();
            $columns = array();
            $server_info = $misc->getServerInfo();

            if (!isset($_REQUEST['name']))
                $_REQUEST['name'] = '';
            if (!isset($_REQUEST['descr']))
                $_REQUEST['descr'] = '';
            if (!isset($_REQUEST['theme']))
                $_REQUEST['theme'] = 'default';
            if (!isset($_REQUEST['db_host']))
                $_REQUEST['db_host'] = '127.0.0.1';
            if (!isset($_REQUEST['db_name']))
                $_REQUEST['db_name'] = '';
            if (!isset($_REQUEST['db_port']))
                $_REQUEST['db_port'] = '5432';
            if (!isset($_REQUEST['db_user']))
                $_REQUEST['db_user'] = $server_info["username"];
            if (!isset($_REQUEST['db_pass']))
                $_REQUEST['db_pass'] = '';
            if (!isset($_REQUEST['auth_method']))
                $_REQUEST['auth_method'] = 'none';
            if (!isset($_REQUEST['auth_table']))
                $_REQUEST['auth_table'] = $tables[0];
            if (!isset($_REQUEST['auth_user_col']))
                $_REQUEST['auth_user_col'] = '';
            if (!isset($_REQUEST['auth_pass_col']))
                $_REQUEST['auth_pass_col'] = '';

            //Loads columns
            $coltmp = $data->getTableAttributes($_REQUEST["auth_table"]);

            if ($coltmp->recordCount() > 0) {
                while (!$coltmp->EOF) {
                    $columns[] = $coltmp->fields['attname'];
                    $coltmp->moveNext();
                }
            }

            if (empty($_REQUEST['app_id']))
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
                $submit_caption = $lang['strcreate'];

            echo "<input type=\"submit\" name=\"vacuum\" value=\"{$submit_caption}\" />\n";
            echo "<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" >\n";
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

        if (!empty($_REQUEST['cancel']))
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

        if (!isset($_REQUEST['auth_method'])) {
            $_REQUEST['auth_method'] = 'none';
        } elseif ($_REQUEST['auth_method'] != 'dbtable') {
            $_REQUEST['auth_table'] = '';
            $_REQUEST['auth_user_col'] = '';
            $_REQUEST['auth_pass_col'] = '';
        }

        $app->setAttributes();
        $unique_name = $app->hasUniqueName();

        if (!$unique_name) {
            return $this->create_app($this->lang['strnouniquename']);
        }

        if ($app->save()) {
            $msg = (empty($_REQUEST['app_id'])) ? $this->lang['strappsaved'] : $this->lang['strappedited'];
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

        if (!empty($_REQUEST['cancel']))
            return $this->show_apps();

        if (!isset($_REQUEST["app_id"]) && !isset($_REQUEST['ma'])) {
            $this->show_apps($this->lang['strselapptodelete']);
            return;
        }


        if (!isset($_POST['delete'])) {
            $misc->printHeader($lang['strdatabase']);
            $misc->printBody();
            $misc->printTrail('schema');
            $misc->printTabs('schema', 'crudgen');

            $delete_text = isset($_REQUEST['ma']) ? $this->lang['strconfdelapps'] : $this->lang['strconfdelapp'];

            echo "\n\t<h2>{$lang['strdelete']}</h2>\n\t<p>{$delete_text}</p>"
            . "\n\t<form method=\"post\" style=\"float:left; margin-right: 5px;\">\n\t\t"
            . "\n\t\t<input type=\"hidden\" name=\"action\" value=\"delete_app\" />";

            //If multi drop
            if (isset($_REQUEST['ma'])) {
                foreach ($_REQUEST['ma'] as $a) {
                    $app = unserialize(htmlspecialchars_decode($a, ENT_QUOTES));
                    echo '<input type="hidden" name="app_id[]" value="', htmlspecialchars($app['app_id']), "\" />\n";
                }
            } else {
                if (isset($_REQUEST["app_id"]))
                    echo "\n\t\t<input type=\"hidden\" name=\"app_id\" value=\"{$_REQUEST["app_id"]}\" />";
            }

            echo "\n\t\t<input type=\"submit\" name=\"delete\" value=\"{$lang['strdelete']}\" />"
            . "\n\t\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\"  />\n\t</form>";

            $misc->printFooter();
        } else {
            if (is_array($_POST['app_id'])) {
                $flag = 0;

                foreach ($_POST['app_id'] as $app_id) {
                    $flag = Application::delete($app_id);

                    if ($flag === 1) {
                        $msg = $this->lang['strerrdelapp'];
                        break;
                    }
                }
                if ($flag == 0)
                    $msg = $this->lang['strdelapps'];
            }
            else
                $msg = (Application::delete($_REQUEST["app_id"])) ? $this->lang['strerrdelapp'] : $this->lang['strdelapp'];

            $this->show_apps($msg);
        }
    }

    /*
     * This function checks for dependencies (for FK) of selected tables
     * for inserting data and adds it on the $_SESSION array of detected pages
     */

    function addDependencies() {
        global $data;

        //Retrieves all tables in the current schema
        $tbls_db = $data->getTables();
        $tbls_4_ins = array();

        if (!isset($_SESSION["crudgen_create"]))
            return;

        foreach ($_SESSION["crudgen_create"] as $table_name => $table) {
            $tbls_4_ins[] = $table_name;

            $attrs = $data->getTableAttributes($table_name);
            if ($attrs->recordCount() > 0) {
                while (!$attrs->EOF) {

                    if (isset($_SESSION['crudgen_create'])) {
                        if (($attrs->fields['attnotnull'] == 't') && (in_array($attrs->fields['attname'], $_SESSION['crudgen_create'][$table_name]) == false)) {
                            $_SESSION['crudgen_create'][$table_name][] = $attrs->fields['attname'];
                        }
                    }
                    $attrs->moveNext();
                }
            }
        }

        foreach ($tbls_db as $table) {
            //sees if the main tables wasn't selected for insertion
            if (in_array($table['relname'], $tbls_4_ins) == false) {
                $dependencies = $data->getReferrers($table['relname']);

                if ($dependencies !== -99 && $dependencies->recordCount() > 0) {
                    while (!$dependencies->EOF) {

                        //check if dependency is selected for insertion
                        if (in_array($dependencies->fields['relname'], $tbls_4_ins) == true) {
                            $attrs = $data->getTableAttributes($table['relname']);
                            if ($attrs->recordCount() > 0) {
                                while (!$attrs->EOF) {
                                    if ($attrs->fields['attnotnull'] == 't') {
                                        $_SESSION['crudgen_create'][$table['relname']][] = $attrs->fields['attname'];
                                    }
                                    $attrs->moveNext();
                                }
                            }
                        }
                        $dependencies->movenext();
                    }
                }
            }
        }
    }

    /**
     * Prints information about a detected pages for a operation
     * @param $operation string with operation to list its pages
     */
    function printDetectedPages($operation) {
        global $lang, $misc;

        $lang_index = "str{$operation}pages";

        //Creates file prefix
        switch ($operation) {
            case "report": $prefix = "report_";
                break;
            case 'create': $prefix = "create_";
                break;
            case "update": $prefix = "update_";
                break;
            case "delete": $prefix = "delete_";
                break;
            default: $prefix = '';
                break;
        }
        echo "<h3>{$this->lang["$lang_index"]}</h3>\n";

        if (isset($_SESSION['crudgen_' . $operation])) {
            if (count($_SESSION['crudgen_' . $operation]) > 0) {
                foreach ($_SESSION['crudgen_' . $operation] as $table_name => $table) {
                    if (count($table) > 0) {
                        echo "\n\t<p class=\"data\">{$this->lang["strthefile"]} ";
                        echo "<strong>{$prefix}" . trim(str_replace("'", "", $table_name)) . ".php</strong> ";
                        echo "{$this->lang["strfilecreation"]}<em> ";
                        echo (count($table) == 1) ? $table[0] : implode(', ', $table);
                        echo "\n\t</em></p>";
                    }
                }
            }
            else
                $misc->printMsg($this->lang['strnone']);
        }
        else
            $misc->printMsg($this->lang['strnone']);
    }

    /*
     * This prints the multi-step form to add as many pages as the operations requires
     */

    function app_wizard() {
        global $data, $misc, $lang;

        //Unset some $_SESSIONs variables (selected operations for tables)
        $tbltmp = $data->getTables(true);

        if (!isset($_REQUEST['step']))
            $_REQUEST['step'] = 0;


        //check if current schema has tables, if not prints a message
        if ($tbltmp->recordCount() > 0 && isset($_REQUEST['app_id'])) {

            if ($_REQUEST['step'] == 0) {
                $this->cleanWizardVars();
                $_REQUEST['step']++;
            }

            $nextstep = $_REQUEST['step'] + 1;

            if ($_REQUEST['step'] < 4) {
                $misc->printHeader($lang['strdatabase']);
                $misc->printBody();
                $misc->printTrail('schema');

                echo $misc->printTitle("{$this->lang['strstep']} {$_REQUEST['step']}");
                echo "\n<form id=\"pages\" name=\"pages\" action=\"\" method=\"post\">";
            }

            switch ($_REQUEST['step']) {
                case 1:
                    echo "<p>{$this->lang['strtbldetect']}</p>";
                    echo "<p><span style=\"font-style:italic;\">({$this->lang['stratbldetectwarn']})</span></p>";

                    foreach ($tbltmp as $i) {
                        $attrs = $data->getTableAttributes($i['relname']);
                        /*
                         * Here  prints a list of tables from current schema,
                         * the user selects wich tables are going to be used by the
                         * application, then it sends the information to the operation page
                         * for processing
                         */
                        if ($attrs->recordCount() > 0) {
                            echo "\n\t\t\t<div  id=\"table_{$i['relname']}\" class=\"trail\" style=\"float:left;margin:5px;\">\n\t\t\t\t<h3>{$i['relname']}</h3>";
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
                            echo "<p>{$lang['strselect']} <a href=\"#\" onclick=\"checkAllCheckboxes('table_{$i['relname']}', true); return false;\">{$this->lang['strall']}</a>";
                            echo " | <a href=\"#\" onclick=\"checkAllCheckboxes('table_{$i['relname']}', false); return false;\">{$this->lang['strnone']}</a></p>";
                            echo "\n\t\t\t</div>";
                        }
                    }
                    break; //end case 1

                /**
                 * Prints the main table for selecting operations to columns
                 */
                case 2:
                    $first = false;
                    $_SESSION['apptables'] = $_POST['field'];

                    echo "<br />{$this->lang['strseloperation']}<br /><br />";
                    foreach ($_REQUEST['field'] as $table_name => $table) {
                        if (count($table) > 0) {
                            //if receives a field from a new table prints a new table
                            $first = true;
                            $th_style = " style=\"padding:1px 10px\" ";
                            echo "<div id=\"table_{$table_name}\" style=\"float:left;padding:5px;\">";
                            echo "<table border=\"1\">\n\t<tr>\n\t\t<th class=\"data\">" . str_replace("'", "", $table_name) . "</th>\n\t</tr>\n\t<tr>\n\t\t<td>";
                            echo "\n\t\t\t<table>\n\t\t\t\t<tr><th class=\"data\">{$lang['strname']}</th>";
                            echo "<th class=\"data\"{$th_style}>{$lang['strcreate']}</th>";
                            echo "<th class=\"data\" {$th_style} >{$lang['strreport']}</th>";
                            echo "<th class=\"data\"{$th_style}>{$lang['strupdate']}</th>\n\t\t\t\t</tr>";

                            $rowClass = 'data1';
                            foreach ($table as $column) {
                                echo "\n\t\t\t\t<tr>\n\t\t\t\t\t<td style=\"padding:1px 5px\" class=\"{$rowClass}\">{$column}\n\t\t\t\t\t</td>";
                                $this->printOperationTableCell("crudgen_create", $rowClass, $table_name, $column);
                                $this->printOperationTableCell("crudgen_report", $rowClass, $table_name, $column);
                                $this->printOperationTableCell("crudgen_update", $rowClass, $table_name, $column);
                                echo "</tr>";
                                $rowClass = $rowClass == 'data1' ? 'data2' : 'data1';
                            }
                            echo "\n\t\t\t</table>\n\t\t</td>\n\t</tr>\n</table>";
                            echo "<p style=\"font-size:smaller;\">{$lang['strselect']} <a href=\"#js\" onclick=\"checkAllCheckboxes('table_{$table_name}', true); \">{$this->lang['strall']}</a>";
                            echo " / <a href=\"#js\" onclick=\"checkAllCheckboxes('table_{$table_name}', false);\">{$this->lang['strnone']}</a></p>";
                            echo "</div>";
                        }
                    }
                    break; //end case 2

                /**
                 * 	Here it prints a list of detected pages  and ask the user
                 *  to confirm this information o go back and make some changes
                 */
                case 3:

                    echo "<table><tr><td style=\"text-align:left;\" >";
                    echo "<br />{$this->lang['strpagesdetected']}<br /><br />";

                    //Saves selected operations in to a session variable
                    if (isset($_POST['crudgen_report']))
                        $_SESSION['crudgen_report'] = $_POST['crudgen_report'];
                    if (isset($_POST['crudgen_create']))
                        $_SESSION['crudgen_create'] = $_POST['crudgen_create'];
                    if (isset($_POST['crudgen_update']))
                        $_SESSION['crudgen_update'] = $_POST['crudgen_update'];

                    //Adds external dependencies (like FK dependencies)
                    $this->addDependencies();

                    //Prints a list of detected pages
                    $this->printDetectedPages('report');
                    $this->printDetectedPages('create');
                    $this->printDetectedPages('update');

                    echo "</td></tr></table>";
                    break;

                /*
                 * Here stores detected pages into the DB
                 */
                case 4:
                    $app = new Application();
                    $app->load($_REQUEST['app_id']);
                    $operations = array("report", 'create', "update", "delete");

                    foreach ($operations as $operation) {

                        //Creates filename prefix
                        switch ($operation) {
                            case "report": $prefix = "report_";
                                break;
                            case 'create': $prefix = "create_";
                                break;
                            case "update": $prefix = "update_";
                                break;
                            case "delete": $prefix = "delete_";
                                break;
                        }

                        if (isset($_SESSION['crudgen_' . $operation])) {
                            foreach ($_SESSION['crudgen_' . $operation] as $table_name => $table) {
                                if (count($_SESSION['crudgen_' . $operation]) > 0) {
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
                                    $page_obj->setTable(str_replace("'", "", $table_name));

                                    foreach ($table as $column) {
                                        //creates a field object
                                        $field = new Columns();
                                        $field->setName($column);
                                        $page_obj->addField($field);
                                    }

                                    //Adds a page to application object, then creates a new Page object
                                    $page_id = $page_obj->insert($_REQUEST['app_id']);
                                    if ($page_id < 0) {
                                        $misc->printMsg($this->lang['strerrorappsavedb']);
                                        exit(1);
                                    }

                                    $table_id = $page_obj->saveTable($page_id);
                                    if ($table_id < 0) {
                                        $misc->printMsg($this->lang['strerrorappsavedb']);
                                        exit(1);
                                    }

                                    $page_obj->saveColumns($table_id);
                                }
                            }
                        }
                    }

                    $this->cleanWizardVars();
                    $this->show_app($this->lang['strsavepagessuccessful']);
                    return;
            }

            echo "\n\t\t\t\t<input type=\"hidden\" name=\"app_id\" value=\"{$_REQUEST['app_id']}\" />";
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
                echo "\n\t\t\t<input type=\"submit\" value=\"{$lang['strnext']}\" />\n\t</form>\n";
        }
        else {
            //Print warning and offers a link for creating tables
            $this->print_no_tables();
        }

        echo $this->include_js();
        $misc->printFooter();
    }

    /**
     * Function to print a list of application's pages
     */
    function list_pages($msg = '') {
        global $lang, $misc;

        $app_id = $_REQUEST['app_id'];
        $pages = Pages::getApplicationPages($app_id, $this->lang);

        $misc->printHeader($lang['strdatabase']);
        $misc->printBody();
        $misc->printTrail('schema');
        $misc->printTabs('schema', 'applications');

        if (!empty($msg))
            $misc->printMsg($msg);

        $extra_vars = array('app_id' => $_REQUEST['app_id']);
        $columns = array(
            'name' => array(
                'title' => $lang['strname'],
                'field' => field('page_title'),
            ),
            'filename' => array(
                'title' => $this->lang['strfilename'],
                'field' => field('page_filename'),
            ),
            'op' => array(
                'title' => $this->lang['stroperation'],
                'field' => field('operation'),
            ),
            'created' => array(
                'title' => $lang['strcreated'],
                'field' => field('date_created'),
            ),
            'actions' => array(
                'title' => "Actions",
            ),
        );

        $actions = array(
            'multiactions' => array(
                'keycols' => array('page_id' => 'page_id'),
                'url' => $this->build_link('list_pages', $extra_vars),
                'default' => 'delete'
            ),
            'edit' => array(
                'title' => $lang['stredit'],
                'url' => $this->build_link('edit_page', $extra_vars),
                'vars' => array('page_id' => 'page_id'),
            ),
            'delete' => array(
                'title' => $lang['strdelete'],
                'url' => $this->build_link('delete_page', $extra_vars),
                'vars' => array('page_id' => 'page_id'),
                'multiaction' => 'delete_page',
            ),
        );

        $misc->printTable($pages, $columns, $actions, null, $this->lang['strnopages']);

        $extra_vars = array('app_id' => $_REQUEST['app_id'], 'plugin' => $this->name);
        $navlinks = array(
            $this->build_nav_link('plugin.php', 'app_wizard', $this->lang['straddpages'], $extra_vars),
        );

        if ($pages->_numOfRows > 0)
            $navlinks[] = $this->build_nav_link('plugin.php', 'generate_app', $this->lang['strgenerate'], $extra_vars);

        $misc->printNavLinks($navlinks, 'list_pages');
        $misc->printFooter();
    }

    /**
     * Function to add a new page to the application 
     */
    function edit_page($msg = '') {
        global $lang, $misc, $data;

        $app_id = $_REQUEST['app_id'];
        $page_id = $_REQUEST['page_id'];

        $app = new Application();
        $app->load($app_id);

        $page = new Pages();
        $page->load($page_id);
        $page->buildPost();

        $misc->printHeader($lang['strdatabase']);
        $misc->printBody();
        $misc->printTrail('schema');
        $misc->printTabs('schema', 'applications');

        $misc->printTitle($app->getName());


        if (!empty($msg))
            $misc->printMsg($msg);

        echo "\n<form name=\"editpageform\" method=\"post\" action=\"\">";
        echo "\n<table>\n\t<tr><th class=\"data\">{$this->lang['strpageinfo']}</th></tr>\n\t<tr><td>";
        echo "\n<table>\n\t<tr>\n\t<th class=\"data left\"> {$lang['strtable']}</th>";
        echo "<td class=\"data required\">" . $page->getTable() . "</td></tr>";
        echo "\n\t<tr>\n\t<th class=\"data left\"> {$this->lang['stroperation']}</th>";
        echo "<td class=\"data\">";
        switch ($page->operation) {
            case "create":
                echo $this->lang['strcreate'];
                break;
            case "update":
                echo $this->lang['strupdate'];
                break;
            case "report":
                echo $this->lang['strreport'];
                break;
            case "delete":
                echo $this->lang['strdelete'];
                break;
        }
        echo "</td></tr>";
        echo "\n\t<tr>\n\t<th class=\"data left required\"> {$this->lang['strpagetitle']}</th>";
        echo "<td class=\"data\"><input type=\"text\" name=\"page_title\" maxlength=\"255\" value=\"{$_POST['page_title']}\"  size=\"33\"  /></td></tr>";
        echo "\n\t<tr>\n\t<th class=\"data left required\">{$this->lang['strfilename']}</th>";
        echo "<td class=\"data\"><input type=\"text\" name=\"page_filename\" maxlength=\"255\" value=\"";
        echo isset($_POST['page_filename']) ? $_POST['page_filename'] : $page->getFilename();
        echo "\"  size=\"33\" /></td></tr>";
        echo "\n\t<tr>\n\t<th class=\"data left\">{$this->lang['strpagemainmenu']}</th>";
        echo "<td class=\"data\"><input type=\"checkbox\" name=\"on_main_menu\" id=\"on_main_menu\" value=\"selected\"";
        if (isset($_POST['on_main_menu']['selected']))
            echo " checked=\"checked\"";
        echo "/>&nbsp;&nbsp;<label for=\"on_main_menu\">{$this->lang['strpageonmainmenu']}</label></td></tr>";
        echo "\n\t<tr>\n\t<th class=\"data left\">{$this->lang['strdescr']}</th>";
        echo "<td class=\"data\"><textarea name=\"page_descr\" rows=\"3\" cols=\"33\" style=\"overflow:auto;\">{$_POST["page_descr"]}</textarea></td></tr>";
        echo "\n\t<tr>\n\t<th class=\"data left\">{$this->lang['strpagecontent']}</th>";
        echo "<td class=\"data\"><textarea name=\"page_text\" rows=\"3\" cols=\"33\" style=\"overflow:auto;\">{$_POST["page_text"]}</textarea></td></tr>";
        echo "\n</table></td></tr></table>";

        /*
         * Prints specific parameters (depedending of operation)
         * delete pages doesn't require filed parameters because they only deletes information
         */
        echo "\n<table id=\"field-table\">\n\t<tr><th class=\"data\">{$this->lang['strorder']}</th></tr>";
        echo "\n\t<tr><td>";

        //Prints inputs, and checkboxes for each column/field
        echo "<table><tr><th class=\"data\">{$this->lang['strfieldname']}</th><th class=\"data\">{$this->lang['strdisplayname']}</th>";
        echo "<th class=\"data\">{$this->lang['strpriority']}</th><th class=\"data\">{$this->lang['strshowinpage']}</th><th class=\"data\">{$this->lang['strremotecol']}</th></tr>";

        foreach ($page->fields as $field) {
            $fk_table = $field->getFkTables($app->getDBName(), $app->getSchema(), $page->getTable());
            //Prints field data
            echo "\n\t\t\t<tr><th class=\"data left\">{$field->getName()}</th>";
            echo "\n\t\t\t<td><input type=\"text\" name=\"display[" . $field->getName() . "]\" maxlength=\"255\" value=\"";
            echo isset($_POST['display'][$field->getName()]) ? $_POST['display'][$field->getName()] : $field->getName();
            echo "\"  /></td>";
            echo "\n\t\t\t<td><select name=\"order[" . $field->getName() . "]\">";

            if (isset($_POST['order'][$field->getName()]))
                $this->printOptionsField($page, $_POST['order'][$field->getName()]);
            else
                $this->printOptionsField($page, $field->getOrder());

            echo "</select></td>";

            //Checkbox
            echo "\n\t\t\t<td style=\"text-align:center;\"><input type=\"checkbox\" name=\"show[{$field->getName()}]\" value=\"selected\"";

            if ($page->operation == "create") {
                $attrs = $data->getTableAttributes($page->getTable());
                $nn = false;
                while (!$attrs->EOF) {
                    if (($attrs->fields['attnotnull'] == 't') && ($attrs->fields['attname'] == $field->getName()) &&
                            ($attrs->fields['attname'] != Generator::getPK($app->getDBName(), $page->getTable()))) {
                        echo " checked=\"checked\" disabled=\"disabled\" />";
                        echo "<input type=\"hidden\" name=\"show[{$field->getName()}]\" value=\"selected\"";
                        $nn = true;
                    }
                    $attrs->moveNext();
                }
                if (($nn == false) && (!empty($_POST["show"][$field->getName()]) || ($field->isOnPage() == "t"))) {
                    echo " checked=\"checked\"";
                }
                echo " />";
            } else {
                if (!empty($_POST["show"][$field->getName()]) || ($field->isOnPage() == "t")) {
                    echo " checked=\"checked\"";
                }
                echo "/>";
            }

            echo "</td><td>";
            //Means $field is a FK
            if ($fk_table != -1) {
                $first_entry = null;

                echo "<select style=\"width:100%;\"name=\"fk_field[{$field->getName()}][]\">";
                $first_entry = ($page->operation == "report") || ($page->operation == "delete") ? $this->lang['strfkvalue'] : $this->lang['strmaninp'];
                echo "\n\t\t\t\t<option value=\"{$first_entry}\">{$first_entry}</option>\n";

                //Builds an array with table's column's name
                $attrs = $data->getTableAttributes($fk_table);
                if ($attrs->recordCount() > 0) {
                    while (!$attrs->EOF) {
                        echo "\n\t\t\t\t<option value=\"{$attrs->fields['attname']}";
                        if (isset($_POST["fk_field"][$field->getName()][0]))
                            if ($_POST["fk_field"][$field->getName()][0] == $attrs->fields['attname'])
                                echo "selected=\"selected\"";
                        echo "\">{$attrs->fields['attname']}</option>\n";
                        $attrs->moveNext();
                    }
                }
                echo "</select>";
                echo "\n<input type=\"hidden\" name=\"fk_table[{$field->getName()}]\" value=\"{$fk_table}\"/>\n ";
            }
            else
                echo $this->lang['strnone'];
            echo "</td></tr>";
        }
        echo "<tr><td colspan=\"2\"><p>{$lang['strshow'] } ";
        echo "<a href=\"#js\" onclick=\"checkAllCheckboxes('field-table',true)\">{$lang['strselectall']}</a>/";
        echo "<a href=\"#js\" onclick=\"checkAllCheckboxes('field-table',false)\">{$lang['strunselectall']}</a></p></td> </tr>";

        echo"\n\t\t</table>\n\t</td></tr></table>";
        echo "\n\t<input type=\"hidden\" name=\"app_id\" value=\"{$app_id}\" />";
        echo "\n\t<input type=\"hidden\" name=\"page_id\" value=\"{$page_id}\" />";
        echo "\n\t<input type=\"hidden\" name=\"action\" value=\"update_page\" />";
        echo "\n\t<input type=\"submit\" name=\"sendbutton\" value=\"{$lang['strsave']}\" />";
        echo "\n\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\" />";
        echo "</form>";
        echo $this->include_js();
        $misc->printFooter();
    }

    function update_page() {
        global $lang;

        if (!empty($_REQUEST['cancel']))
            return $this->list_pages();

        $page = new Pages();

        //Update common parameters
        $page->load($_REQUEST['page_id']);
        $page->setTitle($_POST['page_title']);
        $page->setFilename($_POST['page_filename']);
        $page->setDescription($_POST["page_descr"]);
        $page->setPageText($_POST["page_text"]);

        $page->setInMainMenu(false);

        if (isset($_POST['on_main_menu'])) {
            if ($_POST['on_main_menu'] == "selected")
                $page->setInMainMenu(true);
        }

        $validated = $page->validate($this->lang);

        if ($validated === true) {
            //Checks if filename is unique
            $app = new Application();
            $app->load($_REQUEST['app_id']);

            if ($app->isUniqueFilename($_REQUEST['page_id'], $page->getFilename())) {

                //Checks if there is a field with empty text
                if (isset($_POST['display'])) {
                    foreach ($_POST['display'] as $field) {
                        if (empty($field)) {
                            $this->edit_page($this->lang['strnodisplayname']);
                            return;
                        }
                    }
                }

                //Saves application object at the DB
                $msg = $page->update() >= 0 ? $this->lang['strsavepagesuccessful'] : $this->lang['strpageerrsavedb'];

                //Saves fields
                foreach ($page->fields as $field) {
                    $field->setDisplayName($_POST["display"][$field->getName()]);
                    $field->setOrder($_POST["order"][$field->getName()]);
                    $field->setOnPage(false);

                    if (isset($_POST["show"][$field->getName()])) {
                        if ($_POST["show"][$field->getName()] == "selected")
                            $field->setOnPage(true);
                    }

                    //Checks if it is a FK and adds FK data
                    if (isset($_POST["fk_field"][$field->getName()])) {
                        if (($_POST["fk_field"][$field->getName()][0] != $this->lang['strmaninp']) &&
                                ($_POST["fk_field"][$field->getName()][0] != $this->lang['strfkvalue'])) {
                            $field->setRemoteField($_POST["fk_field"][$field->getName()][0]);
                            $field->setRemoteTable($_POST["fk_table"][$field->getName()]);
                            $FKexist = true;
                        } else {
                            $field->setRemoteField("");
                            $field->setRemoteTable("");
                            $FKexist = false;
                        }
                    }
                    if ($field->update() == -1) {
                        $this->edit_page($this->lang['strerrfielddb']);
                        return;
                    }
                }

                $this->list_pages($msg);
            } else {
                $this->edit_page($this->lang['strnouniquefilename']);
            }
        } else {
            $this->edit_page($validated);
        }
    }

    /**
     * Function to delete a page
     */
    function delete_page() {
        global $misc, $lang;


        if (!empty($_REQUEST['cancel']))
            return $this->list_pages();

        if (!isset($_REQUEST["page_id"]) && !isset($_REQUEST['ma'])) {
            $this->list_pages($this->lang['strselpagetodelete']);
            return;
        }

        if (!isset($_POST['delete'])) {
            $misc->printHeader($lang['strdatabase']);
            $misc->printBody();
            $misc->printTrail('schema');
            $misc->printTabs('schema', 'crudgen');

            $confirmation_text = isset($_REQUEST['ma']) ? $this->lang['strdelpages'] : $this->lang['strdelpage'];

            echo "\n\t<h2>{$lang['strdelete']}</h2>\n\t<p>{$confirmation_text}</p>"
            . "\n\t<form method=\"post\" style=\"float:left; margin-right: 5px;\">\n\t\t"
            . "\n\t\t<input type=\"hidden\" name=\"action\" value=\"delete_page\" />";

            //If multi drop
            if (isset($_REQUEST['ma'])) {
                foreach ($_REQUEST['ma'] as $p) {
                    $page = unserialize(htmlspecialchars_decode($p, ENT_QUOTES));
                    echo '<input type="hidden" name="page_id[]" value="', htmlspecialchars($page['page_id']), "\" />\n";
                }
            } else {
                if (isset($_REQUEST["page_id"]))
                    echo "\n\t\t<input type=\"hidden\" name=\"page_id\" value=\"{$_REQUEST["page_id"]}\" />";
            }

            echo "\n\t\t<input type=\"submit\" name=\"delete\" value=\"{$lang['strdelete']}\" />"
            . "\n\t\t<input type=\"submit\" name=\"cancel\" value=\"{$lang['strcancel']}\"  />\n\t</form>";

            $misc->printFooter();
        } else {
            if (is_array($_POST['page_id'])) {
                $flag = 0;

                foreach ($_POST['page_id'] as $page_id) {
                    $flag = Pages::delete($page_id);

                    if ($flag === 1) {
                        $msg = $this->lang['strerrdelpage'];
                        break;
                    }
                }
                if ($flag == 0)
                    $msg = $this->lang['strdeletedpages'];
            }
            else
                $msg = ( Pages::delete($_REQUEST["page_id"]) == 0) ? $this->lang['strdeletedpage'] : $this->lang['strerrdelpage'];

            $this->list_pages($msg);
        }
    }

    function generate_app() {
        global $lang, $misc;

        if (!isset($_REQUEST["app_id"]))
            return $this->show_apps($this->lang['strerrnoappid']);
        
        if(isset($_REQUEST['btnCancel']))
            return $this->show_app ();

        $download_files = isset($_REQUEST['download']);
        $app_theme = isset($_REQUEST['app_theme']) ? $_REQUEST['app_theme'] : 'default';
        $app_id = $_REQUEST['app_id'];

        $app = new Application();
        $app->load($app_id);

        if (!$download_files) {

            $themes = Generator::getThemes();

            $misc->printHeader($lang['strdatabase']);
            $misc->printBody();
            $misc->printTrail('schema');
            $misc->printTabs('schema', 'applications');

            $misc->printTitle($this->lang['strgenerating'] . ' ' . $app->getName());

            echo "<form id=\"genops\" method=\"post\" action=\"\">\n";
            echo "\n\t\t<input type=\"hidden\" name=\"action\" value=\"generate_app\" />";
            echo "<table>\n";
            echo "\t<tr>\n\t\t<th class=\"data left required\">{$this->lang['strphplibrary']}</th>\n";
            echo "\t\t<td class=\"data\">";
            echo "<select name=\"app_library\">";
            echo "<option value=\"pgsql\">{$this->lang['strpgsql']}</option>";
            echo "<option value=\"pdo\">{$this->lang['strpdo']}</option>";
            echo "</select></td></tr>";
            echo "\t<tr>\n\t\t<th class=\"data left required\">{$this->lang['strtheme']}</th>\n";
            echo "\t\t<td class=\"data\">";
            echo "<select id=\"app_theme\" name=\"app_theme\" onchange=\"updatePreview()\" >";

            foreach ($themes as $theme) {
                echo "<option ";
                echo $app_theme == $theme ? ' selected="selected" ' : '';
                echo ">{$theme}</option>";
            }

            echo "\n\t\t</td></tr>";

            echo "\t<tr>\n\t\t<th class=\"data left\">{$this->lang['strpreview']}</th>\n";
            echo "\t<td><img id=\"thumbnail\" src=\"plugins/CrudGen/themes/{$app_theme}/thumbnail.png\" ";
            echo "width=\"320\" height=\"240\" alt=\"{$this->lang['strpreview']}\" /></td></tr>\n";
            echo "\n\t\t</table>";
            echo "<input type=\"hidden\" name=\"download\" value=\"1\" />\n";
            echo "<input type=\"submit\" name=\"btnGenerate\" value=\"{$this->lang['strgenerate']}\" />\n";
            echo "<input type=\"submit\" name=\"btnCancel\" value=\"{$lang['strcancel']}\" />\n";
            echo "</form>";
            echo $this->include_js();

            $misc->printFooter();
        } else {
            $app->lang = $this->lang;
            $app->generate();
        }
    }

    function tree() {

        global $misc, $data;

        $applications = Application::getApps($_REQUEST['database'], $_REQUEST['schema']);
        $reqvars = $misc->getRequestVars('crudgen');

        $url = url(
                'plugin.php', $reqvars, array(
            'plugin' => $this->name,
            'action' => 'show_app',
            'app_id' => field('app_id')
                )
        );

        $attrs = array(
            'text' => field('app_name'),
            'hide' => false,
            'icon' => array('plugin' => $this->name, 'image' => 'CrudGen'),
            'iconAction' => $url,
            'toolTip' => field('relcomment'),
            'action' => $url,
        );

        $misc->printTreeXML($applications, $attrs);
        exit;
    }

}

?>