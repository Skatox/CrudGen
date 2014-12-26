<?php
/**
 * Class for generating PGSQL (obsolete) code
 */
class GenPsql extends CodeGen 
{
	public $lang;

	function __construct($lang)
    {
    	$this->lang = $lang;
    }

	public function getConnection($user='DB_USER', $password='DB_PASS')
	{
		$code = "\n\t\$conn = pg_connect(\"host='\" . DB_HOST . \"' "
            . "port='\" . DB_PORT . \"' password='\" . " . $password . " . \"' "
            . "user='\" . " . $user . " . \"' dbname='\" . DB_NAME . \"'\");";

		return $code;
	}

	public function getLoginByDbUser()
	{
		$code = "\t\tglobal \$conn;"
            . "\n\n\t\tif(isset(\$_SESSION['crudgen_user']) "
            . "&& isset(\$_SESSION['crudgen_passwd']) ){\n\t\t\t"
            . $this->getConnection("\$_SESSION['crudgen_user']", "\$_SESSION['crudgen_passwd']")
            . "\n\t\t\treturn true;"
            . "\n\t\t} else {"
            . "\n\t\t\tif(isset(\$_POST['crudgen_user']) "
            . "&& isset(\$_POST['crudgen_passwd']) ){\n\t\t\t\t\t"
            . $this->getConnection("\$_POST['crudgen_user']", "\$_POST['crudgen_passwd']")
            . "\n\t\t\t\tif(\$conn){"
            . "\n\t\t\t\t\t\$_SESSION['crudgen_user']=\$_POST['crudgen_user'];"
            . "\n\t\t\t\t\t\$_SESSION['crudgen_passwd']=\$_POST['crudgen_passwd'];"
            . "\n\t\t\t\t\treturn true;"
            . "\n\t\t\t\t}else {"
            . "\n\t\t\t\t\t\$_SESSION['error']=\"{$this->lang['strloginerror']}\";"
            . "\n\t\t\t\t\tinclude \"login.inc.php\";"
            . "\n\t\t\t\t\treturn false;"
            . "\n\t\t\t\t}"
            . "\n\t\t\t} else {"
            . "\n\t\t\t\tinclude \"login.inc.php\";"
            . "\n\t\t\t\treturn false;"
            . "\n\t\t\t}"
            . "\n\t\t}";

         return $code;
	}

	public function getLoginByDbTable($schema, $table, $authUser, $authPassword)
	{
		$code = "\t\tglobal \$conn;\n"
            . "\n\t\tif(isset(\$_SESSION['crudgen_user']))"
            . "\n\t\t\treturn true;"
            . "\n\t\telse{"
            . "\n\t\t\tif(isset(\$_POST['crudgen_user']) "
            . "&& isset(\$_POST['crudgen_passwd']) ){\n\t\t\t\t"
			. "\$query=sprintf(\"SELECT {$authUse},{$authPassword} "
            . "\n\t\t\t\t\t\tFROM {$schema}.{$table} "
            . "\n\t\t\t\t\t\tWHERE {$authUse}='%s' AND {$authPassword}='%s'\","
            . "\$_POST['crudgen_user'],\$_POST['crudgen_passwd']);"
            . "\n\t\t\t\t\$rs=pg_query(\$conn,\$query);\n\t\t\t\t"
            . "if(pg_num_rows(\$rs)){\n\t\t\t\t\t"
            . "\$_SESSION['crudgen_user'] = \$_POST['crudgen_user'];"
            . "\n\t\t\t\t\treturn true;"
            . "\n\t\t\t\t} else {"
            . "\n\t\t\t\t\t\$_SESSION['error']=\"{$this->lang['strloginerror']}\";"
            . "\n\t\t\t\t\tinclude \"login.inc.php\";"
            . "\n\t\t\t\t}"
            . "\n\t\t\t} else {"
            . "\n\t\t\t\tinclude \"login.inc.php\";"
            . "\n\t\t\t}"
            . "\n\t\t}";

		return $code;
	}

