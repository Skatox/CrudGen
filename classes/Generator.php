<?php

class Generator {

    /**
     * Returns the column name of a table's primary key
     * @param $db name of database where the table is located
     * @param $table name of the table
     * @return string with primary key's column name
     */
    function getPK($db, $table) {
        global $misc;

        $driver = $misc->getDatabaseAccessor($db);
        $sql = "SELECT column_name FROM information_schema.key_column_usage "
                . "WHERE table_name='{$table}' AND constraint_name='{$table}_pkey'";
        return $driver->selectField($sql, 'column_name');
    }
    /**
     *  This functions generates necessary classes to validate an insert or update page
     *  using jQuery's  validation plugin
     *
     *  @param $table_name name of the table to check fields attributes
     * @param $name name of the field to check validation rules
     *  @return html code for the required classes (null string if there are not any)
    */
    private function generateValidationClasses($table_name, $name) {
        global $data;
        $class_code='';
        $attrs = $data->getTableAttributes($table_name);

        while (!$attrs->EOF) {

            if (($attrs->fields['attnotnull'] == 't') && ($attrs->fields['attname'] == $name))
                $class_code.= "required ";

            if (($attrs->fields['type'] == "date") && ($attrs->fields['attname'] == $name))
                $class_code .= "date ";
            $attrs->moveNext();
        }
        
        if(!empty($class_code)) $class_code= ' class=\"'.trim($class_code).'\" ';

        return $class_code;
    }
    /**
     * Function to generate a browse webpage or a delete webpage from a Page object, delete pages are exactly
     * as the browse page, with the exception of inclution of delete functions, the order and appeareance of fields
     * wich can be different from the browse page
     *
     * @param $page desired page object to generate its file
     * @param $app current aplication's object
     * @param $is_delete_page bool if it should generate a delete page (default creates browsePage)
     * @return bool if this page was created
     */
    private function generateReportPage($path, Application $app, Pages $page) {
        global $lang, $data;
        $function_code='';
        $FKexist = false;
        $add_delete_code= false;

        /* Checks if this table has a page to delete information, if so, adds a
         *  function to delete information inside this report page, */
        $tbl_op = $this->getTableOperations($app,$page->getTable());
        for ($i=0;$i<count($tbl_op["operations"]);$i++) {
            if ($tbl_op['operations'][$i]== 'd'){
                $add_delete_code= true;
                break;
            }
        }

        //Sort this page fields by its order
        $page->sortFieldsByOrder();

        //If updates info at DB then generates the page function and the sql
        $table_code = "\n\techo \"<input type=\\\"hidden\\\" name=\\\"operation\\\" value=\\\"delete\\\" />";
        $table_code = $table_code . "<div class=\\\"center\\\" ><table id=\\\"table\\\">\n\t\t<thead>\n\t\t\t<tr>";
        $table_code = $table_code . "<th scope=\\\"col\\\" class=\\\"table-topleft\\\">{$lang['strselect']}</th>";

        //Searchs for the primary key of this table
        $pk = $this->getPK($app->getDBName(), $page->getTable());

        //variable to counts tables in the sql
        $sql_tbl = 0;
        $sql_extra_tbls = ","; //here it saves the extra tables (if exists)
        $sql_where = " WHERE";    //here saves extra where for fk (if exists)
        if ($pk == -1)  return false;
        $sql = "SELECT a.{$pk},";

        //Adds table's headers to $code and creates the sql sentence
        $num_fld = $page->countShowFields();
        $fields = $page->fields;
        $show_index = 0;
        for ($i = 0; $i < count($fields); $i++) {
            if ($fields[$i]->isOnPage()) {
                $table_code = $table_code . "<th scope=\\\"col\\\" ";

                if ($show_index == $num_fld - 1)
                    $table_code = $table_code . "class=\\\"table-topright\\\"";

                if ($fields[$i]->isFK()) {
                    $sql = $sql . " a{$sql_tbl}." . $fields[$i]->getRemoteField() . ",";
                    $sql_extra_tbls = $sql_extra_tbls . " {$fields[$i]->getRemoteTable()} a{$sql_tbl},";

                    //Checks for remote PK and compares with fk (in the sql sentence)
                    $fk_pk = $this->getPK($app->getDBName(), $fields[$i]->getRemoteTable());
                    $sql_where = $sql_where . " a.{$fields[$i]->getName()}=a{$sql_tbl}.{$fk_pk} AND";
                    $sql_tbl = $sql_tbl + 1;
                }
                else
                    $sql=$sql . " a." . $fields[$i]->getName() . ",";

                $table_code = $table_code . ">{$fields[$i]->getDisplayName()}</th>";
                $show_index = $show_index + 1;
            }
        }
        //checks if the sql setence's parameters ends with comma, then deletes it
        if (substr($sql, -1) == "," ) $sql[strlen($sql) - 1] = " ";
        if (substr($sql_extra_tbls, -1) == "," )
                $sql_extra_tbls[strlen($sql_extra_tbls) - 1] = " ";
        if (substr($sql_where, -3) == "AND")
                $sql_where = substr($sql_where, 0, -3);

        //adds the rest of the sql sentence
        $sql = $sql . " FROM {$app->getSchema()}.{$page->getTable()} a" . $sql_extra_tbls;

        //checks if there are extra where parameters
        if ($sql_where != " WHERE")  $sql = $sql . $sql_where;
        
        $table_code = $table_code . "</tr>\n\t\t</thead>";

        //Adds table's footer
        $table_code = $table_code . "\n\t\t<tfoot>\n\t\t\t<tr>\n\t\t\t\t<td colspan=\\\"{$num_fld}\\\" class=\\\"table-bottomleft\\\"><a href=\\\"#selAll\\\" id=\\\"selectAll\\\">{$lang['strselectall']}</a>&nbsp;-&nbsp;";
        $table_code = $table_code . "<a href=\\\"#unselAll\\\" id=\\\"unSelectAll\\\">{$lang['strunselectall']}</a></td><td colspan=\\\"1\\\" class=\\\"table-bottomright\\\"></td>\n\t\t\t</tr>\n\t\t</tfoot>\n\t\t<tbody>\";";

        //if is a delete page adds deletion request
        if ($add_delete_code) {
            $code = "\n\tif((\$_POST[\"operation\"]==\"delete\")&&(isset(\$_POST[\"selected\"]))){"
                    . "\n\t\t\$success=true;\n\t\tforeach(\$_POST[\"selected\"] as \$row){"
                    . "\n\t\t\tif(deleteRow(\$row)==false){\n\t\t\t\t\$success=false;\n\t\t\t\tbreak;"
                    . "\n\t\t\t}\n\t\t}\n\t\t\$_POST[\"term\"]=\"\";\n\t\t"
                    . "if(\$success==true) \n\t\t\techo \"<p>&nbsp;</p><p class=\\\"warnmsg\\\"><strong>{$lang['strdelsucess']}</strong></p><br /><br />\";"
                    . "\n\t}";
        }
        else
            $code='';

        //First add the db connection to the function's code
        $code .= "\n\tglobal \$conn;\n\t\$extra_sql=\" WHERE 1=1 \";\n\n\t\n\tif(isset(\$_POST[\"term\"]))"
                ."\n\tif(\$_POST[\"term\"]!=\"\"){\n\t\t\$extra_sql.=\"";

        //If this page work with a fk doesn't need to add a WHERE to the sql sentence
        /* if(!$FKexist) $code=$code."WHERE ";
          else  $code=$code."AND "; */
        $code .= "AND CAST(a.{\$_POST[\"column\"]} AS VARCHAR) ILIKE '%{\$_POST[\"term\"]}%'\";\n\t}"
                . "\n\tif(isset(\$_POST[\"column_order\"])){\n\t\t"
                . "\$extra_sql=\$extra_sql.\" ORDER BY a.{\$_POST[\"column_order\"]}\";"
                . "\n\t\tif(\$_POST[\"order\"]==\"asc\")\$extra_sql=\$extra_sql.\" ASC\";"
                . "\n\t\telse \$extra_sql=\$extra_sql.\" DESC\";\n\t }"
                . "\n\tif(!isset(\$_POST[\"limit\"]))\$_POST[\"limit\"]=10;"
                . "\n\tif(!isset(\$_POST[\"offset\"]))\$_POST[\"offset\"]=1;\n"
                . "\n\t\$offset=\$_POST[\"limit\"]*(\$_POST[\"offset\"]-1);"
                . "\n\t\$paginate_sql=\" LIMIT {\$_POST[\"limit\"]}\";"
                . "\n\t\$paginate_sql=\$paginate_sql.\" OFFSET {\$offset}\";\n"
                . "\n\tif (!\$conn) {"
                . "\n\t\t echo \"<p class=\\\"warnmsg\\\"><strong>{$lang['strerrordbconn']}:\".pg_last_error().\"</strong></p><br /><br />\";\n\t\texit;\n\t}"
                . "\n\t\$rs=pg_query(\$conn,\"{$sql}\".\$extra_sql);\n\tif (!\$rs) {\n\t\t"
                . "echo \"<strong>{$lang['strerrorquery']}</strong>\";\n\t\texit;\n\t}\n\t\$rows= pg_num_rows(\$rs);"
                . "\n\t\$rs=pg_query(\$conn,\"{$sql}\".\$extra_sql.\$paginate_sql);";
        //Prints box code
        $code .= "\n\techo \"<div class=\\\"right;\\\">\";\n\t\tif(isset(\$_SESSION['appgen_user'])){"
                . "\n\t\techo \"<div class=\\\"right clear\\\"><a href=\\\"#logout\\\" id=\\\"logOutButton\\\">{$lang['strlogout']}</a></div><p></p>\";\n\t\t}"
                . "\n\t\techo \"<div class=\\\"filter_cell\\\"><label for=\\\"order\\\">{$lang['strorder']}:</label>\n\t\t\t<select  id=\\\"order\\\" name=\\\"order\\\"><option \";\n\t\t\tif(\$_POST[\"order\"]=='asc') echo \"selected=\\\"selected\\\"\"; \n\t\t\techo \" value=\\\"asc\\\">{$lang['strasc']}</option>"
                . "<option \";\n\t\t\tif(\$_POST[\"order\"]=='desc') echo \"selected=\\\"selected\\\"\"; \n\t\t\techo \" value=\\\"desc\\\">{$lang['strdesc']}\n\t\t\t</option></select></div>"
                . "\n\t\t<div class=\\\"filter_cell\\\"><label for=\\\"column_order\\\">{$lang['strsortby']}:</label>\n\t\t\t<select id=\\\"column_order\\\" name=\\\"column_order\\\">" . $this->printOptions($page->getFieldsName(), '$_POST["column_order"]') . "\n\t\t\t</select></div>"
                . "\n\t\t<div class=\\\"filter_cell\\\"><label for=\\\"term\\\">{$lang['strvalue']}:</label>\n\t\t\t<input type=\\\"text\\\" name=\\\"term\\\" id=\\\"term\\\" value=\\\"\".\$_POST[\"term\"].\"\\\" size=\\\"10\\\" /></div>"
                . "\n\t\t<div class=\\\"filter_cell\\\"><label for=\\\"column\\\">{$lang['strcolumn']}:</label>\n\t\t\t<select id=\\\"column\\\" name=\\\"column\\\">" . $this->printOptions($page->getFieldsName(), '$_POST["column"]') . "\n\t\t\t</select></div>\n\t\t"
                . "</div>\";";
        //Paginate radios code
        $code .= "echo \"<div class=\\\"clear-right\\\">\n\t\t"
                . "<div class=\\\"filter_cell\\\">\n\t\t\t<a class=\\\"button sendForm\\\" href=\\\"#refresh\\\" rel=\\\"{$page->getFilename()}\\\"><span>{$lang['strrefresh']}</span></a>\n\t\t</div>"
                . "\n\t<div class=\\\"filter_cell\\\">{$lang['strsrows']}&nbsp;<input type=\\\"radio\\\" name=\\\"limit\\\"  id=\\\"limit-10\\\" value=\\\"10\\\"\";if(\$_POST[\"limit\"]==10 || !isset(\$_POST[\"limit\"])) echo\" checked=\\\"checked\\\"\"; echo \"/>"
                . "\n\t\t<label for=\\\"limit-10\\\">10</label>"
                . "\n\t\t<input type=\\\"radio\\\" name=\\\"limit\\\" id=\\\"limit-20\\\" value=\\\"20\\\"\";if(\$_POST[\"limit\"]==20) echo \" checked=\\\"checked\\\"\"; echo \"/>"
                . "\n\t\t<label for=\\\"limit-20\\\">20</label>"
                . "\n\t\t<input type=\\\"radio\\\" name=\\\"limit\\\" id=\\\"limit-50\\\" value=\\\"50\\\"\";if(\$_POST[\"limit\"]==50) echo \" checked=\\\"checked\\\"\"; echo \"/>"
                . "\n\t\t<label for=\\\"limit-50\\\">50</label>"
                . "\n\t\t<input type=\\\"radio\\\" name=\\\"limit\\\" id=\\\"limit-100\\\" value=\\\"100\\\"\";if(\$_POST[\"limit\"]==100) echo \" checked=\\\"checked\\\"\"; echo \"/>"
                . "\n\t\t<label for=\\\"limit-100\\\">100</label>"
                . "\n\t</div>\n\t</div>\";";
        //Starts printing the table
        $code .= $table_code;

        //Executes the sql and creates the table
        $num_fld += 1;

        //Adds operations buttons
        $buttons_code = $this->generateButtonsCode($app, $page);

        $code .= "\n\twhile (\$row = pg_fetch_array(\$rs)){\n\t\techo \"<tr>\";"
                . "\n\t\tfor(\$i=0;\$i<{$num_fld};\$i=\$i+1){\n\t\t\tif(\$i==0)"
                . "\n\t\t\t\techo \"<td><input class=\\\"checkbox\\\" type=\\\"checkbox\\\" name=\\\"selected[]\\\" value=\\\"{\$row[0]}\\\" /></td>\";"
                . "\n\t\t\telse\n\t\t\t\techo \"<td>\".htmlspecialchars(\$row[\$i]).\"</td>\";\n\t\t}\n\t\techo \"</tr>\";\n\t}"
                . "\n\t//Closes db connection\n\tpg_free_result(\$rs);\n\techo \"</tbody></table></div>\";"
                . "\n\t//Prints pages\n\tprintPagination(\$rows,\$_POST[\"limit\"]);\n\t//Prints operation buttons"
                . "\n\techo \"{$buttons_code}\";";
        
        //Query pages code
        $qpages_code = "\n\tif(\$nrows==0) return'';\n\t\$pnum= ceil(\$nrows/\$nlimit);\n\t\$pcurrent= \$_POST['offset'];\n\t\$max = 10;"
                . "\n\t\$from = \$pcurrent-(\$max/2);\n\t\$to = \$pcurrent+(\$max/2);\n\t\$right = \$pnum-\$pcurrent+1;"
                . "\n\n\tif(\$right<(\$max/2))\$from=\$from - (\$max/2) + \$right;\n\tif(\$from<=0)\$to=\$to-\$from;"
                . "\n\n\tif(\$pcurrent>0)\n\t\techo \"<a class=\\\"pagination\\\" href=\\\"#page\\\" rel=\\\"1\\\">&lt;&lt;</a>&nbsp;\";"
                . "\n\n\tfor(\$i=\$from;\$i<=\$to;\$i++){\n\t\tif(\$pcurrent==\$i) echo \$i;\n\t\telseif(\$i>0 && \$i<\$pnum)"
                . "\n\t\t\techo \"<a class=\\\"pagination\\\" href=\\\"#page\\\" rel=\\\"\$i\\\">\".\$i.\"</a>\";\n\t\tif(\$i>=0 && \$i<\$pnum)echo \" - \";"
                . "\n\t}\n\tif(\$pcurrent < \$pnum)\n\t\techo \"&nbsp;<a class=\\\"pagination\\\" href=\\\"#page\\\" rel=\\\"\$pnum.\\\">&gt;&gt;</a>\";";
        //Check if it is a delete page so adds the delete function
        if ($add_delete_code) {
            $sql = "DELETE FROM {$app->getSchema()}.{$page->getTable()} WHERE {$pk} = '{\$id}'";
            $delete_code = "global \$conn;\n\tif (!\$conn) { echo \"<p class=\\\"warnmsg\\\"><strong>{$lang['strerrordbconn']}:\".pg_last_error().\"</strong></p><br /><br />\"; exit; }"
                    . "\n\t\$rs=pg_query(\$conn,\"{$sql}\");\n\tif (!\$rs) {\n\t\t"
                    . "echo \"<p>&nbsp;</p><p class=\\\"warnmsg\\\"><strong>{$lang['strrowdeletedbad']}</strong><br />\".pg_last_error(\$conn).\"</p><br /><br />\";"
                    . "\n\t\tpg_free_result(\$rs);\n\t\treturn false;\n\t}\n\telse{\n\t\t"
                    . "pg_free_result(\$rs);\n\t\treturn true;\n\t}";
            //Creates the args array for the function
            $args = array();
            $args[] = "\$id";
            $function_code = $this->getFunctionString("deleteRow", $args, $delete_code);
        }
        $function_code .= $this->getFunctionString("printFormAction", "", "echo \"{$page->getFilename()}\";");
        //$function_code .= $this->getFunctionString("printRowsRadio", "", $pagrad_code);
        //$function_code .= $this->getFunctionString("printFilterBox", "", $box_code);
        $args = array("\$nrows", "\$nlimit");
        $function_code .= $this->getFunctionString("printPagination", $args, $qpages_code);
        //Creates the code function
        $function_code .= $this->generateOperationFunction(null, $code);
        return $this->generatePageFile($function_code, $path . $page->getFilename(), $app->getThemeName(), $page);
    }

