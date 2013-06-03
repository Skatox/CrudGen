<?php

require_once './plugins/CrudGen/classes/GenHtml.php';
require_once './plugins/CrudGen/classes/Generator/GenPsql.php';
require_once './plugins/CrudGen/classes/Generator/GenPDO.php';

class Generator
{

    public $app;
    public $codeGen;

    /**
     * Class constructor, initialices some variables need for
     * the generation process.
     */
    function __construct($application)
    {
        $this->app = $application;

        //Instances the object according to the selected library
        if ($this->app->library == 'pgsql')
            $this->codeGen = new GenPsql($this->app->lang);
        else
            $this->codeGen = new GenPDO($this->app->lang);
    }


    /**
     * Creates the common.php file, wich include common functions for the app
     *
     * @return bool If the file was generated successfully
     */
    public function writeCommonFile()
    {
        global $misc, $lang;

        $filename = $this->app->folder . "/common.php";
        $commonfile = fopen($filename, 'w');

        if ($commonfile) {
            $pag_code = $this->generatePagination();
            $rows_code = $this->generateReportRowsSelect();

            $functions = $this->getGlobals()
                . $this->getFunction("printTitle", "", "\t\techo '{$this->app->name}';")
                . $this->getFunction("printDescr", "", "\t\techo '{$this->app->getDescription()}';")
                . $this->getFunction("printMenu", "", $this->app->getMenu())
                . $this->getFunction("printPagination", array("\$nrows", "\$limit"), $pag_code)
                . $this->getFunction("printRowsRadios", '', $rows_code);

            $notificationCode = "\t\tif(isset(\$_SESSION['error'])) "
                . "echo \"<div class=\\\"errorMsg\\\">{\$_SESSION['error']}</div>\";"
                . "\n\t\tif(isset(\$_SESSION['msg'])) "
                . "echo \"<div class=\\\"message\\\">{\$_SESSION['msg']}</div>\";"
                . "\n\t\tunset(\$_SESSION['error']);"
                . "\n\t\tunset(\$_SESSION['msg']);";

            $logoutCode = "\t\tif(isset(\$_SESSION['crudgen_user']))"
                . "\n\t\t\techo \""
                . GenHtml::link($lang['strlogout'], 'logout', "?logout=true") . "\";";

            $functions .= $this->getFunction("printMessages", '', $notificationCode)
                . $this->getAuthCode($this->app) //For none just creates the db connection
                . $this->getFunction("printLogout", "", $logoutCode);

            fwrite($commonfile, "<?php" . $functions . "\n?>");
            fclose($commonfile);
        } else {
            $misc->printMsg($this->app->lang['strnocommonfile']);
        }

        return $commonfile ? true : false;
    }


    /**
     * Function to generate a page from a Page object
     *
     * @return boolean reporting if page could be created
     */
    public function generatePages()
    {
        $success = true;

        foreach ($this->app->pages as $page) {
            switch ($page->operation) {
                case "create":
                    $success = $this->generateCreatePage($page);
                    break;
                case "report":
                    $success = $this->generateReportPage($page);
                    break;
                case "update":
                    $success = $this->generateUpdatePage($page);
                    break;
            }
            if (!$success)
                break;
        }
        return $success;
    }


