<?php
require_once('GenHtml.php');

class Generator extends GenHtml {
    /**
     * Function to generate a page from a Page object
     * @param $path the application path where files are going to be written
     * @param $app application object to get some general information
     * @param $page desired page object to generate its file
     * @return boolean reporting if page could be created
     */
    public static function generatePage(Application $app, Page $page, $path) {
        switch ($page->operation) {
            case "create": return self::generateCreatePage($path, $app, $page);
            case "report": return self::generateReportPage($path, $app, $page);
            case "update": return self::generateUpdatePage($path, $app, $page);
        }
        return true;
    }

    /**
     * This function generates a Crate page
     * @param $path path where file is going to be written
     * @param $app application object where the $app belongs
     * @param $page Page object wich represents the generating page
     * @return bool if this page was created
     */
    public static function generateCreatePage($path, Application $app, Page $page) {
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
        $columns = array();
        $values = array();
        $sprintf = array();
        $fields = $page->fields;

        for ($i = 0; $i < count($fields); $i++) {
            if ($fields[$i]->isOnPage()) {
                $input_id = "column-{$i}";
                $clear_vars .="\n\t\tif(!isset(\$_POST[\"{$fields[$i]->getName()}\"]))"
                    . "\n\t\t\t\$_POST[\"{$fields[$i]->getName()}\"] = '';\n";

                $code .= "\n\t\t\t\t<div class=\\\"row\\\">"
                    . "\n\t\t\t\t\t<div class=\\\"label-wrapper\\\">"
                    . "\n\t\t\t\t\t\t<label for=\\\"{$input_id}\\\">{$fields[$i]->getDisplayName()}</label>"
                    . "\n\t\t\t\t\t</div>"
                    . "\n\t\t\t\t\t\t<div class=\\\"value-wrapper\\\">";

                if ($fields[$i]->isFK()) {
                    $code .= "\n\t\t\t\t\t\t<select name=\\\"{$fields[$i]->getName()}\\\" "
                        . "class=\\\"almost-full-wide "
                        . self::generateValidationClasses($page->getTable(), $fields[$i]->getName())
                        . "\\\">"
                        . "\n\t\t\t\t\t\t<option value=\\\"\\\">{$app->lang['strselectval']}</option>\";"
                        . "printFKOptions('{$app->getSchema()}','{$fields[$i]->getRemoteTable()}','"
                        . self::getPK($app->getDBName(), $fields[$i]->getRemoteTable()) 
                        . "','{$fields[$i]->getRemoteField()}'); "
                        . "echo \"\n\t\t\t\t\t\t</select>";
                } else {
                    $class_code = self::generateValidationClasses($page->getTable(), $fields[$i]->getName());
                    $code .= "\n\t\t\t\t\t\t<input type=\\\"text\\\" name=\\\"{$fields[$i]->getName()}\\\" "
                    . " id=\\\"{$input_id}\\\" class=\\\"{$class_code}\\\" "
                    . "value=\\\"{\$_POST[\"{$fields[$i]->getName()}\"]}\\\"/>";
                }

                $code .= "\n\t\t\t\t\t</div>"
                    . "\n\t\t\t\t</div>";

                $columns[] = $fields[$i]->getName();
                $values[] = "clearVars(\$_POST[\"{$fields[$i]->getName()}\"])";
                $sprintf[] = "%s";
            }
        }
        $code .=  "\n\t\t</div>\";";

        //Generates code for functions
        $buttons_code = "\t\techo \"" . self::genCreateUpdateBtns($app, $page) . "\";";

        $sql = "\n\t\t\tsprintf(\"INSERT INTO {$app->getSchema()}.{$page->getTable()}"
            . " (" . implode(",", $columns) . ") "
            . "\n\t\t\t\tVALUES (" . implode(",", $sprintf) . ")\",\n\t\t\t\t"
            .  implode(",\n\t\t\t\t", $values) . ")";

        $insert_code = "\t\tglobal \$conn;"
            . "\n\t\tif (!\$conn) {"
            . "\n\t\t\t\$_SESSION['error'] =  \"{$app->lang['strerrordbconn']}: \" . pg_last_error();"
            . "\n\t\t\texit;"
            . "\n\t\t}\n"
            . $clear_vars
            . "\n\n\t\ttry {"
            . "\n\t\t\t\$rs = pg_query(\$conn,{$sql});\n"
            . "\n\t\t} catch (Exception \$e) {"
            . "\n\t\t\t\$rs = NULL;"
            . "\n\t\t}"
            . "\n\t\tif (!\$rs) {"
            . "\n\t\t\t\$_SESSION['error'] = \"{$app->lang['strinsertfail']}:\" . pg_last_error( \$conn );"
            . "\n\t\t\treturn false;"
            . "\n\t\t} else {"
            . "\n\t\t\t\$_SESSION['msg'] = \"{$app->lang['strinsertsuccess']}\";";

        foreach ($columns as $column)
             $insert_code .= "\n\t\t\t\$_POST['{$column}'] = '';";

        $insert_code .= "\n\t\t\tpg_free_result(\$rs);"
            . "\n\t\t\treturn true;"
            . "\n\t\t}";

        $clear_code = "\t\treturn (\$val == '' || \$val == NULL) ? \"NULL\" : \"'{\$val}'\";";

        $form_action = "\n\t\techo \"{$page->getFilename()}\";";

        $args = array("\$schema,\$table", "\$pk", "\$field");
        $function_code .= self::getFunction("insertRecord", "", $insert_code);
        $function_code .= self::getFunction("printFKOptions", $args, self::printFkOptions($app));
        $function_code .= self::getFunction("clearVars", "\$val", $clear_code);
        $function_code .= self::getFunction("printFormAction", '', $form_action);
        $function_code .= self::getFunction("printActionButtons", "", $buttons_code);
        $function_code .= self::generateOpFunc(null, $clear_vars . $code);

        return self::generatePageFile($page, $path, $function_code);
    }