    /**
     * This function generates an Insert Pages
     * @param $app application object where the $app belongs
     * @param $page Page object wich represents the generating page
     * @return bool if this page was created
     */
    private function generateInsertPage($path, Application $app, Pages $page) {
        global $lang, $data;
        $function_code='';

        $sql = "INSERT INTO {$app->getSchema()}.{$page->getTable()} (";
        $sql_values = ") VALUES (";

        //Sort this page fields by its order
        $page->sortFieldsByOrder();

        //If updates info at DB then generates input page
        $clean_vars_code ="";
        $code = "if(isset(\$_POST[\"operation\"]))\n\tif(\$_POST[\"operation\"]==\"insert\"){\n\t\t\$success= insertRecord();"
                . "\n\t\tif(\$success==true) echo \"<p class=\\\"warnmsg\\\"><strong>{$lang['strinsertsuccess']}</strong></p>\";"
                . "\n\t}\n\t\tif(isset(\$_SESSION['appgen_user'])){"
                . "\n\t\t\techo \"<div class=\\\"right;\\\"> <a href=\\\"#logout\\\" id=\\\"logOutButton\\\">{$lang['strlogout']}</a></div><p></p>\";\n\t\t}"
                . "\n\techo \"<input type=\\\"hidden\\\" name=\\\"operation\\\" value=\\\"insert\\\" />"
                . "\n\t\t<input type=\\\"hidden\\\" name=\\\"page_insert_table\\\" value=\\\"{$page->getTable()}\\\" />"
                . "\n\t\t<table id=\\\"table\\\">\n\t<thead><tr><th scope=\\\"row\\\" class=\\\"table-topleft\\\">"
                . "{$lang['strcolumn']}</th><th scope=\\\"row\\\" class=\\\"table-topright\\\">{$lang['strvalue']}</th></tr></thead>"
                . "\n\t\t<tfoot>\n\t<tr>\n\t\t<td class=\\\"table-bottomleft\\\"></td><td class=\\\"table-bottomright\\\"></td></tr></tfoot>\n\t\t<tbody>";

        //Prints the input box for each field
        $num_fld = $page->countShowFields();
        $fields = $page->fields;
        $show_index = 0;
        for ($i = 0; $i < count($fields); $i++) {
            if ($fields[$i]->isOnPage()) {
                $clean_vars_code .="\n\tif(!isset(\$_POST[\"{$fields[$i]->getName()}\"])) \$_POST[\"{$fields[$i]->getName()}\"]='';";
                $code .= "\n\t\t\t<tr><td>{$fields[$i]->getDisplayName()}</td>";
                if ($fields[$i]->isFK()) {
                    $code .= "<td><select name=\\\"{$fields[$i]->getName()}\\\" class=\\\"almost-full-wide\\\">\";"
                            . "printFKOptions('{$app->getSchema()}','{$fields[$i]->getRemoteTable()}',"
                            . "'{$this->getPK($app->getDBName(), $fields[$i]->getRemoteTable())}','{$fields[$i]->getRemoteField()}'); echo \"</select></td></tr>";
                } else {
                   $class_code = $this->generateValidationClasses($page->getTable(),$fields[$i]->getName());
                    $code .= "<td><input type=\\\"text\\\" name=\\\"{$fields[$i]->getName()}\\\"  {$class_code} value=\\\"{\$_POST[\"{$fields[$i]->getName()}\"]}\\\"/></td></tr>";
                }
        //Constructs SQL DATA
                $sql = $sql . $fields[$i]->getName() . ",";
                $sql_values = $sql_values . "'{\$_POST[\"{$fields[$i]->getName()}\"]}',";
            }
        }
        //checks if the sql setence's parameters ends with comma, then deletes it
        if (substr($sql, -1) == ",") $sql[strlen($sql) - 1] = " ";
        if (substr($sql_values, -1) == "," ) $sql_values[strlen($sql_values) - 1] = ")";

        $printfk_code = "global \$conn;\n\t\n\tif (!\$conn) { echo \"<p  class=\\\"warnmsg\\\"><strong>{$lang['strerrordbconn']}:\".pg_last_error().\"</strong></p>\"; exit; }"
                . "\n\t\$rs=pg_query(\$conn,\"SELECT \".\$pk.\",\".\$field.\" FROM \".\$schema.\".\".\$table);"
                . "\n\tif (!\$rs) {\n\t\techo \"<p  class=\\\"warnmsg\\\"><strong>{$lang['strerrorquery']}</strong></div>\"; exit;\n\t}"
                . "\n\twhile (\$row = pg_fetch_array(\$rs)){\n\t\t"
                . "echo \"<option value=\\\"{\$row[0]}\\\">{\$row[1]}</option>\";\n\t}\n\tpg_free_result(\$rs);";

        $insert_code = "global \$conn;\n\tif (!\$conn) { echo \"<p><strong>{$lang['strerrordbconn']}:\".pg_last_error().\"</strong></p>\"; exit; }"
                . "\n\t\$rs=pg_query(\$conn,\"{$sql}{$sql_values}\");\n\tif (!\$rs) {\n\t\t"
                . "echo \"<p class=\\\"warnmsg\\\"><strong>{$lang['strinsertfail']}</strong><br />\".pg_last_error(\$conn).\"</p>\";"
                . "\n\t\treturn false;\n\t}\n\telse{\n\t\tpg_free_result(\$rs);\n\t\treturn true;\n\t}";

        $code .= "\n\t\t</tbody>\n\t</table>";
        //Adds operations buttons
        $buttons_code = $this->generateButtonsCode($app, $page);
        $code .= "{$buttons_code}\";";

        //Creates the code function
        $function_code .= $this->getFunctionString("printRowsRadio", "", "return null;");
        $function_code .= $this->getFunctionString("printFilterBox", "", "return null;");
        $function_code .= $this->getFunctionString("printFormAction", "", "echo \"{$page->getFilename()}\";");
        $args = array("\$schema,\$table", "\$pk", "\$field");
        $function_code .= $this->getFunctionString("printFKOptions", $args, $printfk_code);
        $function_code .= $this->getFunctionString("insertRecord", "", $clean_vars_code.$insert_code);
        $function_code .= $this->generateOperationFunction(null,$clean_vars_code.$code);
        return $this->generatePageFile($function_code, $path . $page->getFilename(), $app->getThemeName(), $page);
    }

