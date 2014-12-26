<?php
/**
 * Class for generating PDO code
 */

class GenPDO extends CodeGen 
{
	function __construct($lang)
    {
    	$this->lang = $lang;
    }

	public function getConnection($user='DB_USER', $password='DB_PASS')
	{
		$code = "\n\t\$conn = new PDO(\"pgsql:dbname=\" . DB_NAME . \";"
            . "host=\" . DB_HOST . \";port=\" . DB_PORT . \";"
            . "user=\" . {$user} . \";password=\" . {$password});";

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
            . "&& isset(\$_POST['crudgen_passwd']) ){"
            . "\n\t\t\t\ttry{"
            . "\n\t\t\t\t\t" . $this->getConnection(
            "\$_POST['crudgen_user']", "\$_POST['crudgen_passwd']")
            . "\n\t\t\t\t\t\$_SESSION['crudgen_user']=\$_POST['crudgen_user'];"
            . "\n\t\t\t\t\t\$_SESSION['crudgen_passwd']=\$_POST['crudgen_passwd'];"
            . "\n\t\t\t\t\treturn true;"
            . "\n\t\t\t\t}catch(PDOException \$e){"
            . "\n\t\t\t\t\t\$_SESSION['error']= \"{$this->lang['strloginerror']}\";"
            . "\n\t\t\t\t\tinclude \"login.inc.php\";"
            . "\n\t\t\t\t}"
            . "\n\t\t\t} else {"
            . "\n\t\t\t\tinclude \"login.inc.php\";"
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
			. "\$query=\"SELECT {$authUser},{$authPassword} "
            . "\n\t\t\t\t\t\tFROM {$schema}.{$table} "
            . "\n\t\t\t\t\t\tWHERE {$authUser}=:crudgen_user AND {$authPassword}=:crudgen_passwd\";"
            . "\n\t\t\t\t\$rs = \$conn->prepare(\$query);"
            . "\n\t\t\t\t\$rs->execute(array(':crudgen_user'=>\$_POST['crudgen_user'],"
            . " ':crudgen_passwd'=>\$_POST['crudgen_passwd']));\n\t\t\t\t"
            . "if(\$rs->rowCount()){\n\t\t\t\t\t"
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
            . "\"{$this->lang['strerrordbconn']}\";"
            . "\n\t\t\texit;"
            . "\n\t\t}"
            . "\n\n\t\ttry {"
            . "\n\t\t\t\$query = \$conn->prepare(sprintf(\"SELECT %s,%s "
            . " FROM %s.%s\", \$pk, \$field, \$schema, \$table));"
            . "\n\t\t\t\$rs = \$query->execute();"
            . "\n\t\t} catch (Exception \$e) {"
            . "\n\t\t\t\$rs = NULL;"
            . "\n\t\t}"
            . "\n\n\t\tif (!\$rs) {"
            . "\n\t\t\t\$_SESSION['error'] = \"{$this->lang['strerrorquery']}\";"
            . "\n\t\t\texit;"
            . "\n\t\t}"
            . "\n\t\twhile (\$row = \$query->fetch()){"
            . "\n\t\t\techo \"<option value=\\\"{\$row[0]}\\\"\";"
            . "\n\t\t\tif(\$row[0] == \$selected_pk) echo \" selected=\\\"selected\\\"\";"
            . "\n\t\t\techo \">{\$row[1]}</option>\";"
            . "\n\t\t};";
        
        return $code;
    }

    public function getLoadRecord($sql)
   	{
   		$code = "\n\t\t\tglobal \$conn;\n"
            . "\n\t\t\tif (!\$conn) {"
            . "\n\t\t\t\t\$_SESSION['error'] = \"{$this->lang['strerrordbconn']}.\";"
            . "\n\t\t\t\texit;"
            . "\n\t\t\t}"
            . "\n\t\t\t\$cant = count(\$_SESSION[\"selected\"]);"
            . "\n\t\t\t\$id = \$cant > 1 ? "
            . "\$_SESSION[\"selected\"][\$index] : \$_SESSION[\"selected\"][0];"
            . "\n\t\t\ttry {"
            . "\n\t\t\t\t\$query = \$conn->prepare(\"{$sql}=:id\");"
            . "\n\t\t\t\t\$query->bindParam(\":id\", \$id);"
            . "\n\t\t\t\t\$rs = \$query->execute();"
            . "\n\t\t\t} catch (Exception \$e) {"
            . "\n\t\t\t\t\$rs = NULL;"
            . "\n\t\t\t}"
            . "\n\t\t\tif (!\$rs){"
            . "\n\t\t\t\t\$_SESSION['error'] = \"{$this->lang['strerrorquery']}\";"
            . "\n\t\t\t\texit;"
            . "\n\t\t\t}\n"
            . "\n\t\t\t\$row = \$query->fetch(PDO::FETCH_ASSOC);\n";

        return $code;
    }