    /**
     * This function generates a Create page
     *
     * @param object  $page Page object wich represents the generating page
     * @return bool if this page was created
     */
    private function generateCreatePage(Page $page)
    {
        global $lang;

        $function_code = '';
        $page->sortFields();

        //If updates info at DB then generates input page
        $clear_vars = "";
        $code = "\n\t\tif(isset(\$_POST[\"operation\"]))"
            . "\n\t\t\tif(\$_POST[\"operation\"] == \"insert\")"
            . "\n\t\t\t\tinsertRecord();"
            . "\n\t\t\t\t\t\n"
            . "\n\t\techo \"" . GenHtml::hidden('operation', 'insert')
            . "\n\t\t\t" . GenHtml::hidden('page_insert_table', $page->getTable())
            . "\n\t\t\t<div class=\\\"form-wrapper\\\">";

        //Prints the input box for each field
        $i = 0;
        $columns = array();
        $values = array();
        $sprintf = array();
        $columns = $page->fields;

        foreach ($columns as $column) {
            if ($column->isOnPage()) {
                $input_id = "column-{$i}";
                $clear_vars .="\n\t\tif(!isset(\$_POST[\"{$column->getName()}\"]))"
                    . "\n\t\t\t\$_POST[\"{$column->getName()}\"] = '';\n";

                $code .= "\n\t\t\t\t<div class=\\\"row\\\">"
                    . "\n\t\t\t\t\t<div class=\\\"label-wrapper\\\">"
                    . "\n\t\t\t\t\t\t"
                    . "<label for=\\\"{$input_id}\\\">{$column->getDisplayName()}</label>"
                    . "\n\t\t\t\t\t</div>"
                    . "\n\t\t\t\t\t\t<div class=\\\"value-wrapper\\\">";

                if ($column->isFK()) {
                    $code .= "\n\t\t\t\t\t\t<select name=\\\"{$column->getName()}\\\" "
                        . "class=\\\"almost-full-wide "
                        . $this->getValidationClasses($page->getTable(), $column->getName())
                        . "\\\">"
                        . "\n\t\t\t\t\t\t"
                        . "<option value=\\\"\\\">{$this->app->lang['strselectval']}</option>\";"
                        . "printFKOptions('{$this->app->getSchema()}','{$column->getRemoteTable()}','"
                        . self::getPK($this->app->getDBName(), $column->getRemoteTable())
                        . "','{$column->getRemoteField()}', \$_POST['{$column->getName()}']); "
                        . "echo \"\n\t\t\t\t\t\t</select>";
                } else {
                    $classes = $this->getValidationClasses($page->getTable(), $column->getName());
                    $code .= "\n\t\t\t\t\t\t"
                        . "<input type=\\\"text\\\" name=\\\"{$column->getName()}\\\" "
                        . " id=\\\"{$input_id}\\\" class=\\\"{$classes}\\\" "
                        . "value=\\\"{\$_POST[\"{$column->getName()}\"]}\\\"/>";
                }
                $code .= "\n\t\t\t\t\t</div>"
                    . "\n\t\t\t\t</div>";

                $i++;
            }
        }
        $code .=  "\n\t\t</div>\";";

        //Generates code for functions
        $buttons_code = "\t\techo \"" . self::getCreateUpdateBtns($page) . "\";";
        $insert_code = $this->codeGen->getCreateCode($this->app->getSchema(), $page->getTable(), $columns, $clear_vars);

        $clear_code = "\t\treturn (\$val == '' || \$val == NULL) ? \"NULL\" : \"'{\$val}'\";";
        $form_action = "\n\t\techo \"{$page->getFilename()}\";";

        $args = array("\$schema,\$table", "\$pk", "\$field", "\$selected_pk");
        $function_code .= $this->getFunction("insertRecord", "", $insert_code)
            . $this->getFunction("printFKOptions", $args, $this->codeGen->printFkOptions())
            . $this->getFunction("clearVars", "\$val", $clear_code)
            . $this->getFunction("printFormAction", '', $form_action)
            . $this->getFunction("printActionButtons", "", $buttons_code)
            . $this->getOperationCode(null, $clear_vars . $code);

        return $this->generatePageFile($page, $function_code);
    }