    /**
     * This function generates an Update Page
     * @param $app application object where the $app belongs
     * @param $page Page object wich represents the generating page
     * @return bool if this page was created
     */
    private function generateUpdatePage($path, Application $app, Pages $page) {
        global $lang, $data;
        $function_code='';

        $sql = "UPDATE {$app->getSchema()}.{$page->getTable()} SET ";
        $sql_array = "\$set_sql=array(";
        $sql_where = " WHERE {$this->getPK($app->getDBName(), $page->getTable())}='{\$id}'";

        //Sort this page fields by its order
        $page->sortFieldsByOrder();

        //If updates info at DB then generates input page
        $code = "\n\tif(isset(\$_POST[\"uindex\"]))\$uindex=\$_POST[\"uindex\"];"
                . "\n\telse \$uindex=0;\n\tif(isset(\$_POST[\"selected\"])) {\n\t\t"
                . "\$_SESSION[\"selected\"]=\$_POST[\"selected\"];\n\t}"
                . "\n\tif(isset(\$_POST[\"operation\"]))\n\tif(\$_POST[\"operation\"]==\"update\"){\n\t\t\$success= updateRow(\$_SESSION[\"selected\"][\$uindex]);"
                . "\n\tif(\$success==true) {\n\t\techo \"<p class=\\\"warnmsg\\\"><strong>{$lang['strupdatesuccess']}</strong></p>\";"
                . "\n\t\t\$uindex=\$uindex+1;\n\t\techo \"<input type=\\\"hidden\\\" name=\\\"uindex\\\" value=\\\"\".\$uindex.\"\\\" />\";"
                . "\n\t}\n\tif(\$uindex==count(\$_SESSION[\"selected\"])){\n\t\tunset(\$_POST[\"operation\"]);"
                . "\n\t\tunset(\$_SESSION[\"selected\"]);\n\t}\n\telse{\n\t\t\$_POST[\"operation\"]=\"edit\";\n\t}\n}"
                . "\n\tif(isset(\$_SESSION[\"selected\"])&&(\$_POST[\"operation\"]==\"edit\")){\n\t\t"
                . "\n\t\tif(isset(\$_SESSION['appgen_user'])){"
                . "\n\t\techo \"<div class=\\\"right;\\\"> <a href=\\\"#logout\\\" id=\\\"logOutButton\\\">{$lang['strlogout']}</a></div><p></p>\";\n\t\t}"
                . "echo \"<p class=\\\"left\\\">{$lang['streditrecord']} \".(\$uindex+1).\"{$lang['streditof']}\".count(\$_SESSION[\"selected\"]).\"</p><div class=\\\"clear\\\"></div>\";"
                . "\n\t\tglobal \$conn;\n\t\tif (!\$conn) { echo \"<p class=\\\"warnmsg\\\"><strong>{$lang['strerrordbconn']}:\".pg_last_error().\"</strong></p>\"; exit; }"
                . "\n\t\t\$cant=count(\$_SESSION[\"selected\"]);"
                . "\n\t\t\$query=\"SELECT ";

        //search for selected columns to update
        $num_fld = $page->countShowFields();
        $fields = $page->fields;
        //Constructs SQL select sentence to retrieve data to be modified
        for ($i = 0; $i < count($fields); $i++) {
            if ($fields[$i]->isOnPage()) {
                $code .= "{$fields[$i]->getName()},";
                //$sql=$sql." {$fields[$i]->getName()}='{\$_POST[\"{$fields[$i]->getName()}\"]}',";
                $sql_array = $sql_array . "\"{$fields[$i]->getName()}\",";
            }
        }
        //delete last comma
        if (substr($code, -1) == ","

            )$code[strlen($code) - 1] = " ";
//      if(substr($sql, -1)==",")$sql[strlen($sql)-1]=" ";
        if (substr($sql_array, -1) == ","

            )$sql_array[strlen($sql_array) - 1] = " ";

        $sql_array = $sql_array . ");";
        $code .= " FROM {$app->getSchema()}.{$page->getTable()} WHERE "
                . "{$this->getPK($app->getDBName(), $page->getTable())}=\";"
                . "\n\t\tif(\$cant>1) \$query=\$query.\"{\$_SESSION[\"selected\"][\$uindex]}\";"
                . "\n\t\telse \$query=\$query.\"{\$_SESSION[\"selected\"][0]}\";"
                . "\n\t\t\$rs=pg_query(\$conn,\$query);"
                . "\n\t\tif (!\$rs) {\n\t\t\techo \"<strong>{$lang['strerrorquery']}</strong>\"; exit;\n\t\t}"
                . "\n\t\t\$row = pg_fetch_array(\$rs);\n\t\tif(!\$row){echo \"{$lang['strrecordnoexist']}\";exit;}\n\t\t"
                . "echo \"<input type=\\\"hidden\\\" name=\\\"operation\\\" value=\\\"update\\\" />\n\t\t"
                . "<input type=\\\"hidden\\\" name=\\\"uindex\\\" value=\\\"\".\$uindex.\"\\\" />"
                . "<table id=\\\"table\\\">\n\t\t<thead><tr><th scope=\\\"row\\\" class=\\\"table-topleft\\\">"
                . "{$lang['strcolumn']}</th><th scope=\\\"row\\\" class=\\\"table-topright\\\">{$lang['strvalue']}</th></tr></thead>"
                . "<tfoot>\n\t\t<tr>\n\t\t<td class=\\\"table-bottomleft\\\"></td><td class=\\\"table-bottomright\\\"></td></tr></tfoot>\n\t\t<tbody>";

        $show_index = 0;
        //Prints the input box for each field
        for ($i = 0; $i < count($fields); $i++) {
            if ($fields[$i]->isOnPage()) {
                $code .= "\n\t\t\t<tr><td>{$fields[$i]->getDisplayName()}</td>";
                if ($fields[$i]->isFK()) {
                    $code .= "<td><select name=\\\"{$fields[$i]->getName()}\\\" class=\\\"full-wide\\\">\";"
                            . "printFKOptions('{$app->getSchema()}','{$fields[$i]->getRemoteTable()}',"
                            . "'{$this->getPK($app->getDBName(), $fields[$i]->getRemoteTable())}','{$fields[$i]->getRemoteField()}',\$row[{$show_index}]); echo \"</select></td></tr>";
                } else {
        //checks if attribute is null or if it is date
                    $class_code=$this->generateValidationClasses($page->getTable(),$fields[$i]->getName());
                    $code .= "<td><input type=\\\"text\\\" name=\\\"{$fields[$i]->getName()}\\\" {$class_code} value=\\\"\".htmlspecialchars(\$row[{$show_index}]).\"\\\"/></td></tr>";
                }
                $show_index = $show_index + 1;
            }
        }
        $code .= "\n\t\t</tbody>\n\t</table>";
        //Prints operation buttons
        $buttons_code = $this->generateButtonsCode($app, $page);
        $only_right_buttons= $this->generateButtonsCode($app, $page,true);
        //Code for print foreing key values in a select input
        $printfk_code = "global \$conn;\n\t"
                . "if (!\$conn) { echo \"<p  class=\\\"warnmsg\\\"><strong>{$lang['strerrordbconn']}:\".pg_last_error().\"</strong></p>\"; exit; }"
                . "\n\t\$rs=pg_query(\$conn,\"SELECT \".\$pk.\",\".\$field.\" FROM \".\$schema.\".\".\$table);"
                . "\n\tif (!\$rs) {\n\t\techo \"<strong>{$lang['strerrorquery']}</strong>\"; exit;\n\t}"
                . "\n\twhile (\$row = pg_fetch_array(\$rs)){\n\t\t"
                . "echo \"<option value=\\\"{\$row[0]}\\\"\";"
                . "\n\t\tif(\$row[0]==\$selected_pk) echo\" selected=\\\"selected\\\" \";"
                . "\n\t\techo \">{\$row[1]}</option>\";\n\t}\n\tpg_free_result(\$rs);";

        //Code for updating information
        $update_code = "global \$conn;\n\t{$sql_array}\n\t\$sql_args=\"\";"
                . "\n\tforeach(\$set_sql as \$update_column){\n\t\t"
                . "if(\$_POST[\$update_column]==\"\")\n\t\t\t\$sql_args=\$sql_args.\"{\$update_column}=NULL,\";"
                . "\n\t\telse\n\t\t\t\$sql_args=\$sql_args.\"{\$update_column}='{\$_POST[\$update_column]}',\";\n\t}"
                . "\n\tif(substr(\$sql_args, -1)==\",\")\$sql_args[strlen(\$sql_args)-1]=\" \";"
                . "\n\tif (!\$conn) { echo \"<p  class=\\\"warnmsg\\\"><strong>{$lang['strerrordbconn']}:\".pg_last_error().\"</strong></p>\"; exit; }"
                . "\n\t\$rs=pg_query(\$conn,\"{$sql} {\$sql_args} {$sql_where}\");"
                . "\n\tif (!\$rs) {\n\t\techo \"<p></p><p class=\\\"warnmsg\\\"><strong>{$lang['strupdatefail']}</strong><br />\".pg_last_error(\$conn).\"</p>\";"
                . "\n\t\tpg_free_result(\$rs);\n\t\treturn false;\n\t}"
                . "\n\telse{\n\t\tpg_free_result(\$rs);\n\t\treturn true;\n\t}";
        
        /***Box for request for a pk if none was sent**/
        //Search for the report page's filename to create a link to go back
        $tbl_op = $this->getTableOperations($app, $page->getTable());
        $report_filename='';

        if(count($tbl_op)>0){
            $i= array_search('b',$tbl_op['operations']);

            if($i!==false)
                $report_filename = $tbl_op['filenames'][$i];
        }

        if(!empty($report_filename))
            $gobacklink = str_replace('{URL}',"\\\"".$report_filename."\\\"",$lang['gobackreport']);
        else
            $gobacklink = '';
        
        $pk_request = "<p class=\\\"warnmsg\\\"><span>{$lang['strnoupdateargs']}<br />{$gobacklink}</span></p>
                    <p>{$lang['strselupdatetxt']}</p>
                    <input type=\\\"hidden\\\" name=\\\"operation\\\" value=\\\"edit\\\" />
                    <div class=\\\"centerdiv input-key\\\">
                        <div>{$lang['strprimarykey']}&nbsp;:</div>
                        <div><input type=\\\"text\\\" class=\\\"required\\\" name=\\\"selected\\\" value=\\\"\\\" /></div>
                    </div>
                    <div class=\\\"full-wide\\\"><div class=\\\"center-buttons\\\">
                        <a class=\\\"button sendForm\\\" href=\\\"#u\\\" rel=\\\"{$page->getFilename()}\\\"><span>{$lang['stredit']}</span></a>
                    </div></div>{$only_right_buttons}";
        $code .= "{$buttons_code}\";\n\t\t}\n\tif(!isset(\$_POST[\"operation\"])|| (count(\$_POST[\"selected\"])<1)){"
                . "\n\t\techo \"{$pk_request}\";\n\t}";

        //Creates the code function
        $function_code .= $this->getFunctionString("printRowsRadio", "", "return null;");
        $function_code .= $this->getFunctionString("printFilterBox", "", "return null;");
        $function_code .= $this->getFunctionString("printFormAction", "", "echo \"{$page->getFilename()}\";");
        $function_code .= $this->getFunctionString("updateRow", "\$id", $update_code);
        $args = array("\$schema,\$table", "\$pk", "\$field", "\$selected_pk");
        $function_code .= $this->getFunctionString("printFKOptions", $args, $printfk_code);
        $function_code .= $this->generateOperationFunction(null, $code);
        return $this->generatePageFile($function_code, $path . $page->getFilename(), $app->getThemeName(), $page);
    }