    /**
     * Function to generate a report webpage with delete functions
     * from a Page object
     * @param $path path where file is going to be written
     * @param $page desired page object to generate its file
     * @param $app current aplication's object
     * @return bool if this page was created
     */
    public static function generateReportPage($path, Application $app, Page $page) {

        //Sort this page fields by its order
        $page->sortFields();

        //Searchs for the primary key of this table
        $pk = self::getPK($app->getDBName(), $page->getTable());

        if ($pk == -1)
            $pk = $page->fields[0]->getName();

        $code = "\n\t\tunset(\$_SESSION['selected']); //clears any selected value "
                . "\n\t\t\$column_order = isset(\$_POST['column_order']) ? "
                . "\$_POST['column_order'] : '{$pk}';"
                . "\n\t\t\$order = isset(\$_POST['order']) ? "
                . "\$_POST['order'] : 'ASC';"
                . "\n\t\techo \"" 
                . GenHtml::hidden('column_order','{$column_order}', 'column_order')
                . "\n\t\t\t\t" . GenHtml::hidden('order','{$order}', 'order')
                . "\n\t\t\t\t" . GenHtml::hidden('deletetext', $app->lang['strconfirmdelete'], 'deletetext')
                . "\n\t\t\t\t" 
                . GenHtml::hidden('noselected', $app->lang['strnoselecteditems'], 'noselected') . "\";";

        $table_code = "\n\t\techo \"<table id=\\\"results\\\">"
                    . "\n\t\t\t\t<thead>"
                    . "\n\t\t\t\t\t<tr>"
                    . "\n\t\t\t\t\t\t<th scope=\\\"col\\\">"
                    . "<input type=\\\"checkbox\\\" id=\\\"selectedAll\\\" "
                    . "value=\\\"0\\\"/>"
                    . "\n\t\t\t\t\t\t</th>";

        //variable to counts tables in the sql
        $tables = 0;
        $from = array("{$app->getSchema()}.{$page->getTable()} a");
        $wheres = array();
        $selects = array("a.{$pk}");

        //Adds table's headers to $code and creates the sql sentence
        $column_name = '';
        $fields = $page->fields;
        $num_fld = $page->countShowFields();

        for ($i = 0; $i < count($fields); $i++) {
            if ($fields[$i]->isOnPage()) {

                if ($fields[$i]->isFK()) {
                    $column_name = $fields[$i]->getRemoteField();
                    $selects[] = "a{$tables}." . $column_name;
                    $from[] = "{$fields[$i]->getRemoteTable()} a{$tables}";

                    //Checks for remote PK and compares with fk (in the sql sentence)
                    $fk_pk = Generator::getPK($app->getDBName(),
                        $fields[$i]->getRemoteTable());

                    $wheres[] = "a.{$fields[$i]->getName()}=a{$tables}.{$fk_pk} ";
                    $tables++;
                }
                else {
                    $column_name = $fields[$i]->getName();
                    $selects[] = "a." . $column_name;
                }

                $table_code .= "\n\t\t\t\t\t\t<th scope=\\\"col\\\">"
                            . "<a rel=\\\"{$fields[$i]->getName()}\\\" \";"
                            . "\n\t\tif(isset(\$_REQUEST['column_order']))"
                            . "\n\t\t\tif(\$_REQUEST['column_order'] == '{$column_name}')"
                            . "\n\t\t\t\techo \"class=\\\"\" . strtolower(\$_REQUEST['order']) . \"\\\"\";"
                            . "\n\t\t\t\techo \">{$fields[$i]->getDisplayName()}</a></th>";
            }
        };
        $table_code .= "\n\t\t\t\t\t\t<th>{$app->lang['stractions']}</th>"
                    . "\n\t\t\t\t\t</tr>"
                    . "\n\t\t\t\t</thead>"
                    . "\n\t\t\t<tbody>\";";

        //Builds sql sentence
        $sql = "SELECT " . implode(",", $selects) . " FROM " . implode(",", $from);

        //Adds deletion request at the begining of the code
        $code .= "\n\n\t\t//Deletion process"
            . "\n\t\tif(isset(\$_REQUEST[\"operation\"])){"
            . "\n\t\t\tif(\$_REQUEST[\"operation\"] == \"delete\"){"
            . "\n\t\t\t\tif(isset(\$_REQUEST[\"selected\"])){"
            . "\n\t\t\t\t\tif (deleteRecords(\$_REQUEST[\"selected\"])){"
            . "\n\t\t\t\t\t\t\$_SESSION['msg'] = \"{$app->lang['strdelsucess']}\";"
            . "\n\t\t\t\t\t\t\$_POST[\"term\"] = \"\";"
            . "\n\t\t\t\t\t} else {"
            . "\n\t\t\t\t\t\t\$_SESSION['error'] = \"{$app->lang['strrowdeletedbad']}\";"
            . "\n\t\t\t\t\t}"
            . "\n\t\t\t\t} else {"
            . "\n\t\t\t\t\t\$_SESSION['error'] = \"{$app->lang['strnorowstodelete']}\";"
            . "\n\t\t\t\t}"
            . "\n\t\t\t}"
            . "\n\t\t}"
            . "\n\n\t\tglobal \$conn;"
            . "\n\t\t\$extra_sql=\" WHERE ";

        $code .= count($wheres) ? implode("AND ", $wheres) : "1=1";

        $code .= "\";"
            . "\n\t\n\t\tif(isset(\$_POST[\"filter-term\"])&& isset(\$_POST['filter-column']))"
            . "\n\t\t\tif(!empty(\$_POST[\"filter-term\"]) && !empty(\$_POST['filter-column']))"
            . "\n\t\t\t\t\$extra_sql.= sprintf("
            . "\"AND CAST(%s  AS VARCHAR) ILIKE '%s'\", \$_POST[\"filter-column\"],"
            . " \"%{\$_POST[\"filter-term\"]}%\");"
            . "\n\t\t\telse"
            . "\n\t\t\t\t\$_POST[\"filter-term\"] = '';"
            . "\n\n\t\tif(isset(\$_POST[\"column_order\"])){"
            . "\n\t\t\t\$extra_sql .= sprintf(\" ORDER BY a.%s\",\$_POST[\"column_order\"]);"
            . "\n\t\t\t\$extra_sql .= \$_POST[\"order\"]==\"ASC\" ? \" ASC\" : \" DESC\";"
            . "\n\t\t}"
            . "\n\n\t\t\$limit = isset(\$_POST[\"filter-limit\"]) ? "
            . "\$_POST[\"filter-limit\"] : RESULTS_LIMIT;"
            . "\n\t\t\$offset = isset(\$_POST[\"offset\"]) ? \$_POST[\"offset\"]"
            . " : RESULTS_START;"
            . "\n\n\t\tif (isset(\$_POST['filter-button']))"
            . "\n\t\t\t\$offset = RESULTS_START;"
            . "\n\n\t\t\$offset = \$limit * (\$offset -1);"
            . "\n\t\t\$paginate_sql = sprintf(\" LIMIT %d OFFSET %d\", \$limit, \$offset);\n"
            . "\n\t\tif (!\$conn) {\n\t\t\t"
            . "\$_SESSION['error'] = \"{$app->lang['strerrordbconn']}: \".pg_last_error();"
            . "\n\t\t\texit;"
            . "\n\t\t}\n"
            . "\n\t\t\$rs = pg_query(\$conn, \"{$sql}\".\$extra_sql);"
            . "\n\n\t\tif (!\$rs) {"
            . "\n\t\t\t\$_SESSION['error'] = \"{$app->lang['strerrorquery']}\";"
            . "\n\t\t\texit;"
            . "\n\t\t}"
            . "\n\t\t\$rows = pg_num_rows(\$rs);"
            . "\n\t\t\$rs = pg_query(\$conn,\"{$sql}\".\$extra_sql.\$paginate_sql);"
            . "\n\n\t\tprintFilterBox();//Filter results";
        $code .= $table_code;

        //Executes the sql and creates the table
        $num_fld += 1;
        $actions = self::generateActionLinks($app,$page,'{$row[0]}');
        $code .= "\n\n\t\tif(!\$rows)"
            . "\n\t\t\techo \"<tr><td colspan=\\\"" . ($num_fld + 1) ."\\\">"
            . "{$app->lang['stremptyrows']}</td></tr>\";"
            . "\n\n\t\twhile (\$row = pg_fetch_array(\$rs)){\n\t\t\techo \"\t<tr>\";"
            . "\n\t\t\techo \"\t\t<td><input class=\\\"checkbox\\\" "
            . "type=\\\"checkbox\\\" name=\\\"selected[]\\\" value=\\\"{\$row[0]}\\\" />"
            . "</td>\";"
            . "\n\t\t\tfor(\$i=1;\$i<{$num_fld};\$i++)"
            . "\n\t\t\t\techo \"<td>\".htmlspecialchars(\$row[\$i]).\"</td>\";"
            . "\n\t\t\techo \"<td class=\\\"actions\\\">{$actions}</td>\";"
            . "\n\t\t\techo \"</tr>\";\n\t\t}"
            . "\n\n\t\tpg_free_result(\$rs);//Closes db connection"
            . "\n\t\techo \"</tbody></table>\";"
            . "\n\t\tprintRowsRadios();"
            . "\n\t\tprintPagination(\$rows,\$limit);";

        $filter_code = self::generateReportFilterBox($app, $page);
        $delete_code = self::generateDeleteCode($app, $page->getTable(), $pk );
        $buttons_code = "\t\techo \"". self::genReportBtns($app, $page) . "\";";
        $form_action = "\t\techo \"{$page->getFilename()}\";";

        //Creates the args array for the function
        $function_code = self::getFunction("printFilterBox", '', $filter_code);
        $function_code .= self::getFunction("printActionButtons", '', $buttons_code);
        $function_code .= self::getFunction("printFormAction", '', $form_action);
        $function_code .= self::getFunction("deleteRecords", array("\$ids"), $delete_code);

        //Creates the code function
        $function_code .= self::generateOpFunc(null, $code);
        return self::generatePageFile($page, $path, $function_code);
    }

