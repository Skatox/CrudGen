<?php 

/**
 * Class for managing and creating security code for the pages
 */
class Security {

/**
 * This function generates code for no security
 * @param Application $app application where to insert the security code
 * @return string with php code for no security
 */
    private function getNoneSecurity(Application $app) {
        return "\$GLOBALS['conn']= pg_connect(\"host='{$app->getDBHost()}' port='{$app->getDBPort()}' password='"
            ."{$app->getDBPass()}' user='{$app->getDBUser()}' dbname='{$app->getDBName()}'\");\n\treturn true;";
    }
    /**
     * This function generates code for security trought database users
     * @param Application $app application where to insert the security code
     * @return string with php code for no security
     */
    private function getDbUserSecurity(Application $app) {
        global $lang;
        $code="//Closes session
        if(isset(\$_POST['login_close'])){
            unset(\$_SESSION['appgen_user']);
            unset(\$_SESSION['appgen_passwd']);
            session_destroy();
        }
        //Authorizes user
        if(isset(\$_SESSION['appgen_user']) && isset(\$_SESSION['appgen_passwd'])){
            \$GLOBALS['conn'] = pg_connect(\"host='{$app->getDBHost()}' port='{$app->getDBPort()}' password='{\$_SESSION['appgen_passwd']}' user='{\$_SESSION['appgen_user']}' dbname='{$app->getDBName()}'\");
            return true;
        }
        else {
            if(isset(\$_POST['login_start'])){
                \$GLOBALS['conn'] = pg_connect(\"host='{$app->getDBHost()}' port='{$app->getDBPort()}' password='{\$_POST['appgen_passwd']}' user='{\$_POST['appgen_user']}' dbname='{$app->getDBName()}'\");
            if(\$GLOBALS['conn']){
                \$_SESSION['appgen_user']=\$_POST['appgen_user'];\$_SESSION['appgen_passwd']=\$_POST['appgen_passwd'];
                return true;
		}
		else{
		    echo \"<div class=\\\"login main\\\">\";
            echo \"<div class=\\\"warnmsg\\\"><strong>{$lang['strloginfailed']}</strong></div>\";
            echo \"</div>\";
        }
        }
        \$curfile = Explode('/', \$_SERVER[\"SCRIPT_NAME\"]);
        echo \"<div class=\\\"login main\\\">\";
        echo \"<form action=\\\"{\$curfile[count(\$curfile)-1]}\\\" method=\\\"post\\\">\";
        echo \"<div class=\\\"login_label\\\">{$lang['strusername']}: </div><div><input type=\\\"text\\\" name=\\\"appgen_user\\\" /></div>\";
        echo \"<div class=\\\"login_label\\\">{$lang['strpassword']}: </div><div><input type=\\\"password\\\" name=\\\"appgen_passwd\\\" /></div>\";
        echo \"<p><input type=\\\"submit\\\" name=\\\"login_start\\\" value=\\\"{$lang['strlogin']}\\\"/></p>\";
        echo \"</form>\";
        echo \"</div>\";
        return false;
        }";

        return $code;
    }
    /**
     * This function generates code for security trought username and password
     * stored in the database
     * @param Application $app application where to insert the security code
     * @return string with php code for no security
     */
    private function getDbTableSecurity(Application $app) {
        global $lang;
        $code="//Closes session
        if(isset(\$_POST['login_close'])){
            unset(\$_SESSION['appgen_user']);
            unset(\$_SESSION['appgen_passwd']);
            session_destroy();
        }
        //Authorizes user
        if(isset(\$_SESSION['appgen_user']) && isset(\$_SESSION['appgen_passwd'])){
            \$GLOBALS['conn'] = pg_connect(\"host='{$app->getDBHost()}' port='{$app->getDBPort()}' password='{$app->getDBPass()}' user='{$app->getDBUser()}' dbname='{$app->getDBName()}'\");
            return true;
        }
        else {
            if(isset(\$_POST['login_start'])){
                \$GLOBALS['conn'] = pg_connect(\"host='{$app->getDBHost()}' port='{$app->getDBPort()}' password='{$app->getDBPass()}' user='{$app->getDBUser()}' dbname='{$app->getDBName()}'\");
                \$query=\"SELECT {$app->getAuthUser()},{$app->getAuthPassword()} FROM {$app->getSchema()}.{$app->getAuthTable()} WHERE {$app->getAuthUser()}='{\$_POST['appgen_user']}' AND {$app->getAuthPassword()}='{\$_POST['appgen_passwd']}'\";
                \$rs=pg_query(\$GLOBALS['conn'],\$query);
            if(pg_num_rows(\$rs)){
                \$_SESSION['appgen_user']=\$_POST['appgen_user'];\$_SESSION['appgen_passwd']=\$_POST['appgen_passwd'];
                return true;
		}
		else{
		    echo \"<div class=\\\"login main\\\">\";
            echo \"<div class=\\\"warnmsg\\\"><strong>{$lang['strloginfailed']}</strong></div>\";
            echo \"</div>\";
        }
        }
        \$curfile = Explode('/', \$_SERVER[\"SCRIPT_NAME\"]);
        echo \"<div class=\\\"login main\\\">\";
        echo \"<form action=\\\"{\$curfile[count(\$curfile)-1]}\\\" method=\\\"post\\\">\";
        echo \"<div class=\\\"login_label\\\">{$lang['strusername']}: </div><div><input type=\\\"text\\\" name=\\\"appgen_user\\\" /></div>\";
        echo \"<div class=\\\"login_label\\\">{$lang['strpassword']}: </div><div><input type=\\\"password\\\" name=\\\"appgen_passwd\\\" /></div>\";
        echo \"<p><input type=\\\"submit\\\" name=\\\"login_start\\\" value=\\\"{$lang['strlogin']}\\\"/></p>\";
        echo \"</form>\";
        echo \"</div>\";
        return false;
        }";

        return $code;
    }
    /**
     * This is the main function for generated the security code
     * @param Application $app application where to insert the security code
     * @return string with php code for no security
     */
    public function getSecurityCode(Application $app) {
        switch($app->getAuthMethod()) {
            case "none": return $this->getNoneSecurity($app);
            case "dbuser": return $this->getDbUserSecurity($app);
            case "dbtable": return $this->getDbTableSecurity($app);
            default: return null;
        }
    }
} 
?>