	public function printFkOptions()
    {
        $code = "\t\tglobal \$conn;\n"
	   		. "\n\t\tif (!\$conn) { "
	        . "\n\t\t\t\$_SESSION['error'] = "
	        . "\"{$this->lang['strerrordbconn']}:\" . pg_last_error();"
	        . "\n\t\t\texit;"
	        . "\n\t\t}"
	        . "\n\n\t\ttry {"
	        . "\n\t\t\t\$rs = pg_query(\$conn, sprintf(\"SELECT %s,%s "
	        ." FROM %s.%s\", \$pk, \$field, \$schema, \$table));"
	        . "\n\t\t} catch (Exception \$e) {"
	        . "\n\t\t\t\$rs = NULL;"
	        . "\n\t\t}"
	        . "\n\n\t\tif (!\$rs) {"
	        . "\n\t\t\t\$_SESSION['error'] = \"{$this->lang['strerrorquery']}\";"
	        . "\n\t\t\texit;"
	        . "\n\t\t}"
	        . "\n\t\twhile (\$row = pg_fetch_array(\$rs)){"
	        . "\n\t\t\techo \"<option value=\\\"{\$row[0]}\\\"\";"
	        . "\n\t\t\tif(\$row[0] == \$selected_pk) echo \" selected=\\\"selected\\\"\";"
	        . "\n\t\t\techo \">{\$row[1]}</option>\";"
	        . "\n\t\t};"
	        . "\n\n\t\tpg_free_result(\$rs);";

	    return $code;
   	}

   	public function getLoadRecord($sql)
   	{
   		$code = "\n\t\t\tglobal \$conn;\n"
            . "\n\t\t\tif (!\$conn) {"
            . "\"{$this->lang['strerrordbconn']}:\" . pg_last_error();"
            . "\n\t\t\t\t\texit;"
            . "\n\t\t\t\t}"
            . "\n\t\t\t\t\$cant = count(\$_SESSION[\"selected\"]);"
            . "\n\t\t\t\t\$id = \$cant > 1 ? "
            . "\$_SESSION[\"selected\"][\$index] : \$_SESSION[\"selected\"][0];"
            . "\n\t\t\t\t\$query = sprintf(\"{$sql}=%s\", \$id);\n"
            . "\n\t\t\t\t\$rs = pg_query(\$conn, \$query);\n"
            . "\n\t\t\t\tif (!\$rs){"
            . "\n\t\t\t\t\t\$_SESSION['error'] = \"{$this->lang['strerrorquery']}\";"
            . "\n\t\t\t\t\texit;"
            . "\n\t\t\t\t}\n"
            . "\n\t\t\t\t\$row = pg_fetch_array(\$rs);\n";

        return $code;
   	}

   	public function getCreateCode($schema, $table, $columns, $clearVars)
   	{
   		$column_names = array();

   		foreach ($columns as $column)
            if ($column->isOnPage()) {
            	$values[] = "clearVars(\$_POST[\"{$column->getName()}\"])";
            	$sprintf[] = "%s";
            	$column_names[] = $column->getName();
            }

        $sql = "\n\t\t\tsprintf(\"INSERT INTO {$schema}.{$table}"
            . " (" . implode(",", $column_names) . ") "
            . "\n\t\t\t\tVALUES (" . implode(",", $sprintf) . ")\",\n\t\t\t\t"
            .  implode(",\n\t\t\t\t", $values) . ")";

   		$code = "\t\tglobal \$conn;"
            . "\n\t\tif (!\$conn) {"
			. "\n\t\t\t\$_SESSION['error'] = "
            . "\"{$this->lang['strerrordbconn']}: \" . pg_last_error();"
            . "\n\t\t\texit;"
            . "\n\t\t}\n"
            . $clearVars
            . "\n\n\t\ttry {"
            . "\n\t\t\t\$rs = pg_query(\$conn,{$sql});\n"
            . "\n\t\t} catch (Exception \$e) {"
            . "\n\t\t\t\$rs = NULL;"
            . "\n\t\t}"
            . "\n\t\tif (!\$rs) {"
            . "\n\t\t\t\$_SESSION['error'] = "
            . "\"{$this->lang['strinsertfail']}:\" . pg_last_error( \$conn );"
            . "\n\t\t\treturn false;"
            . "\n\t\t} else {"
            . "\n\t\t\t\$_SESSION['msg'] = \"{$this->lang['strinsertsuccess']}\";";

        foreach ($column_names as $column)
            $code .= "\n\t\t\t\$_POST['{$column}'] = '';";

        $code .= "\n\t\t\tpg_free_result(\$rs);"
       		. "\n\t\t\treturn true;"
            . "\n\t\t}";

        return $code;
   	}