    /**
     * This function generates an Update Page
     * @param $path path where file is going to be written
     * @param $app application object where the $app belongs
     * @param $page Page object wich represents the generating page
     * @return bool if this page was created
     */
    public static function generateUpdatePage($path, Application $app, Page $page) {
        global $lang;

        $fields = $page->fields;
        $columns = array(); 
        $visible_columns = array(); 
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
            . "\n\t\t\t\$_SESSION['error'] = \"{$app->lang['strnoselecteditem']}\";"
            . "\n\t\t\t\$operation = 'none';"
            . "\n\t\t}"
            . "\n\n\t\tif(\$operation == \"update\"){"
            . "\n\t\t\t\t\$success= updateRow(\$_SESSION[\"selected\"][\$index]);\n"
            . "\n\t\t\t\tif(\$success) {"
            . "\n\t\t\t\t\t\$_SESSION['msg'] = \"{$app->lang['strupdatesuccess']}\";"
            . "\n\t\t\t\t\t\$index++;";

        for ($i = 0; $i < count($fields); $i++) {
            if ($fields[$i]->isOnPage()) {
                $code .="\n\t\t\t\t\tunset(\$_POST[\"{$fields[$i]->getName()}\"]);";   
            }
        }

        $code .= "\n\t\t\t\t} else {"
            . "\n\t\t\t\t\t\$operation = \"edit\";"
            . "\n\t\t\t\t\t\$_SESSION['error'] = \"{$app->lang['strpageerredit']}\";"
            . "\n\t\t\t\t}"
            . "\n\t\t\t\tif(\$index == count(\$_SESSION[\"selected\"])){"
            . "\n\t\t\t\t\t\$operation = 'none';"
            . "\n\t\t\t\t\t\$_SESSION['error'] = \"{$app->lang['strnomoreitems']}\";"
            . "\n\t\t\t\t\tunset(\$_SESSION['msg']);"
            . "\n\t\t\t\t\tunset(\$_SESSION[\"selected\"]);"
            . "\n\t\t\t\t} else {"
            . "\n\t\t\t\t\t\$operation = \"edit\";"
            . "\n\t\t\t\t}"
            . "\n\n\t\t}"
            . "\n\n\t\tif(\$operation == \"edit\"){"
            . "\n\t\t\t\tglobal \$conn;\n"
            . "\n\t\t\t\tif (!\$conn) {"
            . "\n\t\t\t\t\t\$_SESSION['error'] = \"{$app->lang['strerrordbconn']} :\" . pg_last_error();"
            . "\n\t\t\t\t\texit;"
            . "\n\t\t\t\t}"
            . "\n\t\t\t\t\$cant = count(\$_SESSION[\"selected\"]);"
            . "\n\t\t\t\t\$id = \$cant > 1 ? \$_SESSION[\"selected\"][\$index] : \$_SESSION[\"selected\"][0];"
            . "\n\t\t\t\t\$query = sprintf(\"SELECT ";

        for ($i = 0; $i < count($fields); $i++) {
            if ($fields[$i]->isOnPage()) {
                $columns[] = $fields[$i]->getName();
                $visible_columns[] = "'{$fields[$i]->getName()}'";
            }
        }

        $code .= implode(', ', $columns ) 
            . "\n\t\t\t\t\tFROM {$app->getSchema()}.{$page->getTable()} WHERE "
            . self::getPK($app->getDBName(), $page->getTable()) . " = %s\", \$id );\n"
            . "\n\t\t\t\t\$rs = pg_query(\$conn, \$query);\n"
            . "\n\t\t\t\tif (!\$rs){"
            . "\n\t\t\t\t\t\$_SESSION['error'] = \"{$app->lang['strerrorquery']}\";"
            . "\n\t\t\t\t\texit;"
            . "\n\t\t\t\t}\n"
            . "\n\t\t\t\t\$row = pg_fetch_array(\$rs);\n"
            . "\n\t\t\t\tif(!\$row ) {"
            . "\n\t\t\t\t\t\$_SESSION['error'] = \"{$app->lang['strrecordnoexist']}\";"
            . "\n\t\t\t\t\t\$operation = 'none';"
            . "\n\t\t\t\t} else {";

        for ($i = 0; $i < count($fields); $i++) {
            if ($fields[$i]->isOnPage()) {
                $code .="\n\t\t\t\t\t\$_POST[\"{$fields[$i]->getName()}\"] = "   
                . "isset( \$_POST[\"{$fields[$i]->getName()}\"] ) ? "   
                . "\$_POST[\"{$fields[$i]->getName()}\"] : \$row[\"{$fields[$i]->getName()}\"] ;";   
            }
        }
        
        $code .= "\n\t\t\t\t\techo \"". GenHtml::hidden('crudgen_operation', 'update') . "\";"
            . "\n\t\t\t\t\techo \"". GenHtml::hidden('crudgen_index', "\". \$index . \"")
            . "\n\t\t\t\t\t\t<div class=\\\"form-wrapper\\\">";

        //Prints the input box for each field
        $clear_vars = "";
        $values = array();
        $columns = array();
        $fields = $page->fields;

        for ($i = 0; $i < count($fields); $i++) {
            if ($fields[$i]->isOnPage()) {
                $input_id = "column-{$i}";
                $clear_vars .="\n\t\t\tif(!isset(\$_POST[\"{$fields[$i]->getName()}\"]))"
                    . "\n\t\t\t\t\$_POST[\"{$fields[$i]->getName()}\"] = '';\n";

                $code .= "\n\t\t\t\t\t\t<div class=\\\"row\\\">"
                    . "\n\t\t\t\t\t\t\t<div class=\\\"label-wrapper\\\">"
                    . "\n\t\t\t\t\t\t\t\t<label for=\\\"{$input_id}\\\">{$fields[$i]->getDisplayName()}</label>"
                    . "\n\t\t\t\t\t\t\t</div>"
                    . "\n\t\t\t\t\t\t\t<div class=\\\"value-wrapper\\\">";

                if ($fields[$i]->isFK()) {
                    $code .= "\n\t\t\t\t\t\t\t<select name=\\\"{$fields[$i]->getName()}\\\" "
                        . "class=\\\""
                        . self::generateValidationClasses($page->getTable(), $fields[$i]->getName())
                        . "\\\">"
                        . "\n\t\t\t\t\t\t\t<option value=\\\"\\\">{$app->lang['strselectval']}</option>\";"
                        . "printFKOptions('{$app->getSchema()}','{$fields[$i]->getRemoteTable()}','"
                        . self::getPK($app->getDBName(), $fields[$i]->getRemoteTable()) 
                        . "','{$fields[$i]->getRemoteField()}'); "
                        . "echo \"\n\t\t\t\t\t\t\t</select>";
                } else {
                    $class_code = self::generateValidationClasses($page->getTable(), $fields[$i]->getName());
                    $code .= "\n\t\t\t\t\t\t\t<input type=\\\"text\\\" name=\\\"{$fields[$i]->getName()}\\\" "
                    . " id=\\\"{$input_id}\\\" class=\\\"{$class_code}\\\" "
                    . "value=\\\"{\$_POST[\"{$fields[$i]->getName()}\"]}\\\"/>";
                }

                $code .= "\n\t\t\t\t\t\t\t</div>"
                    . "\n\t\t\t\t\t\t</div>";

                $columns[] = $fields[$i]->getName();
                $values[] = "clearVars(\$_POST[\"{$fields[$i]->getName()}\"])";
            }
        }
        $code .=  "\n\t\t\t\t\t</div>\";"
            . "\n\t\t\t\t}"
            . "\n\n\t\t}"
            . "\n\n\t\tif(\$operation == \"none\"){"
            . "\n\t\t\techo \"<div class=\\\"form-wrapper\\\">"
            . "\n\t\t\t\t<p>{$app->lang['strwriteprimarykey']}</p>"
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
        $sql = "UPDATE {$app->getSchema()}.{$page->getTable()} SET \" . "
            . "implode(',',\$sql_set) . \" WHERE "
            . self::getPK($app->getDBName(), $page->getTable()) 
            . " = '{\$id}'";

        $update_code = "\t\tglobal \$conn;\n"
            . "\n\t\t\$columns = array(" . implode(',', $visible_columns) . ");"
            . "\n\t\t\$sql_set = array();"
            . "\n\n\t\tforeach(\$columns as \$column){"
            . "\n\t\t\tif(\$_POST[\$column] == \"\")"
            . "\n\t\t\t\t\$sql_set[] = \"{\$column} = NULL\";"
            . "\n\t\t\telse"
            . "\n\t\t\t\t\$sql_set[] = \"{\$column} = '{\$_POST[\$column]}'\";"
            . "\n\t\t}\n"
            . "\n\t\tif (!\$conn ) {"
            . "\n\t\t\t\$_SESSION['error'] = \"{$app->lang['strerrordbconn']}: \" . pg_last_error();"
            . "\n\t\t\texit;"
            . "\n\t\t}\n"
            . "\n\t\t\$rs = pg_query(\$conn,\"{$sql}\");\n"
            . "\n\t\tif (!\$rs) {"
            . "\n\t\t\t\$_SESSION['error'] = \"{$app->lang['strupdatefail']}: \" . pg_last_error(\$conn);"
            . "\n\t\t\tpg_free_result(\$rs);"
            . "\n\t\t\treturn false;"
            . "\n\t\t} else {"
            . "\n\t\t\tpg_free_result(\$rs);"
            . "\n\t\t\treturn true;"
            . "\n\t\t}";

        $form_action_code = "\t\techo \"{$page->getFilename()}\";";
        $buttons_code = "\t\techo \"" . self::genCreateUpdateBtns($app, $page) . "\";";
        $clear_code = "\t\treturn (\$val == '' || \$val == NULL) ? \"NULL\" : \"'{\$val}'\";";

        //Creates the code function
        $function_code = self::getFunction("printFormAction", "", $form_action_code);
        $function_code .= self::getFunction("clearVars", "", $clear_code);
        $function_code .= self::getFunction("updateRow", "\$id", $update_code);
        $function_code .= self::getFunction("printActionButtons", "", $buttons_code);

        $args = array("\$schema,\$table", "\$pk", "\$field", "\$selected_pk");
        $function_code .= self::getFunction("printFKOptions", $args, self::printFkOptions($app));
        $function_code .= self::generateOpFunc(null, $code);

        return self::generatePageFile($page, $path, $function_code);
    }