    /**
     * Function to generate a report webpage with delete functions
     * from a Page object
     *
     * @param object  $page desired page object to generate its file
     * @return bool if this page was created
     */
    private function generateReportPage(Page $page)
    {
        //Sort this page columns by its order
        $page->sortFields();

        //Searchs for the primary key of this table
        $pk = self::getPK($this->app->getDBName(), $page->getTable());

        if ($pk == -1)
            $pk = $page->fields[0]->getName();

        $code = "\n\t\tunset(\$_SESSION['selected']); //clears any selected value "
            . "\n\t\t\$column_order = isset(\$_POST['column_order']) ? "
            . "\$_POST['column_order'] : '{$pk}';"
            . "\n\t\t\$order = isset(\$_POST['order']) ? "
            . "\$_POST['order'] : 'ASC';"
            . "\n\t\techo \""
            . GenHtml::hidden('column_order', '{$column_order}', 'column_order')
            . "\n\t\t\t\t" . GenHtml::hidden('order', '{$order}', 'order')
            . "\n\t\t\t\t"
            . GenHtml::hidden('deletetext', $this->app->lang['strconfirmdelete'], 'deletetext')
            . "\n\t\t\t\t"
            . GenHtml::hidden('noselected', $this->app->lang['strnoselecteditems'], 'noselected')
            . "\";";

        $table_code = "\n\t\techo \"<table id=\\\"results\\\">"
            . "\n\t\t\t\t<thead>"
            . "\n\t\t\t\t\t<tr>"
            . "\n\t\t\t\t\t\t<th scope=\\\"col\\\">"
            . "<input type=\\\"checkbox\\\" id=\\\"selectedAll\\\" value=\\\"0\\\"/>"
            . "\n\t\t\t\t\t\t</th>";

        //variable to counts tables in the sql
        $tables = 0;
        $from = "{$this->app->getSchema()}.{$page->getTable()} a ";
        $joins = array();
        $selects = array("a.{$pk}");

        //Adds table's headers to $code and creates the sql sentence
        $column_name = '';
        $columns = $page->fields;
        $num_fld = $page->countShowFields();

        foreach ($columns as $column) {
            if ($column->isOnPage()) {
                if ($column->isFK()) {
                    $column_name = $column->getRemoteField();
                    $selects[] = " a{$tables}." . $column_name;

                    //Checks for remote PK and compares with fk (in the sql sentence)
                    $fk_pk = Generator::getPK($this->app->getDBName(),
                        $column->getRemoteTable());

                    $joins[] = "\n\t\t\t\tINNER JOIN {$column->getRemoteTable()} a{$tables} "
                        . " ON a.{$column->getName()}=a{$tables}.{$fk_pk} ";

                    $tables++;
                }
                else {
                    $column_name = $column->getName();
                    $selects[] = "a." . $column_name;
                }
                $table_code .= "\n\t\t\t\t\t\t<th scope=\\\"col\\\">"
                    . "<a rel=\\\"{$column->getName()}\\\" \";"
                    . "\n\t\tif(isset(\$_REQUEST['column_order']))"
                    . "\n\t\t\tif(\$_REQUEST['column_order'] == '{$column_name}')"
                    . "\n\t\t\t\techo \"class=\\\"\" . strtolower(\$_REQUEST['order'])"
                    . " . \"\\\"\";"
                    . "\n\t\t\t\techo \">{$column->getDisplayName()}</a></th>";
            }
        }
        $table_code .= "\n\t\t\t\t\t\t<th>{$this->app->lang['stractions']}</th>"
            . "\n\t\t\t\t\t</tr>"
            . "\n\t\t\t\t</thead>"
            . "\n\t\t\t<tbody>\";";

        //Adds deletion request at the begining of the code
        $code .= "\n\n\t\t//Deletion process"
            . "\n\t\tif(isset(\$_REQUEST[\"operation\"])){"
            . "\n\t\t\tif(\$_REQUEST[\"operation\"] == \"delete\"){"
            . "\n\t\t\t\tif(isset(\$_REQUEST[\"selected\"])){"
            . "\n\t\t\t\t\tif (deleteRecords(\$_REQUEST[\"selected\"])){"
            . "\n\t\t\t\t\t\t\$_SESSION['msg'] = \"{$this->app->lang['strdelsucess']}\";"
            . "\n\t\t\t\t\t\t\$_POST[\"term\"] = \"\";"
            . "\n\t\t\t\t\t} else {"
            . "\n\t\t\t\t\t\t\$_SESSION['error'] = \"{$this->app->lang['strrowdeletedbad']}\";"
            . "\n\t\t\t\t\t}"
            . "\n\t\t\t\t} else {"
            . "\n\t\t\t\t\t\$_SESSION['error'] = \"{$this->app->lang['strnorowstodelete']}\";"
            . "\n\t\t\t\t}"
            . "\n\t\t\t}"
            . "\n\t\t}";


        $fetch_code = $this->codeGen->getFetchCode();
        $code .= $this->codeGen->getReportCode($selects, $from, $joins);
        $code .= "\n\n\t\tprintFilterBox(); //Filter results" . $table_code;

        //Executes the sql and creates the table
        $num_fld++;
        $actions = $this->generateActionLinks($page, '{$row[0]}');
        $code .= "\n\n\t\tif(!\$rows)"
            . "\n\t\t\techo \"<tr><td colspan=\\\"" . ($num_fld + 1) ."\\\">"
            . "{$this->app->lang['stremptyrows']}</td></tr>\";"
            . "\n\n\t\twhile ({$fetch_code}){"
            . "\n\t\t\techo \"\t<tr>\";"
            . "\n\t\t\techo \"\t\t<td><input class=\\\"checkbox\\\" "
            . "type=\\\"checkbox\\\" name=\\\"selected[]\\\" value=\\\"{\$row[0]}\\\" />"
            . "</td>\";"
            . "\n\t\t\tfor(\$i=1;\$i<{$num_fld};\$i++)"
            . "\n\t\t\t\techo \"<td>\".htmlspecialchars(\$row[\$i]).\"</td>\";"
            . "\n\t\t\techo \"<td class=\\\"actions\\\">{$actions}</td>\";"
            . "\n\t\t\techo \"</tr>\";\n\t\t}"
            . "\n\t\techo \"</tbody></table>\";"
            . "\n\t\tprintRowsRadios();"
            . "\n\t\tprintPagination(\$rows,\$limit);";

        $filter_code  = $this->generateReportFilterBox($page);
        $delete_code  = $this->codeGen->getDeleteCode($this->app->getSchema(), $page->getTable(), $pk);
        $buttons_code = "\t\techo \"". $this->genReportBtns($page) . "\";";
        $form_action  = "\t\techo \"{$page->getFilename()}\";";

        //Creates the args array for the function
        $function_code = $this->getFunction("printFilterBox", '', $filter_code)
            . $this->getFunction("printActionButtons", '', $buttons_code)
            . $this->getFunction("printFormAction", '', $form_action)
            . $this->getFunction("deleteRecords", array("\$ids"), $delete_code)
            . $this->getOperationCode(null, $code); //Creates the code function

        return $this->generatePageFile($page, $function_code);
    }