   	public function getReportCode($selects, $from, $joins)
   	{
   		$sql = "SELECT " . implode(",", $selects)
            . "\n\t\t\t\tFROM " . $from . implode(" ", $joins);

   		$code = "\n\n\t\tglobal \$conn;"
            . "\n\t\t\$extra_sql=\" WHERE 1=1\";"
            . "\n\t\n\t\tif(isset(\$_POST[\"filter-term\"])&& isset(\$_POST['filter-column']))"
            . "\n\t\t\tif(!empty(\$_POST[\"filter-term\"]) && !empty(\$_POST['filter-column'])){"
            . "\n\t\t\t\t\$extra_sql.= sprintf("
            . "\" AND CAST(%s  AS VARCHAR) ILIKE '%s'\", \$_POST[\"filter-column\"],"
            . " \"%{\$_POST[\"filter-term\"]}%\");"
            . "\n\t\t\t} else"
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
            . "\$_SESSION['error'] = \"{$this->lang['strerrordbconn']}: \".pg_last_error();"
            . "\n\t\t\texit;"
            . "\n\t\t}\n"
            . "\n\t\t\$rs = pg_query(\$conn, \"{$sql}\".\$extra_sql);"
            . "\n\n\t\tif (!\$rs) {"
            . "\n\t\t\t\$_SESSION['error'] = \"{$this->lang['strerrorquery']}\";"
            . "\n\t\t\texit;"
            . "\n\t\t}"
            . "\n\t\t\$rows = pg_num_rows(\$rs);"
            . "\n\t\t\$rs = pg_query(\$conn,\"{$sql}\".\$extra_sql.\$paginate_sql);";

        return $code;
   	}

   	public function getFetchCode()
   	{
   		return '$row = pg_fetch_array($rs)';
   	}

   	public function getUpdateCode($sql, $columns)
   	{
   		$code = "\t\tglobal \$conn;\n"
            . "\n\t\t\$columns = array(" . implode(',', $columns) . ");"
            . "\n\t\t\$sql_set = array();"
            . "\n\n\t\tforeach(\$columns as \$column){"
            . "\n\t\t\tif(\$_POST[\$column] == \"\")"
            . "\n\t\t\t\t\$sql_set[] = \"{\$column} = NULL\";"
            . "\n\t\t\telse"
            . "\n\t\t\t\t\$sql_set[] = \"{\$column} = '{\$_POST[\$column]}'\";"
            . "\n\t\t}\n"
            . "\n\t\tif (!\$conn ) {"
            . "\n\t\t\t\$_SESSION['error'] = "
            . "\"{$this->lang['strerrordbconn']}: \" . pg_last_error();"
            . "\n\t\t\texit;"
            . "\n\t\t}\n"
            . "\n\t\t\$rs = pg_query(\$conn,{$sql});\n"
            . "\n\t\tif (!\$rs) {"
            . "\n\t\t\t\$_SESSION['error'] = "
            . "\"{$this->lang['strupdatefail']}: \" . pg_last_error(\$conn);"
            . "\n\t\t\tpg_free_result(\$rs);"
            . "\n\t\t\treturn false;"
            . "\n\t\t} else {"
            . "\n\t\t\tpg_free_result(\$rs);"
            . "\n\t\t\treturn true;"
            . "\n\t\t}";

        return $code;
   	}

   	public function getDeleteCode($schema, $table, $pk)
    {
        $sql = "DELETE FROM {$schema}.{$table} WHERE {$pk} IN (%s)";
        $code = "\t\tglobal \$conn;"
            . "\n\n\t\tif (!\$conn) {"
            . "\n\t\t\t\$_SESSION['error'] = \"{$this->lang['strerrordbconn']}: \""
            . " . pg_last_error();"
            . "\n\t\t\treturn false;"
            . "\n\t\t}"
            . "\n\t\t\$rs = pg_query(\$conn, sprintf(\"{$sql}\", implode(\",\" , \$ids ) ) );"
            . "\n\n\t\tif (!\$rs){"
            . "\n\t\t\t\$_SESSION['error'] = \"{$this->lang['strrowdeletedbad']}: \""
            . " . pg_last_error(\$conn);"
            . "\n\t\t\treturn false;"
            . "\n\t\t}"
            . "\n\n\t\tpg_free_result(\$rs);";

        return $code;
    }
}
?>