    /**
     * Here generates all global variables and common code
     * @param Application $app application where to insert the security code
     * @return string php code for global variables
     */
    public static function getGlobals(Application $app) {
        $code = "\n\tdefine( 'DB_HOST' , '{$app->getDBHost()}' );\n\t";
        $code .= "define( 'DB_PORT' , {$app->getDBPort()} );\n\t";
        $code .= "define( 'DB_USER' , '{$app->getDBUser()}' );\n\t";
        $code .= "define( 'DB_PASS' , '{$app->getDBPass()}' );\n\t";
        $code .= "define( 'DB_NAME' , '{$app->getDBName()}' );\n\t";
        $code .= "define( 'RESULTS_LIMIT' , 10 );\n\t";
        $code .= "define( 'RESULTS_START' , 1 );\n\t";
        $code .= "define( 'MAX_FILTER_LENGTH' , 50 );\n\t";
        $code .= "\n\tsession_start();";

        return $code;
    }

    /**
     * Gets connections string
     * @param string $library PHP Posgrest library to use
     * @return string code to connect to the database
     */
    public static function getConnection($library, $user='DB_USER', $password='DB_PASS'){
        $code = "\n\n\t";
        
        if ($library == 'pgsql') {
            $code .= "\$conn = pg_connect(\"host='\" . DB_HOST . \"' "
                . "port='\" . DB_PORT . \"' password='\" . " . $password . " . \"' "
                . "user='\" . " . $user . " . \"' dbname='\" . DB_NAME . \"'\");";
        } else {
            $code .= "\$conn = new PDO(\"pgsql:dbname=\" . DB_NAME . \";"
                . "host=\" . DB_HOST . \":\" . DB_PORT . \"\","
                . "'\" . " . $user . " . \"','\" . " . $password . " . \"');";
        }

        return $code;
    }