    /**
     * This function generates an Update Page
     *
     * @param object  $page Page object wich represents the generating page
     * @return bool if this page was created
     */
    private function generateUpdatePage(Page $page)
    {
        global $lang;

        $columns = $page->fields;
        $page->sortFields();

        //If updates info at DB then generates input page
        $code = "\t\$index = isset(\$_POST[\"crudgen_index\"]) ? \$_POST[\"crudgen_index\"] : 0;"
            . "\n\t\t\$operation = isset(\$_REQUEST[\"crudgen_operation\"]) ? "
            . "\$_REQUEST[\"crudgen_operation\"] : 'edit';"
            . "\n\n\t\tif(isset(\$_REQUEST[\"selected\"]))"
            . "\n\t\t\tif(empty(\$_REQUEST[\"selected\"][\$index])){"
            . "\n\t\t\t\t\$operation = 'none';"
            . "\n\n\t\t\t} else {"
            . "\n\t\t\t\t\$_SESSION[\"selected\"] = \$_REQUEST[\"selected\"];"
            . "\n\t\t\t}\n"
            . "\n\n\t\tif(empty(\$_SESSION[\"selected\"])){"
            . "\n\t\t\t\$_SESSION['error'] = \"{$this->app->lang['strnoselecteditem']}\";"
            . "\n\t\t\t\$operation = 'none';"
            . "\n\t\t}"
            . "\n\n\t\tif(\$operation == \"update\"){"
            . "\n\t\t\t\$success= updateRow(\$_SESSION[\"selected\"][\$index]);\n"
            . "\n\t\t\tif(\$success) {"
            . "\n\t\t\t\t\$_SESSION['msg'] = \"{$this->app->lang['strupdatesuccess']}\";"
            . "\n\t\t\t\t\$index++;";

        foreach ($columns as $column)
            if ($column->isOnPage())
                $code .="\n\t\t\t\tunset(\$_POST[\"{$column->getName()}\"]);";

        $code .= "\n\t\t\t} else {"
            . "\n\t\t\t\t\$operation = \"edit\";"
            . "\n\t\t\t\t\$_SESSION['error'] = \"{$this->app->lang['strpageerredit']}\";"
            . "\n\t\t\t}"
            . "\n\t\t\tif(\$index == count(\$_SESSION[\"selected\"])){"
            . "\n\t\t\t\t\$operation = 'none';"
            . "\n\t\t\t\tunset(\$_SESSION[\"selected\"]);"
            . "\n\t\t\t} else {"
            . "\n\t\t\t\t\$operation = \"edit\";"
            . "\n\t\t\t}"
            . "\n\n\t\t}"
            . "\n\n\t\tif(\$operation == \"edit\"){";
            
        $tables = 0;
        $joins = array();
        $selects = array();
        $update_columns = array();

        foreach ($columns as $column) {
            if ($column->isOnPage()) {

                $selects[] = "a." . $column->getName();
                $update_columns[] = "'{$column->getName()}'";

                if ($column->isFK()) {
                    $selects[] = "a{$tables}." . $column->getRemoteField();
                    $fk_pk = Generator::getPK($this->app->getDBName(), $column->getRemoteTable());
                    $joins[] = "\n\t\t\t\tINNER JOIN {$column->getRemoteTable()} a{$tables} "
                        . " ON a.{$column->getName()}=a{$tables}.{$fk_pk} ";
                    $tables++;
                }
            }
        }
        $sql = "SELECT " . implode(", ", array_values($selects) ) . "\n\t\t\t\t\t"
            . "FROM {$this->app->getSchema()}.{$page->getTable()} a " . implode(" ", $joins)
            . " WHERE a." . self::getPK($this->app->getDBName(), $page->getTable());
        
        $code .= $this->codeGen->getLoadRecord($sql);

        $code .= "\n\t\t\tif(!\$row ) {"
            . "\n\t\t\t\t\$_SESSION['error'] = \"{$this->app->lang['strrecordnoexist']}\";"
            . "\n\t\t\t\t\$operation = 'none';"
            . "\n\t\t\t} else {";

        foreach ($columns as $column) {
            if ($column->isOnPage()) {
                $code .="\n\t\t\t\t\$_POST[\"{$column->getName()}\"] = "
                    . "isset( \$_POST[\"{$column->getName()}\"] ) ? "
                    . "\$_POST[\"{$column->getName()}\"] : \$row[\"{$column->getName()}\"] ;";
            }
        }
        $code .= "\n\t\t\t\techo \"". GenHtml::hidden('crudgen_operation', 'update') . "\";"
            . "\n\t\t\t\techo \"". GenHtml::hidden('crudgen_index', "\". \$index . \"")
            . "\n\t\t\t\t\t<div class=\\\"form-wrapper\\\">";

        //Prints the input box for each field
        $clear_vars = "";
        $values = array();
        $fields = $page->fields;
        $i = 0;

        foreach ($columns as $column) {
            if ($column->isOnPage()) {
                $input_id = "column-{$i}";
                $clear_vars .="\n\t\tif(!isset(\$_POST[\"{$column->getName()}\"]))"
                    . "\n\t\t\t\$_POST[\"{$column->getName()}\"] = '';\n";

                $code .= "\n\t\t\t\t\t\t<div class=\\\"row\\\">"
                    . "\n\t\t\t\t\t\t\t<div class=\\\"label-wrapper\\\">"
                    . "\n\t\t\t\t\t\t\t\t<label for=\\\"{$input_id}\\\">{$column->getDisplayName()}</label>"
                    . "\n\t\t\t\t\t\t\t</div>"
                    . "\n\t\t\t\t\t\t\t<div class=\\\"value-wrapper\\\">";

                if ($column->isFK()) {
                    $code .= "\n\t\t\t\t\t\t\t<select name=\\\"{$column->getName()}\\\" "
                        . "class=\\\""
                        . $this->getValidationClasses($page->getTable(), $column->getName())
                        . "\\\">"
                        . "\n\t\t\t\t\t\t\t"
                        . "<option value=\\\"\\\">{$this->app->lang['strselectval']}</option>\";"
                        . "printFKOptions('{$this->app->getSchema()}','{$column->getRemoteTable()}','"
                        . self::getPK($this->app->getDBName(), $column->getRemoteTable())
                        . "','{$column->getRemoteField()}', \$_POST['{$column->getName()}']); "
                        . "echo \"\n\t\t\t\t\t\t\t</select>";
                } else {
                    $classes = $this->getValidationClasses($page->getTable(), $column->getName());
                    $code .= "\n\t\t\t\t\t\t\t"
                        . "<input type=\\\"text\\\" name=\\\"{$column->getName()}\\\" "
                        . " id=\\\"{$input_id}\\\" class=\\\"{$classes}\\\" "
                        . "value=\\\"{\$_POST[\"{$column->getName()}\"]}\\\"/>";
                }

                $code .= "\n\t\t\t\t\t\t\t</div>"
                    . "\n\t\t\t\t\t\t</div>";
                $values[] = "clearVars(\$_POST[\"{$column->getName()}\"])";
                $i++;
            }
        }
        $code .=  "\n\t\t\t\t\t</div>\";"
            . "\n\t\t\t}"
            . "\n\t\t}"
            . "\n\n\t\tif(\$operation == \"none\"){"
            . "\n\t\t\techo \"<div class=\\\"form-wrapper\\\">"
            . "\n\t\t\t\t<p>{$this->app->lang['strwriteprimarykey']}</p>"
            . "\n\t\t\t\t<div class=\\\"label-wrapper\\\">"
            . "\n\t\t\t\t\t<label for=\\\"selected\\\">{$lang['strprimarykey']}</label>"
            . "\n\t\t\t\t</div>"
            . "\n\t\t\t\t<div class=\\\"value-wrapper\\\">"
            . "\n\t\t\t\t\t<input type=\\\"text\\\" id=\\\"selected\\\" "
            . "name=\\\"selected[]\\\" value=\\\"\\\"/>"
            . "\n\t\t\t\t</div>"
            . "\n\t\t\t</div>\";"
            . "\n\t\t}";

        //Generates code for functions
        $sql = "sprintf(\"UPDATE {$this->app->getSchema()}.{$page->getTable()} "
            . "SET \" . " . "implode(',',\$sql_set) . \" "
            . "WHERE " . self::getPK($this->app->getDBName(), $page->getTable())
            . " = '%s'\",\$id)";

        $update_code = $this->codeGen->getUpdateCode($sql, $update_columns);

        $form_action_code = "\t\techo \"{$page->getFilename()}\";";
        $buttons_code = "\t\techo \"" . self::getCreateUpdateBtns($page) . "\";";
        $clear_code = "\t\treturn (\$val == '' || \$val == NULL) ? \"NULL\" : \"'{\$val}'\";";

        //Creates the code function
        $function_code = $this->getFunction("printFormAction", "", $form_action_code)
            . $this->getFunction("clearVars", '$val', $clear_code)
            . $this->getFunction("updateRow", "\$id", $update_code)
            . $this->getFunction("printActionButtons", "", $buttons_code);

        $args = array("\$schema,\$table", "\$pk", "\$field", "\$selected_pk");

        $function_code .= $this->getFunction("printFKOptions", $args, $this->codeGen->printFkOptions())
            . $this->getOperationCode(null, $code);

        return $this->generatePageFile($page, $function_code);
    }