    /**
     * Generates code for this page's operation's function
     * @param $args string array with args required for the function
     * @param $code string with the function's code
     * @return string with generated function
     */
    private function generateOperationFunction($args, $code) {
        $strfunction = "\n\nfunction pageOperation(";
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
     * This function generates the page file
     * @param $code string with the operation's function code
     * @param $filename page's filename
     * @param $app  current application object
     * @return true if everything went ok
     */
    private function generatePageFile($code, $filename, $theme_name, Pages $page) {
        //Retrieves all content from current theme's file
        $fTheme = file_get_contents("./themes/appgen/" . $theme_name . "/index.php");

        $title = $page->getTitle();
        $descr = $page->getDescription();
        $txt = $page->getPageText();

        //Adds page's operation function
        if (empty($title))
            $title = '&nbsp;';
        if (empty($descr))
            $descr = '&nbsp;';
        if (empty($txt))
            $txt = '&nbsp;';

        $functions = $this->getFunctionString("printPageTitle", "", "echo '{$title}';");
        $functions .= $this->getFunctionString("printPageDescr", "", "echo '{$descr}';");
        $functions .= $this->getFunctionString("printPageText", "", "echo '{$txt}';");

        $fTheme = "<?php\n" . $functions . $code . "\n?>\n" . $fTheme;
        $fPage = fopen($filename, "w");
        if (!$fPage)
            return false;
        fwrite($fPage, $fTheme);
        fclose($fPage);
        return true;
    }

    /**
     * This function generate buttons for a page, this buttons let navigate trought
     * all pages that interact with current page's db table
     * @param Application $app current aplication object
     * @param Pages $page page object where the buttons will be inserted
     * @param $noMainButtons flag to check if print main action buttons or not
     * @return string html code for buttons
     */
    private function generateButtonsCode (Application $app, Pages $page, $noMainButtons=false) {
        global $lang;
        $buttons_code='';
        $tbl_op = $this->getTableOperations($app, $page->getTable());
        
        if(!$noMainButtons) {
            $buttons_code = "\n\t<div class=\\\"full-wide\\\"><div class=\\\"center-buttons\\\">";
            switch ($page->getOperation()) {
                case "delete": $buttons_code .= "\t<a id=\\\"deleteButton\\\" class=\\\"button sendForm\\\" href=\\\"#d\\\" rel=\\\"{$page->getFilename()}\\\"><span>{$lang['strdelete']}</span></a>";
                    break;
                case "insert": $buttons_code .= "\t<a id=\\\"insertButton\\\" class=\\\"button sendForm\\\" href=\\\"#i\\\" rel=\\\"{$page->getFilename()}\\\"><span>{$lang['strinsert']}</span></a>";
                    break;
                case "update":
                    $buttons_code .= "\t<a class=\\\"button sendForm\\\" href=\\\"#u\\\" rel=\\\"{$page->getFilename()}\\\"><span>{$lang['stredit']}</span></a>";
                    $buttons_code .= "\t<a class=\\\"button\\\" href=\\\"{$page->getFilename()}\\\" ><span>{$lang['strcancel']}</span></a>";
                    break;
            }
            $buttons_code .= "\t</div></div>";
        }
        $buttons_code .= "<div class=\\\"right-buttons\\\">";
        $buttons_code .= "\n\t<div class=\\\"clear\\\">";

        for ($i=0;$i<count($tbl_op["operations"]);$i++) {
            $cur_op = $page->getOperation();
            if ($tbl_op['operations'][$i]!= $cur_op[0])
                switch ($tbl_op['operations'][$i]) {
                    case "d": 
                        if($cur_op=='update')
                            $buttons_code .= "\n\t\t<a id=\\\"deleteButton\\\" class=\\\"button\\\" href=\\\"#d\\\" rel=\\\"{$tbl_op['filenames'][$i]}\\\"><span>{$lang['strdelete']}</span></a>";
                        if($cur_op=='browse')
                            $buttons_code .= "\n\t\t<a id=\\\"deleteReportButton\\\" class=\\\"button\\\" href=\\\"#d\\\" rel=\\\"{$page->getFilename()}\\\"><span>{$lang['strdelete']}</span></a>";
                        break;
                    case "i":
                        $buttons_code .= "\n\t\t<a id=\\\"insertButton\\\" class=\\\"button\\\" href=\\\"{$tbl_op['filenames'][$i]}\\\"><span>{$lang['strinsert']}</span></a>";
                        break;
                    case "u":
                        if($cur_op!='insert')
                            $buttons_code .= "\n\t\t<a id=\\\"updateButton\\\" class=\\\"button\\\" href=\\\"#i\\\" rel=\\\"{$tbl_op['filenames'][$i]}\\\"><span>{$lang['stredit']}</span></a>";
                        break;
                    case "b":
                        $buttons_code .= "\n\t\t<a id=\\\"reportButton\\\" class=\\\"button\\\" href=\\\"{$tbl_op['filenames'][$i]}\\\"><span>{$lang['strreports']}</span></a>";
                        break;
                }
        }
        $buttons_code .= "\n\t</div>\n\t</div>";
        return $buttons_code;
    }

    /**
     * Returns an array of operations that an applications do in a specific table
     * @param $app an application object to get its operations
     * @param $table name of the table
     * @param $filename_array optional array to store page's filename of operations array
     * @return array of operations (b=browse, d=deletion, i=insert, u=update) and
     *               each respective filename 
     */
    private function getTableOperations(Application $app, $table) {
        global $misc;
        $tbl_op = array();
        $tbl_op['operations'] = array();
        $tbl_op['filenames'] = array();

        $driver = $misc->getDatabaseAccessor("phppgadmin");
        $sql = "SELECT p.page_filename, p.operation FROM appgen.pages p, appgen.page_tables pt,appgen.application a "
                . "WHERE pt.table_name='{$table}' AND a.app_id='{$app->getId()}' "
                . "AND p.app_id=a.app_id AND pt.pages_page_id=p.page_id";
        $rs = $driver->selectSet($sql);

        $i=0;
        foreach ($rs as $row) {
            $tbl_op['operations'][] = $rs->fields['operation'];
            $tbl_op['filenames'][] = $rs->fields['page_filename'];
            $i++;
        }
        return $tbl_op;
    }

    /**
     * Prints options for a html combo-box
     * @param $array an array with values for the combo box
     * @param $select compare value to print select
     * @return string with html code for options
     */
    public function printOptions($array, $select) {
        $html_code='';
        $i = 0;
        foreach ($array as $value) {
            $html_code = $html_code . "\n\t\t\t\t<option \";";
            $html_code = $html_code . " if({$select}=='{$value}') echo \" selected=\\\"selected\\\"\"; echo \" ";
            $html_code = $html_code . ">{$value}</option>";
            $i++;
        }
        return $html_code;
    }

    /**
     * Prints options for a html combo-box and receives a value to select by default
     * @param $array an array with values for the combo box
     * @param $sel_value value of selected index
     * @return string with html code for options
     */
    public function printSelOptions($array, $sel_value) {
        $html_code='';
        foreach ($array as $value) {
            $html_code .= "\n\t\t\t\t<option";
            if ($value == $sel_value)
                $html_code .= " selected=\"selected\"";
            $html_code .= ">{$value}</option>\n";
        }
        return $html_code;
    }

    /**
     * Prints error and returns false due to error
     *
     * @return bool false due to error
     */
    private function printError($error_text) {
        global $misc;
        $misc->printMsg($error_text);
        return false;
    }

    /**
     * Returns a string with a function code to write it on a file
     * @param $name function's name
     * @param $args an array with the function arguments
     * @param $code the code inside the function, if it has return include it too
     * @return string with complete function code
     */
    public function getFunctionString($name, $args, $code) {
        $strfunction = "\n\nfunction {$name}(";
        $argc = count($args);
        $i = 0;
        if (is_array($args))
            foreach ($args as $arg) {
                $strfunction = $strfunction . $arg;
                if ($i < $argc - 1)
                    $strfunction = $strfunction . ",";
                $i++;
            }
        else
            $strfunction = $strfunction . $args;


        $strfunction .= "){\n";
        $strfunction .= "\t" . $code;
        $strfunction .= "\n}\n";

        return $strfunction;
    }

    /**
     * Function to generate a page from a Page object
     * @param $page desired page object to generate its file
     * @return true if everything went ok
     * @return false if something bad happened like an error
     */
    public function generatePage($path, Application $app, Pages $page) {
        switch ($page->getOperation()) {
            case "browse": return $this->generateReportPage($path, $app, $page, false);
                break;
            case "insert": return $this->generateInsertPage($path, $app, $page);
                break;
            case "update": return $this->generateUpdatePage($path, $app, $page);
                break;
            case "delete": return $this->generateReportPage($path, $app, $page, true);
                break;
        }
        return true;
    }

    /**
     * This function validates parameters from a browse page
     * @return bool of accepted or not parameters from a browse page
     */
    public function validateParameters() {
        global $lang, $misc;

        //Checks if page's filename is not null or doesn't have extension
        if (($_POST['page_filename'] == "") || (substr($_POST['page_filename'], -4) != ".php") || !isset($_POST["page_filename"]))
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

    public function createZipFile($source, $destination) {
        if (extension_loaded('zip') === true) {
            if (file_exists($source) === true) {
                $zip = new ZipArchive();

                if ($zip->open($destination, ZIPARCHIVE::CREATE) === true) {
                    $source = realpath($source);

                    if (is_dir($source) === true) {
                        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

                        foreach ($files as $file) {
                            $file = realpath($file);

                            if (is_dir($file) === true) {
                                $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                            } else if (is_file($file) === true) {
                                $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                            }
                        }
                    } else if (is_file($source) === true) {
                        $zip->addFromString(basename($source), file_get_contents($source));
                    }
                    return $zip->close();
                }
            }
        }
        return false;
    }

//--------------------------------------------------------------
}
?>