    /**
     * Generates authentication code for the common file
     * @param Application $app application to generate auth code
     * @return authorization process code
     */
    public static function getAuthCode($app) {
         //Logout function
        $logout_code = "\n\t\tunset(\$_SESSION['crudgen_user']);"
            . "\n\t\tunset(\$_SESSION['crudgen_passwd']);"
            . "\n\t\tsession_destroy();\n\t";

        $code = Generator::getFunction("logout", "", $logout_code);

        //Login function
        switch ($app->getAuthMethod()) {
            case "dbuser":
                $login_code = Generator::getLoginByDbUser($app);
                break;
            case "dbtable":
                $code .= "\n\t" . Generator::getConnection($app->library);
                $login_code = Generator::getLoginByDbTable($app);
                break;
            default:
                $code = Generator::getConnection($app->library);
                $login_code = "\t\treturn true;";
        }

        $code .= Generator::getFunction("checkAccess", "", $login_code);

        //Global code
        $code .= "\n\n\tif(isset(\$_POST['login_close']))"
                . "\n\t\tlogout();\n\n\t";

        return $code;
    }

    /**
     *  Gets authentication via a Postgres user
     * @param Application $app application to generate auth code
     * @return authorization process code
     */
    public static function getLoginByDbUser(Application $app) {

        $code = "\t\tglobal \$conn;"
            . "\n\n\t\tif(isset(\$_SESSION['crudgen_user']) "
            . "&& isset(\$_SESSION['crudgen_passwd']) ){"
            . "\n\t\t\t" . Generator::getConnection($app->library,
                "\$_SESSION['crudgen_user']", "\$_SESSION['crudgen_passwd']")
            . "\n\t\t\treturn true;"
            . "\n\t\t} else {";

        if ($app->library == 'pgsql') {

            $code .= "\n\t\t\tif(isset(\$_POST['crudgen_user']) "
                . "&& isset(\$_POST['crudgen_passwd']) )"
                . "\n\t\t\t\t" . Generator::getConnection($app->library,
                    "\$_POST['crudgen_user']", "\$_POST['crudgen_passwd']")
                . "\n\t\t\t\treturn true;"
                . "\n\t\t\tif(\$conn){"
                . "\n\t\t\t\t\$_SESSION['crudgen_user']=\$_POST['crudgen_user'];"
                . "\n\t\t\t\t\$_SESSION['crudgen_passwd']=\$_POST['crudgen_passwd'];"
                . "\n\t\t\t\treturn true;"
                . "\n\t\t\t}else {"
                . "\n\t\t\t\t\$_SESSION['error']=\"{$app->lang['strloginerror']}\";"
                . "\n\t\t\t\tinclude \"login.inc.php\";"
                . "\n\t\t\t\treturn false;"
                . "\n\t\t\t}"
                . "\n\t\t\tinclude \"login.inc.php\";"
                . "\n\t\t\treturn false;";
        } else {
            $code .= "if(isset(\$_POST['crudgen_user']) "
                . "&& isset(\$_POST['crudgen_passwd']) ){"
                . "\n\t\t\t\ttry{"
                . "\n\t\t\t\t\t" . Generator::getConnection($app->library,
                    "\$_POST['crudgen_user']", "\$_POST['crudgen_passwd']")
                . "\n\t\t\t\t\t\$_SESSION['crudgen_user']=\$_POST['crudgen_user'];"
                . "\n\t\t\t\t\t\$_SESSION['crudgen_passwd']=\$_POST['crudgen_passwd'];"
                . "\n\t\t\t\t}catch(PDOException \$e){"
                . "\n\t\t\t\t\t\$_SESSION['error']= \"{$app->lang['strloginerror']}\";"
                . "\n\t\t\t\t\tinclude \"login.inc.php\";"
                . "\n\t\t\t\t}"
                . "\n\t\t\t}";
        }

        return $code . "\n\t\t}";
    }