    /**
     * Here generates all global variables and common code
     *
     * @return string php code for global variables
     */
    private function getGlobals()
    {
        $code = "\n\tdefine( 'DB_HOST' , '{$this->app->getDBHost()}' );\n\t"
            . "define( 'DB_PORT' , {$this->app->getDBPort()} );\n\t"
            . "define( 'DB_USER' , '{$this->app->getDBUser()}' );\n\t"
            . "define( 'DB_PASS' , '{$this->app->getDBPass()}' );\n\t"
            . "define( 'DB_NAME' , '{$this->app->getDBName()}' );\n\t"
            . "define( 'RESULTS_LIMIT' , 10 );\n\t"
            . "define( 'RESULTS_START' , 1 );\n\t"
            . "define( 'MAX_FILTER_LENGTH' , 50 );\n\t"
            . "\n\tsession_start();";

        return $code;
    }

    /**
     * Generates authentication code for the common file
     *
     * @return authorization process code
     */
    private function getAuthCode()
    {
        //Logout function
        $logout_code = "\n\t\tunset(\$_SESSION['crudgen_user']);"
            . "\n\t\tunset(\$_SESSION['crudgen_passwd']);"
            . "\n\t\tsession_destroy();\n\t";

        $code = $this->getFunction("logout", "", $logout_code);

        //Login function
        switch ($this->app->getAuthMethod()) {
            case "dbuser":
                $login_code = $this->codeGen->getLoginByDbUser();
                break;
            case "dbtable":
                $code .= "\n\t" . $this->codeGen->getConnection();
                $login_code = $this->codeGen->getLoginByDbTable(
                    $this->app->getSchema(),
                    $this->app->getAuthTable(),
                    $this->app->getAuthUser(),
                    $this->app->getAuthPassword()
                );
                break;
            default:
                $code .= $this->codeGen->getConnection();
                $login_code = "\t\treturn true;";
        }

        $code .= $this->getFunction('checkAccess', '', $login_code);

        //Global code
        $code .= "\n\n\tif(isset(\$_REQUEST['logout']))"
            . "\n\t\tlogout();\n\n\t";

        return $code;
    }


