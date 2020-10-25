<?php
//$id$

//This function is called automatically when a user uninstalls the module
//(see xoops_version.php), after the normal uninstall procedures are finished
//(i.e. deleting tables declared in xoops_version.php, deleting templates,...)
//This function removes all token and survey tables, which are generated by
//the script itself, so Xoops will never know they exist, and cannot drop them
//automatically.
function xoops_module_uninstall_xsurvey(&$module)
{
    //TODO: Always check query return codes. Return false if necessary

    //Get a database connection instance
    global $xoopsDB;

    //Get a list of all tables in the Xoops database
    //TODO: Check if there's a Xoops API call to do this
    $result = mysql_list_tables(XOOPS_DB_NAME, $xoopsDB->conn);

    if (!$result) {
        //Something very bad happened. Return false to tell Xoops we
        //failed. An error message will be presented to the user.
        //We can't use echo to give more details because it would screw
        //up the administration panel layout (echos would be placed above
        //the header)
        return false;
    }

    //Loop through all tables
    while (false !== ($row = $GLOBALS['xoopsDB']->fetchRow($result))) {
        //Let's check if this specific table should be dropped
        //Tablename must be something like
        //'XOOPS_DB_PREFIX'_xsurvey_survey_X where X is numeric
        if (substr($row[0], 0, strlen(XOOPS_DB_PREFIX . '_xsurvey_survey_')) == XOOPS_DB_PREFIX . '_xsurvey_survey_') {
            //Possibly a table we shoud drop. Check if the character after XOOPS_DB_PREFIX_xsurvey_survey_ is an int
            //TODO: Add this check to the first if to speed things up!!
            //(so add '&& is_numeric(...))'
            $x = substr($row[0], strlen(XOOPS_DB_PREFIX . '_xsurvey_survey_'), 1);
            if (is_numeric($x){$sql = 'DROP TABLE `' . $row[0] . '`'{;
        }
            //Use queryF, normal 'query' doesnt allow a DROP TABLE
            //query I think.
            //TODO: Test if I can use query instead of queryF
            $xoopsDB->queryF($sql);
        }
        }

        //check for xsurvey_tokens_X
        //Code is almost exactly the same as the 'survey' one
        //TODO: Add numeric X check like in the _survey_ part
        if (substr($row[0], 0, strlen(XOOPS_DB_PREFIX . '_xsurvey_tokens_')) == XOOPS_DB_PREFIX . '_xsurvey_tokens_') {
            $sql = 'DROP TABLE `' . $row[0] . '`';
            $xoopsDB->queryF($sql);
        }
    }
    //Great, everything worked fine, let's tell Xoops :-)
    return true;
}