    /**
     * This function generates code for security trought an username and password
     * stored in the database
     * @param Application $app application where to insert the security code
     * @return string with php code for no security
     */
    public static function getLoginByDbTable(Application $app) {

        $code = "\t\tglobal \$conn;\n"
            . "\n\t\tif(isset(\$_SESSION['crudgen_user']))"
            . "\n\t\t\treturn true;"
            . "\n\t\telse{"
            . "\n\t\t\tif(isset(\$_POST['crudgen_user']) "
            . "&& isset(\$_POST['crudgen_passwd']) ){\n\t\t\t\t";

        if ($app->library == 'pgsql') {
            $code .= "\$query=sprintf(\"SELECT {$app->getAuthUser()},"
                . "{$app->getAuthPassword()} "
                . "\n\t\t\t\t\t\tFROM {$app->getSchema()}.{$app->getAuthTable()} "
                . "\n\t\t\t\t\t\tWHERE {$app->getAuthUser()}='%s' "
                . "AND {$app->getAuthPassword()}='%s'\","
                . "\$_POST['crudgen_user'],\$_POST['crudgen_passwd']);"
                . "\n\t\t\t\t\$rs=pg_query(\$conn,\$query);\n\t\t\t\t"
                . "if(pg_num_rows(\$rs)){\n\t\t\t\t\t";
        } else {
            $code .= "\$query=\"SELECT {$app->getAuthUser()},{$app->getAuthPassword()} "
                . "\n\t\t\t\t\t\tFROM {$app->getSchema()}.{$app->getAuthTable()} "
                . "\n\t\t\t\t\t\tWHERE {$app->getAuthUser()}=:crudgen_user "
                . "AND {$app->getAuthPassword()}=:crudgen_passwd\";"
                . "\n\t\t\t\t\$rs = \$conn->prepare(\$query);"
                . "\n\t\t\t\t\$rs->execute(array(':crudgen_user'=>\$_POST['crudgen_user'],"
                . " ':crudgen_passwd'=>\$_POST['crudgen_passwd']));\n\t\t\t\t"
                . "if(\$rs->rowCount()){\n\t\t\t\t\t";
        }

        $code .= "\$_SESSION['crudgen_user'] = \$_POST['crudgen_user'];"
            . "\n\t\t\t\t\treturn true;"
            . "\n\t\t\t\t} else {"
            . "\n\t\t\t\t\t\$_SESSION['error']=\"{$app->lang['strloginerror']}\";"
            . '\n\t\t\t\t\tinclude "login.inc.php";'
            . "\n\t\t\t\t}"
            . "\n\t\t\t} else {"
            . "\n\t\t\t\tinclude \"login.inc.php\";"
            . "\n\t\t\t}"
            . "\n\t\t}";

        return $code;
    }

    /**
     * This function generates the page file
     * @param $app  current application object
     * @param path application's file path
     * @param $op_code string with the operation's function code
     * @return true if everything went ok
     */
    public static function generatePageFile(Page $page, $path, $op_code) {
        $code = file_get_contents($path . "/index.php"); //Content from theme file

        $title = $page->getTitle() == '' ? '&nbsp;' : $page->getTitle();
        $descr = $page->getDescription() == '' ? '&nbsp;' : $page->getDescription();
        $txt = $page->getPageText() == '' ? '&nbsp;' : $page->getPageText();

        $functions = self::getFunction("printPageTitle", "", "\t\techo '{$title}';");
        $functions .= self::getFunction("printPageDescr", "", "\t\techo '{$descr}';");
        $functions .= self::getFunction("printPageText", "", "\t\techo '{$txt}';");

        $code = "<?php\n\tinclude_once('common.php');" . $functions . $op_code . "\n?>\n" . $code;
        $generated_file = fopen($path . '/' . $page->getFilename(), "w");

        if (!$generated_file)
            return false;

        fwrite($generated_file, $code);
        fclose($generated_file);
        return true;
    }