    public function getCreateCode($schema, $table, $columns, $clearVars)
   	{
   		$column_names = array();

   		foreach ($columns as $column)
            if ($column->isOnPage()){
   				$values[] = ":{$column->getName()}";
            	$column_names[] = $column->getName();
            }

        $sql = "\n\t\t\t\"INSERT INTO {$schema}.{$table}"
            . " (" . implode(",", $column_names) . ") "
            . "\n\t\t\t\tVALUES (" . implode(",\n\t\t\t\t", $values) . ")\"";

   		$code = "\t\tglobal \$conn;"
            . "\n\t\tif (!\$conn) {"
            . "\n\t\t\t\$_SESSION['error'] = "
            . "\"{$this->lang['strerrordbconn']}.\";"
            . "\n\t\t\texit;"
            . "\n\t\t}\n"
            . $clearVars
            . "\n\n\t\ttry {"
            . "\n\t\t\t\$query = \$conn->prepare({$sql});\n";

        foreach ($column_names as $column)
            $code .= "\n\t\t\t\$query->bindParam("
                . "':{$column}', \$_POST[\"{$column}\"]);";

        $code .= "\n\t\t\t\$rs = \$query->execute();"
            . "\n\t\t} catch (Exception \$e) {"
            . "\n\t\t\t\$rs = NULL;"
            . "\n\t\t}"
            . "\n\t\tif (!\$rs) {"
            . "\n\t\t\t\$_SESSION['error'] = "
            . "\"{$this->lang['strinsertfail']}.\";"
            . "\n\t\t\treturn false;"
            . "\n\t\t} else {"
            . "\n\t\t\t\$_SESSION['msg'] = \"{$this->lang['strinsertsuccess']}\";";

        foreach ($column_names as $column)
            $code .= "\n\t\t\t\$_POST['{$column}'] = '';";
        
        $code .= "\n\t\t\treturn true;"
            . "\n\t\t}";

        return $code;
    }

    public function getReportCode($selects, $from, $joins)
   	{
   		$sql = "SELECT " . implode(",", $selects) . "\n\t\t\t\tFROM " . $from . implode(" ", $joins);
        $sql_count = "SELECT COUNT(*) FROM " . $from . implode(" ", $joins);

   		$code = "\n\n\t\tglobal \$conn;"
            . "\n\t\t\$extra_sql=\" WHERE 1=1\";"
            . "\n\t\n\t\tif(isset(\$_POST[\"filter-term\"])&& isset(\$_POST['filter-column']))"
            . "\n\t\t\tif(!empty(\$_POST[\"filter-term\"]) && !empty(\$_POST['filter-column'])){"
    		. "\n\t\t\t\t\$extra_sql.= sprintf(\" AND CAST(%s  AS VARCHAR) ILIKE :term\","
            . " \$_POST['filter-column']);"
            . "\n\t\t\t\t\$term = \$_POST[\"filter-term\"] . '%';"
            . "\n\t\t\t} else"
            . "\n\t\t\t\t\$term = NULL;"
            . "\n\t\t\$sql_count = \"{$sql_count}\" . \$extra_sql;"
            . "\n\n\t\tif(isset(\$_POST[\"column_order\"])){"
            . "\n\t\t\t\$extra_sql .= sprintf(\" ORDER BY a.%s \", \$_POST[\"column_order\"]);"
            . "\n\t\t\t\$extra_sql .= \$_POST[\"order\"]==\"ASC\" ? \" ASC\" : \" DESC\";"
            . "\n\t\t}"
            . "\n\n\t\t\$limit = isset(\$_POST[\"filter-limit\"]) ? "
            . "\$_POST[\"filter-limit\"] : RESULTS_LIMIT;"
            . "\n\t\t\$offset = isset(\$_POST[\"offset\"]) ? \$_POST[\"offset\"]"
            . " : RESULTS_START;"
            . "\n\n\t\tif (isset(\$_POST['filter-button']))"
            . "\n\t\t\t\$offset = RESULTS_START;"
            . "\n\n\t\t\$offset = \$limit * (\$offset -1);"
            . "\n\t\t\$paginate_sql = \" LIMIT :limit OFFSET :offset\";\n"
            . "\n\t\tif (!\$conn) {"
            . "\n\t\t\t\$_SESSION['error'] = \"{$this->lang['strerrordbconn']}\";"
            . "\n\t\t\texit;"
            . "\n\t\t}\n"
            . "\n\t\ttry {"
            . "\n\t\t\t\$query = \$conn->prepare(\"{$sql}\" . \$extra_sql . \$paginate_sql);"
            . "\n\t\t\t\$query_count = \$conn->prepare(\$sql_count);"
            . "\n\n\t\t\tif(!empty(\$term)){"
            . "\n\t\t\t\t\$query->bindParam(\":term\",\$term, PDO::PARAM_STR);"
            . "\n\t\t\t\t\$query_count->bindParam(\":term\",\$term, PDO::PARAM_STR);"
            . "\n\t\t\t}"
            . "\n\t\t\t\$query->bindParam(\":limit\", \$limit, PDO::PARAM_INT);"
            . "\n\t\t\t\$query->bindParam(\":offset\", \$offset, PDO::PARAM_INT);"
            . "\n\t\t\t\$query->execute();"
            . "\n\t\t\t\$query_count->execute();"
            . "\n\t\t\t\$rows = \$query_count->fetchColumn();"
            . "\n\t\t} catch(PDOException \$e){"
            . "\n\t\t\t\$_SESSION['error'] = \"{$this->lang['strerrorquery']}\";"
            . "\n\t\t\texit;"
            . "\n\t\t}";
        
        return $code;
    }