    /**
     * This function generates the page file
     *
     * @param object  $page
     * @param unknown $op_code string with the operation's function code
     * @return true if everything went ok
     */
    private function generatePageFile(Page $page, $op_code)
    {
        $code = file_get_contents($this->app->folder . "/index.php"); //Content from theme file

        $title = $page->page_title == '' ? '&nbsp;' : $page->page_title;
        $descr = $page->descr == '' ? '&nbsp;' : $page->descr;
        $txt = $page->page_text == '' ? '&nbsp;' : $page->page_text;

        $functions = $this->getFunction("printPageTitle", "", "\t\techo '{$title}';")
            . $this->getFunction("printPageDescr", "", "\t\techo '{$descr}';")
            . $this->getFunction("printPageText", "", "\t\techo '{$txt}';");

        $code = "<?php\n\tinclude_once('common.php');" . $functions . $op_code . "\n?>\n" . $code;
        $generated_file = fopen($this->app->folder . '/' . $page->getFilename(), "w");

        if (!$generated_file)
            return false;

        fwrite($generated_file, $code);
        fclose($generated_file);
        return true;
    }


    /**
     * Returns an array of operations made by an applications in a specific table
     *               each respective filename
     *
     * @param unknown $table name of the table
     * @return array of operations (c=create, r=report, u=update, d= delete) and
     */
    private function getPageOperations($table)
    {
        global $misc;

        $tbl_op = array();
        $tbl_op['operations'] = array();
        $tbl_op['filenames'] = array();

        $driver = $misc->getDatabaseAccessor("phppgadmin");
        $sql = "SELECT DISTINCT p.page_filename, p.operation "
            . "FROM crudgen.application a "
            . "INNER JOIN crudgen.pages p ON p.app_id=a.app_id "
            . "INNER JOIN crudgen.page_tables pt ON pt.pages_page_id=p.page_id "
            . "WHERE pt.table_name='{$table}' AND a.app_id='{$this->app->getId()}' ";

        $rs = $driver->selectSet($sql);

        foreach ($rs as $row) {
            $tbl_op['operations'][] = $row['operation'];
            $tbl_op['filenames'][] = $row['page_filename'];
        }

        return $tbl_op;
    }


    /**
     * Generates code for this page's operation's function
     *
     * @param unknown $args (optional) string array with args required for the function
     * @param unknown $code string with the function's code
     * @return string with generated function
     */
    private function getOperationCode($args = array(), $code)
    {
        $arguments = is_array($args) ? implode(", ", $args) : $args;

        return "\n\n\tfunction pageOperation({$arguments}){\n\t{$code}\n}";
    }


    /**
     * This functions generates necessary classes to validate a create
     * or update page using jQuery's  validation plugin
     *
     * @param unknown $table_name name of the table to check fields attributes
     * @param unknown $name       name of the field to check validation rules
     * @return html code for the required classes (null string if there are not any)
     */
    private function getValidationClasses($table_name, $name)
    {
        global $data;

        $class_code = '';
        $attrs = $data->getTableAttributes($table_name, $name);

        if ($attrs->fields['attnotnull'] == 't')
            $class_code .= 'required ';

        switch ($attrs->fields['type']) {
            case 'date':
                $class_code .= "date ";
                break;
            case 'numeric':
                $class_code .= "number ";
                break;
            case 'smallint':
            case 'integer':
                $class_code .= "digits ";
                break;
        }

        return trim($class_code);;
    }


    /**
     * Function to generate code for filtering results
     *
     * @param object  $page desired page object to generate its file
     * @return string html code of filering box
     */
    private function generateReportFilterBox(Page $page)
    {
        global $lang;

        $columns = array_merge( array(''=>'&nbsp;'), $page->getFieldsName() );

        return "\t\t\$filter_column = isset(\$_POST['filter-column']) ?"
            . " \$_POST['filter-column'] : '';"
            . "\n\t\techo \"<div class=\\\"filter-wrapper\\\">"
            . "\n\t\t\t<label>{$lang['strvalue']}:</label>"
            . "\n\t\t\t<input type=\\\"text\\\" name=\\\"filter-term\\\""
            . " value=\\\"\";\n\t\t\t"
            . "if(isset(\$_POST[\"filter-term\"])) echo \$_POST['filter-term'];"
            . "\n\t\t\techo \"\\\" maxlength=\\\"\" . MAX_FILTER_LENGTH . \"\\\" />"
            . "\n\t\t\t<label>{$lang['strcolumn']}:</label>"
            . GenHtml::select('filter-column', $columns, '$filter_column')
            . "\n\t\t\t<input type=\\\"submit\\\" name=\\\"filter-button\\\" "
            . "value=\\\"{$this->app->lang['strsearch']}\\\" />"
            . "\n\t\t</div>\";";
    }