    /**
     * Recursive function to delete folders and its files
     * @param $dir directory
     */
    public static function rrmdir($dir) {
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
     * Recursive function to copy elements from a folder to other
     * @param $src source file's path
     * @param $dst destion of files
     */
    public static function recursive_copy($src, $dst) {
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

    public function createZipFile($source, $destination) {
        if (extension_loaded('zip') === true) {
            if (file_exists($source) === true) {
                $zip = new ZipArchive();

                if ($zip->open($destination, ZIPARCHIVE::CREATE) === true) {
                    $source = realpath($source);

                    if (is_dir($source) === true) {
                        $files = new RecursiveIteratorIterator(
                            new RecursiveDirectoryIterator($source),
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
     * Returns an array of operations made by an applications in a specific table
     * @param $app an application object to get its operations
     * @param $table name of the table
     * @return array of operations (c=create, r=report, u=update, d= delete) and
     *               each respective filename
     */
    public static function getPageOperations(Application $app, $table) {
        global $misc;

        $tbl_op = array();
        $tbl_op['operations'] = array();
        $tbl_op['filenames'] = array();

        $driver = $misc->getDatabaseAccessor("phppgadmin");
        $sql = "SELECT DISTINCT p.page_filename, p.operation "
            . "FROM crudgen.application a "
            . "INNER JOIN crudgen.pages p ON p.app_id=a.app_id "
            . "INNER JOIN crudgen.page_tables pt ON pt.pages_page_id=p.page_id "
            . "WHERE pt.table_name='{$table}' AND a.app_id='{$app->getId()}' ";

        $rs = $driver->selectSet($sql);

        foreach ($rs as $row) {
            $tbl_op['operations'][] = $row['operation'];
            $tbl_op['filenames'][] = $row['page_filename'];
        }

        return $tbl_op;
    }


    /**
     * Prints error and returns false due to error
     *
     * @return bool false due to error
     */
    public static function printError($error_text) {
        global $misc;

        $misc->printMsg($error_text);

        return false;
    }

    /**
     * Generates code for this page's operation's function
     * @param $args string array with args required for the function
     * @param $code string with the function's code
     * @return string with generated function
     */
    public static function generateOpFunc($args, $code) {
        $strfunction = "\n\n\tfunction pageOperation(";
        $argc = count($args);
        $i = 0;
        if ($argc > 0)
            foreach ($args as $arg) {
                $strfunction = $strfunction . $arg;
                if ($i < $argc - 1)
                    $strfunction = $strfunction . ",";
                $i++;
            }
        $strfunction = $strfunction . "){\n";
        $strfunction = $strfunction . "\t" . $code;
        $strfunction = $strfunction . "\n}";
        return $strfunction;
    }

    /**
     * Returns a string with a function code to write it on a file
     * @param $name function's name
     * @param $args an array with the function arguments
     * @param $code the code inside the function, if it has return include it too
     * @return string with complete function code
     */
    public static function getFunction($name, $args, $code) {
        $argc = count($args);

        $strfunction = "\n\n\tfunction {$name}(";
        $strfunction .= is_array($args) ? implode(',', $args) : $args;
        $strfunction .= "){\n{$code}\n\t}";

        return $strfunction;
    }

    /**
     * Returns the column name of a table's primary key
     * @param $db name of database where the table is located
     * @param $table name of the table
     * @return string with primary key's column name
     */
    public static function getPK($db, $table) {
        global $misc;

        $driver = $misc->getDatabaseAccessor($db);
        $sql = "SELECT column_name "
            . "FROM information_schema.key_column_usage "
            . "WHERE table_name='{$table}' AND constraint_name='{$table}_pkey'";

        return $driver->selectField($sql, 'column_name');
    }

    /**
     * This function validates parameters from a browse page
     * @return bool of accepted or not parameters from a browse page
     */
    public function validateParameters() {
        global $lang;

        //Checks if page's filename is not null or doesn't have extension
        if (($_POST['page_filename'] == "") ||
            (substr($_POST['page_filename'], -4) != ".php") ||
            !isset($_POST["page_filename"]))
            return $this->printError($lang['strerrpagefield']);

        //Checks if page title is not null
        if ($_POST["page_title"] == "" || !isset($_POST["page_title"]))
            return $this->printError($lang['strnopagetitle']);

        //Checks if each filename has a .php extension
        foreach ($_POST["display"] as $dis_name) {
            if ($dis_name == "")
                return printError($lang['strnodisplayname']);
        }
        return true;
    }

    /**
     * Returns an array of detected themes
     *
     * @return string array of detected themes
     * @access public
     */
    public static function getThemes() {
        $themes = array();
        $dir = dir("./plugins/CrudGen/themes/");

        while ($folder = $dir->read())
                if (($folder != '.') && ($folder != '..'))
                $themes[] = $folder;

        $dir->close();
        return $themes;
    }

    /**
     * This functions generates necessary classes to validate a create
     * or update page using jQuery's  validation plugin
     * @param $table_name name of the table to check fields attributes
     * @param $name name of the field to check validation rules
     * @return html code for the required classes (null string if there are not any)
     */
    public static function generateValidationClasses($table_name, $name) {
        global $data;

        $class_code = '';
        $attrs = $data->getTableAttributes($table_name);

        while (!$attrs->EOF) {
            if($attrs->fields['attname'] == $name) {
                if ($attrs->fields['attnotnull'] == 't')
                    $class_code .= 'required ';

                switch($attrs->fields['type']){
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
            }
            $attrs->moveNext();
        }
        return trim($class_code);;
    }

    /**
    * Function to generate code for filtering results
    * @param $app current aplication's object
    * @param $page desired page object to generate its file
    * @return string html code of filering box
    */
    public static function generateReportFilterBox( Application $app, Page $page){
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
            . self::select('filter-column', $columns, '$filter_column')
            . "\n\t\t\t<input type=\\\"submit\\\" name=\\\"filter-button\\\" "
            . "value=\\\"{$app->lang['strsearch']}\\\" />"
            . "\n\t\t</div>\";";
    }

    /**
    * Function to generate deletion code
    * @param $app current aplication's object
    * @param $table page's table
    * @param $pk primary key of the table
    * @return string html code of deletion
    */
    public static function generateDeleteCode(Application $app, $table, $pk){
        $sql = "DELETE FROM {$app->getSchema()}.{$table} WHERE {$pk} IN (%s)";

        return "\t\tglobal \$conn;"
            . "\n\n\t\tif (!\$conn) {"
            . "\n\t\t\t\$_SESSION['error'] = \"{$app->lang['strerrordbconn']}: \""
            . " . pg_last_error();"
            . "\n\t\t\treturn false;"
            . "\n\t\t}"
            . "\n\t\t\$rs = pg_query(\$conn, sprintf(\"{$sql}\", implode(\",\" , \$ids ) ) );"
            . "\n\n\t\tif (!\$rs){"
            . "\n\t\t\t\$_SESSION['error'] = \"{$app->lang['strrowdeletedbad']}: \""
            . " . pg_last_error(\$conn);"
            . "\n\t\t\treturn false;"
            . "\n\t\t}"
            . "\n\n\t\tpg_free_result(\$rs);"
            . "\n\t\treturn (!\$rs) ? false : true;";
    }

   /**
    * Function to generate radios for selection how many results to display
    * @param $plugLang plugin's language file
    * @return string html code of radio buttons for results
    */
    public static function generateReportRowsSelect( $plugLang ){
        $options = array( 10=>10, 20=>20, 50=>50, 100=>100);

        return "\t\t\$limit = isset(\$_POST['filter-limit']) ? \$_POST['filter-limit'] : 10;"
            . "\n\t\techo \"<div class=\\\"limit-wrapper\\\">"
            . "\n\t\t\t{$plugLang['strdisplay']}"
            . "\n\t\t\t".self::select('filter-limit', $options, '$limit')
            . "\n\t\t\t<label>{$plugLang['strsrows']}</label>"
            . "\n\t\t</div>\";";
    }

    /**
    * Function generate pagination code for reports
    *
    * @param $pagText Text to display next to the dropdown
    * @return string pagination code
    */
    public static function generatePagination($pagText){

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
            . "\n\t\techo \"<label>{$pagText}</label>\";"
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
     * @param Application $app current aplication object
     * @param Page $page page object where the buttons will be inserted
     * @return string html code for buttons
     */
    public static function genReportBtns(Application $app, Page $page) {
        global $lang;

        $cur_op = $page->operation;
        $page_ops = self::getPageOperations($app, $page->getTable());

        $create = array_search('c', $page_ops['operations']);
        $report = array_search('r', $page_ops['operations']);
        $update = array_search('u', $page_ops['operations']);

        $code = "<div class=\\\"actions-wrapper\\\">";
        if($create !== false)
            $code   .= "\n\t\t\t" . GenHtml::link($lang['strinsert'],
                    'insertButton button', $page_ops['filenames'][$create]);

        if($update !== false)
            $code   .= "\n\t\t\t" . GenHtml::link($lang['stredit'],
                    'updateButton button', $page_ops['filenames'][$update]);

        if($report !== false)
            $code   .= "\n\t\t\t" . GenHtml::link($lang['strdelete'],
                    'deleteButton button', $page_ops['filenames'][$report]);

        $code .= "\n\t\t</div>";

        return $code;
    }

    /**
     * This function generates action buttons for Create and Update pages
     * @param Application $app current aplication object
     * @param Page $page page object where the buttons will be inserted
     * @return string html code for buttons
     */
    public static function genCreateUpdateBtns(Application $app, Page $page) {
        global $lang;

        $cur_op = $page->operation;
        $page_ops = self::getPageOperations($app, $page->getTable());

        $create = array_search('c', $page_ops['operations']);
        $report = array_search('r', $page_ops['operations']);
        $update = array_search('u', $page_ops['operations']);

        $code = "<div class=\\\"actions-wrapper {$cur_op}\\\">";

        if($create !== false && $cur_op != 'update'){
            $caption = $cur_op == 'create' ? $lang['strsave'] : $lang['strinsert'];
            $code   .= "\n\t\t\t" . GenHtml::submit('insertButton', $caption);
        }

        if($update !== false && $cur_op != 'create'){
            $code   .= "\n\t\t\t" . GenHtml::submit('updateButton', $lang['stredit']);
        }

        if($report !== false)
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
     * @param Application $app current aplication object
     * @param Page $page page object where the buttons will be inserted
     * @param $id Row primary key to realize the action
     * @return string html code for link
     */
    public static function generateActionLinks(Application $app, Page $page, $id) {
        global $lang;

        $cur_op = $page->operation;
        $page_ops = self::getPageOperations($app, $page->getTable());

        $report = array_search('r', $page_ops['operations']);
        $update = array_search('u', $page_ops['operations']);
        $code = '';

        if($update !== false && $cur_op != 'update')
            $code   .= "\n\t\t\t" . GenHtml::link($lang['stredit'],
                    'updateButton action',
                    $page_ops['filenames'][$update] . '?selected[]=' . $id );

        if($report !== false)
            $code   .= "\n\t\t\t" . GenHtml::link($lang['strdelete'],
                    'deleteButton action',
                    $page_ops['filenames'][$report] .
                    '?operation=delete&amp;selected[]=' . $id);

        return $code;
    }

    public static function printFkOptions($app){
        return "\t\tglobal \$conn;\n"
            . "\n\t\tif (!\$conn) { "
            . "\n\t\t\t\$_SESSION['error'] = \"{$app->lang['strerrordbconn']}:\" . pg_last_error();"
            . "\n\t\t\texit;"
            . "\n\t\t}"
            . "\n\n\t\ttry {"
            . "\n\t\t\t\$rs = pg_query(\$conn, sprintf(\"SELECT %s,%s "
            ." FROM %s.%s\", \$pk, \$field, \$schema, \$table));"
            . "\n\t\t} catch (Exception \$e) {"
            . "\n\t\t\t\$rs = NULL;"
            . "\n\t\t}"
            . "\n\n\t\tif (!\$rs) {"
            . "\n\t\t\t\$_SESSION['error'] = \"{$app->lang['strerrorquery']}\";"
            . "\n\t\t\texit;"
            . "\n\t\t}"
            . "\n\t\twhile (\$row = pg_fetch_array(\$rs))"
            . "\n\t\t\techo \"<option value=\\\"{\$row[0]}\\\">{\$row[1]}</option>\";"
            . "\n\n\t\tpg_free_result(\$rs);";
    }
}
?>