    public function getFetchCode()
   	{
   		return '$row = $query->fetch()';
   	}

   	public function getUpdateCode($sql, $columns)
   	{
	  	$code = "\t\tglobal \$conn;\n"
            . "\n\t\t\$columns = array(" . implode(',', $columns) . ");"
            . "\n\t\t\$sql_set = array();"
            . "\n\t\t\$params = array();"
            . "\n\n\t\tforeach(\$columns as \$column){"
            . "\n\t\t\tif(\$_POST[\$column] == \"\")"
            . "\n\t\t\t\t\$sql_set[] = \"{\$column} = NULL\";"
            . "\n\t\t\telse{"
            . "\n\t\t\t\t\$sql_set[] = \"{\$column} = ?\";"
            . "\n\t\t\t\t\$params[] = \$_POST[\$column];"
            . "\n\t\t\t}"
            . "\n\t\t}\n"
            . "\n\t\tif (!\$conn ) {"
            . "\n\t\t\t\$_SESSION['error'] = \"{$this->lang['strerrordbconn']}.\";"
            . "\n\t\t\texit;"
            . "\n\t\t}\n"
            . "\n\t\t\$query = \$conn->prepare({$sql});\n"
            . "\n\n\t\t\$rs = \$query->execute(\$params);"
            . "\n\t\tif (!\$rs)"
            . "\n\t\t\t\$_SESSION['error'] = \"{$this->lang['strupdatefail']}.\";"
            . "\n\n\t\treturn \$rs;";

     	return $code;
     }

    public function getDeleteCode($schema, $table, $pk)
    {
        $sql = "DELETE FROM {$schema}.{$table} WHERE {$pk} IN (%s)";
        $code = "\t\tglobal \$conn;"
        	. "\n\n\t\tif (!\$conn) {"
         	. "\n\t\t\t\$_SESSION['error'] = \"{$this->lang['strerrordbconn']}\";"
            . "\n\t\t\treturn false;"
            . "\n\t\t}"
            . "\n\t\ttry {"
            . "\n\t\t\t\$query= sprintf(\"{$sql}\", implode(\",\" , \$ids ) );"
            . "\n\t\t\t\$count = \$conn->exec(\$query);"
            . "\n\t\t\t\treturn \$count  > 0;"
            . "\n\t\t} catch(PDOException \$e){"
            . "\n\t\t\t\$_SESSION['error'] = \"{$this->lang['strrowdeletedbad']}\";"
            . "\n\t\t\treturn false;"
            . "\n\t\t}";

        return $code;
    }
}
?>