    /**
     * Function to generate radios for selection how many results to display
     *
     * @return string html code of radio buttons for results
     */
    private function generateReportRowsSelect()
    {
        $options = array( 10=>10, 20=>20, 50=>50, 100=>100);

        return "\t\t\$limit = isset(\$_POST['filter-limit']) ? \$_POST['filter-limit'] : 10;"
            . "\n\t\techo \"<div class=\\\"limit-wrapper\\\">"
            . "\n\t\t\t{$this->app->lang['strdisplay']}"
            . "\n\t\t\t" . GenHtml::select('filter-limit', $options, '$limit')
            . "\n\t\t\t<label>{$this->app->lang['strsrows']}</label>"
            . "\n\t\t</div>\";";
    }


    /**
     * Function generate pagination code for reports
     *
     * @return string pagination code
     */
    private function generatePagination()
    {
        global $lang;

        return "\t\tif(!\$nrows) return '';\n"
            . "\n\t\t\$pages = ceil(\$nrows/\$limit);\n"
            . "\n\t\tif(\$pages < 2) return ;\n"
            . "\n\t\techo \"<div class=\\\"pagination-wrapper\\\">\";"
            . "\n\t\t\$max = RESULTS_LIMIT;"
            . "\n\t\t\$current = isset(\$_POST['offset']) ? \$_POST['offset'] : RESULTS_START;"
            . "\n\t\t\$previous = \$current - 1;"
            . "\n\t\t\$next = \$current + 1;\n"
            . "\n\t\tif(\$current > 1)"
            . "\n\t\t\techo \"<a class=\\\"pagination\\\" rel=\\\""
            . "\". \$previous .\"\\\">{$lang['strprev']}</a>\"\n;"
            . "\n\t\techo \"<label>{$this->app->lang['strgotopage']}</label>\";"
            . "\n\t\techo \"<select name=\\\"offset\\\" class=\\\"offset\\\">\";"
            . "\n\n\t\tfor(\$i=1;\$i <= \$pages;\$i++){"
            . "\n\t\t\techo '<option ';"
            . "\n\t\t\tif(\$current == \$i)"
            . "\n\t\t\t\techo 'selected=\"selected\"';"
            . "\n\t\t\techo '>' . \$i .'</option>';"
            . "\n\t\t}"
            . "\n\t\techo \"</select>\";"
            . "\n\n\t\tif(\$current < \$pages)"
            . "\n\t\t\techo \"<a class=\\\"pagination\\\" rel=\\\""
            . "\". \$next .\"\\\">{$lang['strnext']}</a>\";"
            . "\n\t\techo \"</div>\";";
    }


    /**
     * This function generate buttons for a report page, this buttons
     * lets you navigate trought all pages that interact with current
     * page's db table
     *
     * @param object  $page page object where the buttons will be inserted
     * @return string html code for buttons
     */
    private function genReportBtns(Page $page)
    {
        global $lang;

        $cur_op = $page->operation;
        $page_ops = $this->getPageOperations($page->getTable());

        $create = array_search('c', $page_ops['operations']);
        $report = array_search('r', $page_ops['operations']);
        $update = array_search('u', $page_ops['operations']);

        $code = "<div class=\\\"actions-wrapper\\\">";
        if ($create !== false)
            $code   .= "\n\t\t\t" . GenHtml::link($lang['strinsert'],
                'insertButton button', $page_ops['filenames'][$create]);

        if ($update !== false)
            $code   .= "\n\t\t\t" . GenHtml::link($lang['stredit'],
                'updateButton button', $page_ops['filenames'][$update]);

        if ($report !== false)
            $code   .= "\n\t\t\t" . GenHtml::link($lang['strdelete'],
                'deleteButton button', $page_ops['filenames'][$report]);

        $code .= "\n\t\t</div>";

        return $code;
    }


    /**
     * This function generates action buttons for Create and Update pages
     *
     * @param object  $page page object where the buttons will be inserted
     * @return string html code for buttons
     */
    private function getCreateUpdateBtns(Page $page)
    {
        global $lang;

        $cur_op = $page->operation;
        $page_ops = $this->getPageOperations($page->getTable());

        $create = array_search('c', $page_ops['operations']);
        $report = array_search('r', $page_ops['operations']);
        $update = array_search('u', $page_ops['operations']);

        $code = "<div class=\\\"actions-wrapper {$cur_op}\\\">";

        if ($create !== false && $cur_op != 'update') {
            $caption = $cur_op == 'create' ? $lang['strsave'] : $lang['strinsert'];
            $code   .= "\n\t\t\t" . GenHtml::submit('insertButton', $caption);
        }

        if ($update !== false && $cur_op != 'create') {
            $code   .= "\n\t\t\t" . GenHtml::submit('updateButton', $lang['stredit']);
        }

        if ($report !== false)
            $code   .= "\n\t\t\t" . GenHtml::link($lang['strcancel'],
                'reportButton button', $page_ops['filenames'][$report]);
        else
            $code   .= "\n\t\t\t" . GenHtml::link($lang['strcancel'],
                'reportButton button', $page->getFilename()
                . '?crudgen_operation=none');

        $code .= "\n\t\t</div>";

        return $code;
    }


    /**
     * This function generate links for row actions (at report page)
     *
     * @param object  $page page object where the buttons will be inserted
     * @param unknown $id   Row primary key to realize the action
     * @return string html code for link
     */
    private function generateActionLinks(Page $page, $id)
    {
        global $lang;

        $cur_op = $page->operation;
        $page_ops = $this->getPageOperations($page->getTable());

        $report = array_search('r', $page_ops['operations']);
        $update = array_search('u', $page_ops['operations']);
        $code = '';

        if ($update !== false && $cur_op != 'update')
            $code   .= "\n\t\t\t" . GenHtml::link($lang['stredit'],
                'updateButton action',
                $page_ops['filenames'][$update] . '?selected[]=' . $id );

        if ($report !== false)
            $code   .= "\n\t\t\t" . GenHtml::link($lang['strdelete'],
                'deleteButton action',
                $page_ops['filenames'][$report] .
                '?operation=delete&amp;selected[]=' . $id);

        return $code;
    }

    /**
     * Recursive function to delete folders and its files
     *
     * @param unknown $dir directory
     */
    private static function rrmdir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);

            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir. DIRECTORY_SEPARATOR .$object) == "dir")
                        self::rrmdir($dir. DIRECTORY_SEPARATOR . $object);
                    else unlink($dir. DIRECTORY_SEPARATOR .$object);
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }


    /**
     * Returns the column name of a table's primary key
     *
     * @param unknown $db    name of database where the table is located
     * @param unknown $table name of the table
     * @return string with primary key's column name
     */
    public static function getPK($db, $table)
    {
        global $misc;

        $driver = $misc->getDatabaseAccessor($db);
        $sql = "SELECT column_name "
            . "FROM information_schema.key_column_usage "
            . "WHERE table_name='{$table}' AND constraint_name='{$table}_pkey'";

        return $driver->selectField($sql, 'column_name');
    }


    /**
     * Returns an array of detected themes
     *
     * @access public
     * @return string array of detected themes
     */
    public static function getThemes()
    {
        $themes = array();
        $dir = dir("./plugins/CrudGen/themes/");

        while ($folder = $dir->read())
            if (($folder != '.') && ($folder != '..'))
                $themes[] = $folder;

            $dir->close();
        return $themes;
    }


    /**
     * Recursive function to copy elements from a folder to other
     *
     * @param unknown $src source file's path
     * @param unknown $dst destion of files
     */
    public static function recursive_copy($src, $dst)
    {
        $dir = opendir($src);

        if (file_exists($dst)) { //If directory exists deletes it
            $files = glob($dst . '/*');
            if (count($files) > 1) {
                foreach ($files as $file) {
                    if (is_dir($file))
                        self::rrmdir($file);
                    else
                        unlink($file);
                }
                rmdir($dst);
            }
        }
        @mkdir($dst);

        $ignored_files = array('.', '..', 'thumbnail.png');
        while (false !== ( $file = readdir($dir))) {
            if (!in_array($file, $ignored_files)) {
                if (is_dir($src . '/' . $file)) {
                    self::recursive_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }


    public function createZipFile()
    {
        $source = $this->app->folder;
        $destination = $this->app->folder . ".zip";

        if (extension_loaded('zip') === true) {
            if (file_exists($source) === true) {
                $zip = new ZipArchive();

                if ($zip->open($destination, ZIPARCHIVE::CREATE) === true) {
                    $source = realpath($source);

                    if (is_dir($source) === true) {
                        $files = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($source,
                                RecursiveDirectoryIterator::SKIP_DOTS),
                            RecursiveIteratorIterator::SELF_FIRST);

                        foreach ($files as $file) {
                            $file = realpath($file);

                            if (is_dir($file) === true) {
                                $zip->addEmptyDir(str_replace($source.'/', '', $file.'/'));
                            } else if (is_file($file) === true) {
                                    $zip->addFromString(str_replace($source.'/', '', $file),
                                        file_get_contents($file));
                                }
                        }
                    } else if (is_file($source) === true) {
                            $zip->addFromString(basename($source),
                                file_get_contents($source));
                    }
                    return $zip->close();
                }
            }
        }
        return false;
    }

    /**
     * Returns a string with a function code to write it on a file
     * @param $name function's name
     * @param $args an array with the function arguments
     * @param $code the code inside the function, if it has return include it too
     * @return string with complete function code
     */
    static public function getFunction($name, $args, $code) {
        $argc = count($args);

        $strfunction = "\n\n\tfunction {$name}(";
        $strfunction .= is_array($args) ? implode(',', $args) : $args;
        $strfunction .= "){\n{$code}\n\t}";

        return $strfunction;
    }


}
?>