<?php
/****************************************************************************
 *
 * Copyright (C) 2004 Ikke - http://www.eikke.com.  All rights reserved.
 *
 * This file is part of the XSurvey module for Xoops2.
 *
 * This file may be distributed under the terms of the Q Public License
 * as defined by Troll Tech AS of Norway and appearing in the file
 * LICENSE.QPL included in the packaging of this file.
 *
 * See http://www.troll.no/qpl for QPL licensing information.
 *
 * $Id: index.php,v 1.1 2006/02/22 16:02:18 mikhail Exp $
 *****************************************************************************/

require dirname(__DIR__, 3) . '/include/cp_header.php';
//For 'XoopsForm' support
require_once XOOPS_ROOT_PATH . '/class/xoopsformloader.php';
require_once XOOPS_ROOT_PATH . '/modules/xsurvey/include/QuestionTypes.php';

//Include all possible question types
$types = ListQuestionTypes();
foreach ($types as $type => $desc) {
    require_once XOOPS_ROOT_PATH . '/modules/xsurvey/class/question/Question' . $type . '.class.php';
}

//To be uncommented later, when I18N is done
/*if ( file_exists("../language/".$xoopsConfig['language']."/main.php") ) {
    include "../language/".$xoopsConfig['language']."/main.php";
} else {
    include "../language/english/main.php";
}*/

//act can be view, edit, add, activate (just toggles activation state), delete
$action = $_GET['act'] ?? 'show';
//what can be survey (all if no sid is set, only the one with sid=$_GET['sid'], detailed), group, question, users
$what = $_GET['what'] ?? 'survey';
//sid
$sid = (isset($_GET['sid']) && is_numeric($_GET['sid'])) ? $_GET['sid'] : null;
//qid
$qid = (isset($_GET['qid']) && is_numeric($_GET['qid'])) ? $_GET['qid'] : null;

switch ($what) {
    case 'survey':
        switch ($action) {
            case 'add':
                AddSurvey();
                break;
            case 'activate':
                if (null != $sid) {
                    ActivateSurvey($sid);
                } else {
                    ShowSurvey(null);
                }
                break;
            case 'delete':
                if (null != $sid) {
                    DeleteSurvey($sid);
                } else {
                    ShowSurvey(null);
                }
                break;
            case 'show':
            default:
                ShowSurvey($sid);
                break;
        }
        break;
    case 'question':
        switch ($action) {
            case 'add':
                AddQuestion($sid);
                break;
            case 'delete':
                if (null != $qid) {
                    DeleteQuestion($qid);
                } else {
                    ShowQuestion($qid);
                }
                break;
            case 'show':
            default:
                //TODO: write ShowQuestion function
                ShowQuestion($qid);
                break;
        }
        break;
    default:
        ShowSurvey(null);
        break;
}

exit();
//Nothing should be done/displayed/... after this line

function DeleteQuestion($qid)
{
    global $xoopsDB, $_POST;

    if (null === $qid) {
        return false;
    }

    if (isset($_POST['ok']) && 1 == $_POST['ok']) {
        $qid = $_POST['qid'];

        //Get sid of parent survey, to return the user

        $sql = 'SELECT `sid` FROM `' . $xoopsDB->prefix('xsurvey_questions') . "` WHERE `qid`='" . (int)$qid . "'";

        $res = $xoopsDB->query($sql);

        $row = $xoopsDB->fetchRow($res);

        $sid = $row[0];

        $sql = 'DELETE FROM `' . $xoopsDB->prefix('xsurvey_questions') . "` WHERE `qid`='" . (int)$qid . "'";

        $res = $xoopsDB->query($sql);

        //TODO: check return status, tell the user if the unsuccessfull

        redirect_header('index.php?act=show&what=survey&sid=' . $sid, 2, 'Question ' . $sid . ' has been deleted successfully.');

        xoops_cp_footer();

        exit();
    }  

    xoops_cp_header();

    xoops_confirm(['qid' => $qid, 'ok' => 1], 'index.php?act=delete&what=question&qid=' . $qid, 'Are you sure you want to delete this question?');

    xoops_cp_footer();

    exit();
}

//sid is sid of parent survey
function AddQuestion($sid)
{
    if (!is_numeric($sid) || null === $sid) {
        return false;
    }

    global $xoopsDB;

    //Look of parent survey exist

    $sql = 'SELECT COUNT(*) FROM `' . $xoopsDB->prefix('xsurvey_surveys') . "` WHERE `sid`='" . (int)$sid . "'";

    //TODO: Check if res is valid

    $res = $xoopsDB->query($sql);

    $row = $xoopsDB->fetchRow($res);

    $cnt = $row[0];

    if (1 != $cnt) {
        return false;
    }

    //OK, safe to create the question :-)

    global $_POST;

    //First stage...

    if (!isset($_POST['action'])) {
        $question_form = new XoopsThemeForm('Create a new question', 'addquestion_form', 'index.php?act=add&what=question&sid=' . $sid);

        $question_type = new XoopsFormSelect('Question type', 'type');

        $question_type->addOptionArray(ListQuestionTypes());

        $question_form->addElement($question_type);

        $question_title = new XoopsFormText('Question title', 'title', 25, 50);

        $question_form->addElement($question_title);

        $question_question = new XoopsFormTextarea('Question', 'question');

        $question_form->addElement($question_question);

        $question_help = new XoopsFormTextarea('Help', 'help');

        $question_form->addElement($question_help);

        $question_post = new XoopsFormHidden('action', 'opts');

        $question_form->addElement($question_post);

        $question_sid = new XoopsFormHidden('sid', $sid);

        $question_form->addElement($question_sid);

        $question_submit = new XoopsFormButton('', 'question_submit', _SUBMIT, 'submit');

        $question_form->addElement($question_submit);

        xoops_cp_header();

        $question_form->display();

        xoops_cp_footer();
    } else {
        if ('opts' == $_POST['action']) {
            //Display question options form

            $QType = 'Question' . $_POST['type'];

            echo $QType;

            $tmp = new $QType();

            $questionopts_form = new XoopsThemeForm('Question options', 'questionopts_form', 'index.php?act=add&what=question&sid=' . $sid);

            $elements = $tmp->getOptionsForm();

            //Add all these elements to our form

            foreach ($elements as $element) {
                $questionopts_form->addElement($element);
            }

            $questionopts_act = new XoopsFormHidden('action', 'add');

            $questionopts_form->addElement($questionopts_act);

            $questionopts_firstdata = new XoopsFormHidden('firstdata', base64_encode(serialize($_POST)));

            $questionopts_form->addElement($questionopts_firstdata);

            $questionopts_submit = new XoopsFormButton('', 'questionopts_submit', _SUBMIT, 'submit');

            $questionopts_form->addElement($questionopts_submit);

            xoops_cp_header();

            $questionopts_form->display();

            xoops_cp_footer();
        }

        if ('add' == $_POST['action']) {
            //We got all data, lets add it to the DB

            $error = false;

            $firstdata = unserialize(base64_decode($_POST['firstdata'], true));

            //Let's fetch all data from POST, checking values etc. Set error=true when an error occurs.

            $ts = MyTextSanitizer::getInstance();

            $data = [];

            if (isset($firstdata['type']) && null != $firstdata['type']) {
                $data['type'] = $firstdata['type'];
            } else {
                $error = true;
            }

            if (isset($firstdata['title']) && null != $firstdata['title']) {
                $data['title'] = $ts->addSlashes($firstdata['title']);
            } else {
                $error = true;
            }

            if (isset($firstdata['question']) && null != $firstdata['question']) {
                $data['question'] = $ts->addSlashes($firstdata['question']);
            } else {
                $error = true;
            }

            if (isset($firstdata['sid']) && null != $firstdata['sid']) {
                $data['sid'] = $ts->addSlashes($firstdata['sid']);
            } else {
                $error = true;
            }

            if (true === $error) {
                //Some data have not been set correctly, or another error occured. Redirecting...

                xoops_cp_header();

                echo '<div><b>Error</b><br><br>';

                echo '<b>ERROR:</b> Invalid data entered <br><br><br>';

                echo "[ <a href='javascript:history.go(-1)'>Go Back</a> ]</div>";

                xoops_cp_footer();

                exit();
            }

            if (isset($firstdata['help']) && null != $firstdata['help']) {
                $data['help'] = $ts->addSlashes($firstdata['help']);
            } else {
                $data['help'] = null;
            }

            $QType = 'Question' . $firstdata['type'];

            $tmp = new $QType();

            $optdata = $tmp->processOptionsForm($_POST);

            $sql = 'INSERT INTO `'
                   . $xoopsDB->prefix('xsurvey_questions')
                   . "` (`sid` , `type` , `title` , `question` , `help`, `other`) VALUES ('"
                   . $data['sid']
                   . "', '"
                   . $data['type']
                   . "', '"
                   . $data['title']
                   . "', '"
                   . $data['question']
                   . "', '"
                   . $data['help']
                   . "', '"
                   . serialize($optdata)
                   . "')";

            //Next execute the query, and return to index.php

            $ret = $xoopsDB->query($sql);

            //$ret should be 1, else something went wrong

            if (1 == $ret) {
                redirect_header('index.php?act=view&what=survey&sid=' . $sid, 3, 'The new question was successfully added to the database');

                exit();
            }  

            xoops_cp_header();

            echo '<div><b>Error</b><br><br>';

            echo '<b>ERROR:</b> INSERT query could not be executed <br><br><br>';

            echo '<b>MySQL Error:</b> ' . $xoopsDB->error() . '<br><br><br>';

            echo '<b>Query:</b> ' . $sql . '<br><br><br>';

            echo "[ <a href='javascript:history.go(-1)'>Go Back</a> ]</div>";

            xoops_cp_footer();

            exit();
        }
    }
}

function DeleteSurvey($sid)
{
    if (null === $sid) {
        return false;
    }

    if (isset($_POST['ok']) && 1 == $_POST['ok']) {
        //Deleted surveys should be desactivated, otherwise they cannot be deleted

        global $xoopsDB;

        $sid = $_POST['sid'];

        $sql = 'SELECT `active` FROM `' . $xoopsDB->prefix('xsurvey_surveys') . "` WHERE `sid`='" . (int)$sid . "'";

        $res = $xoopsDB->query($sql);

        //TODO: add check if line row is returned correctly

        $row = $xoopsDB->fetchRow($res);

        $status = $row[0];

        if ('true' == $status) { //active
            redirect_header('index.php', 2, 'A survey has to be desactivated before it can be deleted.');

            exit();
        }

        //Desactivated if we reach this point :-D

        //TODO: Delete all other things related to this survey: questions, answers, tokens table,...

        $sql = 'DELETE FROM `' . $xoopsDB->prefix('xsurvey_surveys') . "` WHERE `sid`='" . (int)$sid . "'";

        $res = $xoopsDB->query($sql);

        //TODO: check return status, tell the user if the unsuccessfull

        redirect_header('index.php', 2, 'Survey ' . $sid . ' has been deleted successfully.');

        exit();
    }  

    xoops_cp_header();

    xoops_confirm(['sid' => $sid, 'ok' => 1], 'index.php?act=delete&what=survey&sid=' . $sid, 'Are you sure you want to delete this survey?');

    xoops_cp_footer();

    exit();
}

function ActivateSurvey($sid)
{
    if (null === $sid) {
        return false;
    }

    if (isset($_POST['ok']) && 1 == $_POST['ok']) {
        global $xoopsDB;

        $sid = $_POST['sid'];

        //Lets get the current activation state of the survey

        $curstate = false;

        $sql = 'SELECT `active` FROM `' . $xoopsDB->prefix('xsurvey_surveys') . "` WHERE `sid`='" . (int)$sid . "' LIMIT 1";

        $res = $xoopsDB->query($sql);

        if (null === $res) {
            //Something went wrong

            xoops_cp_header();

            echo '<div><b>Error</b><br><br>';

            echo '<b>ERROR:</b> SELECT query could not be executed<br><br><br>';

            echo '<b>MySQL Error:</b> ' . $xoopsDB->error() . '<br><br><br>';

            echo '<b>Query:</b> ' . $sql . '<br><br><br>';

            echo "[ <a href='javascript:history.go(-1)'>Go Back</a> ]</div>";

            xoops_cp_footer();

            exit();
        }  

        $row = $xoopsDB->fetchRow($res);

        $curstate = $row[0];

        $state = ('true' == $curstate ? 'false' : 'true');

        $sql = 'UPDATE `' . $xoopsDB->prefix('xsurvey_surveys') . "` SET `active`='" . $state . "' WHERE `sid`='" . (int)$sid . "'";

        $ret = $xoopsDB->query($sql);

        if (1 == $ret) {
            //Query was successfull

            $done = ('false' == $state ? 'desactivated' : 'activated');

            redirect_header('index.php', 2, 'Survey ' . (int)$sid . ' has been ' . $done . '.');
        } else {
            xoops_cp_header();

            echo '<div><b>Error</b><br><br>';

            echo '<b>ERROR:</b> UPDATE query could not be executed <br><br><br>';

            echo '<b>MySQL Error:</b> ' . $xoopsDB->error() . '<br><br><br>';

            echo '<b>Query:</b> ' . $sql . '<br><br><br>';

            echo "[ <a href='javascript:history.go(-1)'>Go Back</a> ]</div>";

            xoops_cp_footer();

            exit();
        }
    } else {
        global $xoopsDB;

        $sql = 'SELECT `active` FROM `' . $xoopsDB->prefix('xsurvey_surveys') . "` WHERE `sid`='" . (int)$sid . "' LIMIT 1";

        $res = $xoopsDB->query($sql);

        if (null === $res) {
            //Something went wrong

            xoops_cp_header();

            echo '<div><b>Error</b><br><br>';

            echo '<b>ERROR:</b> SELECT query could not be executed<br><br><br>';

            echo '<b>MySQL Error:</b> ' . $xoopsDB->error() . '<br><br><br>';

            echo '<b>Query:</b> ' . $sql . '<br><br><br>';

            echo "[ <a href='javascript:history.go(-1)'>Go Back</a> ]</div>";

            xoops_cp_footer();

            exit();
        }  

        $row = $xoopsDB->fetchRow($res);

        $curstate = $row[0];

        $state = ('true' == $curstate ? 'false' : 'true');

        $done = ('false' == $state ? 'desactivate' : 'activate');

        xoops_cp_header();

        xoops_confirm(['sid' => $sid, 'ok' => 1], 'index.php?act=activate&what=survey&sid=' . $sid, 'Are you sure you want to ' . $done . ' this survey?');

        xoops_cp_footer();

        exit();
    }
}

function AddSurvey()
{
    global $_POST, $xoopsDB;

    if (!isset($_POST['action']) || 'add' != $_POST['action']) {
        //Display "add survey" form

        $survey_form = new XoopsThemeForm('Create a new Survey', 'addsurvey_form', 'index.php?act=add&what=survey');

        $survey_title = new XoopsFormText('Survey title', 'title', 50, 50);

        $survey_form->addElement($survey_title);

        $survey_desc = new XoopsFormTextarea('Survey description', 'description');

        $survey_form->addElement($survey_desc);

        $survey_welcome = new XoopsFormTextarea('Welcome text', 'welcome');

        $survey_form->addElement($survey_welcome);

        $survey_expires = new XoopsFormDateTime('Expires', 'expires');

        $survey_form->addElement($survey_expires);

        $survey_fax = new XoopsFormText('Faxnumber', 'faxto', 20, 20);

        $survey_form->addElement($survey_fax);

        $survey_post = new XoopsFormHidden('action', 'add');

        $survey_form->addElement($survey_post);

        $survey_url = new XoopsFormText('End URL', 'url', 50, 255);

        $survey_form->addElement($survey_url);

        $survey_url_desc = new XoopsFormTextarea('End URL description', 'urldescription');

        $survey_form->addElement($survey_url_desc);

        $survey_anon = new XoopsFormSelect('Allow anonymous responses', 'private');

        $survey_anon->addOption('false', 'Yes');

        $survey_anon->addOption('true', 'No');

        $survey_form->addElement($survey_anon);

        //TODO: Add new 'forms' when necessary

        $survey_format = new XoopsFormSelect('Presentation form', 'format');

        $survey_format->addOption('OneByOne', 'One by one');

        $survey_form->addElement($survey_format);

        $survey_submit = new XoopsFormButton('', 'survey_submit', _SUBMIT, 'submit');

        $survey_form->addElement($survey_submit);

        xoops_cp_header();

        $survey_form->display();

        xoops_cp_footer();
    } else {
        $error = false;

        //Let's fetch all data from POST, checking values etc. Set error=true when an error occurs.

        $ts = MyTextSanitizer::getInstance();

        $data = [];

        if (isset($_POST['title']) && null != $_POST['title']) {
            $data['title'] = $ts->addSlashes($_POST['title']);
        } else {
            $error = true;
        }

        if (isset($_POST['private']) && null != $_POST['private']) {
            $data['private'] = $_POST['private']; //Doesnt need addSlashes (Dropdown data!)
        } else {
            $error = true;
        }

        if (isset($_POST['format']) && null != $_POST['format']) {
            $data['format'] = $_POST['format'];   //Doesnt need addSlashes (Dropdown data!)
        } else {
            $error = true;
        }

        if (true === $error) {
            //Some data have not been set correctly, or another error occured. Redirecting...

            xoops_cp_header();

            echo '<div><b>Error</b><br><br>';

            echo '<b>ERROR:</b> Invalid data entered <br><br><br>';

            echo "[ <a href='javascript:history.go(-1)'>Go Back</a> ]</div>";

            xoops_cp_footer();

            exit();
        }

        //Now get the other data, make it null if unset

        if (isset($_POST['welcome']) && null != $_POST['welcome']) {
            $data['welcome'] = $ts->addSlashes($_POST['welcome']);
        } else {
            $data['welcome'] = null;
        }

        if (isset($_POST['description']) && null != $_POST['description']) {
            $data['description'] = $ts->addSlashes($_POST['description']);
        } else {
            $data['description'] = null;
        }

        if (isset($_POST['faxto']) && null != $_POST['faxto']) {
            $data['faxto'] = $ts->addSlashes($_POST['faxto']);
        } else {
            $data['faxto'] = null;
        }

        if (isset($_POST['url']) && null != $_POST['url']) {
            $data['url'] = $ts->addSlashes($_POST['url']);
        } else {
            $data['url'] = null;
        }

        if (isset($_POST['urldescription']) && null != $_POST['urldescription']) {
            $data['urldescription'] = $ts->addSlashes($_POST['urldescription']);
        } else {
            $data['urldescription'] = null;
        }

        if (isset($_POST['expires']) && null != $_POST['expires']) {
            $data['expires'] = $_POST['expires']['date'];
        } else {
            $data['expires'] = null;
        }

        global $xoopsUser;

        $data['adminid'] = $xoopsUser->uid();

        //Add data

        $sql = 'INSERT INTO `' . $xoopsDB->prefix('xsurvey_surveys') . '` (`title` , `description` , `adminid` , `welcome` , `expires` , `private` , `faxto` , `format` , `url` , `urldescription`) VALUES (';

        $sql .= "'" . $data['title'] . "', '" . $data['description'] . "', '" . $data['adminid'] . "', '" . $data['welcome'] . "', '" . $data['expires'] . "', '" . $data['private'] . "', '" . $data['faxto'] . "', '" . $data['format'] . "', '" . $data['url'] . "', '" . $data['urldescription'] . "')";

        //Then execute the query, and return to index.php

        $ret = $xoopsDB->query($sql);

        //$ret should be 1, else something went wrong

        if (1 == $ret) {
            redirect_header('index.php', 3, 'The new survey was successfully added to the database');

            exit();
        }  

        xoops_cp_header();

        echo '<div><b>Error</b><br><br>';

        echo '<b>ERROR:</b> INSERT query could not be executed <br><br><br>';

        echo '<b>MySQL Error:</b> ' . $xoopsDB->error() . '<br><br><br>';

        echo '<b>Query:</b> ' . $sql . '<br><br><br>';

        echo "[ <a href='javascript:history.go(-1)'>Go Back</a> ]</div>";

        xoops_cp_footer();

        exit();
    }
}

function ShowSurvey($sid = null)
{
    //TODO: Use Xoops Kernel TextSanatizer checks

    global $xoopsDB;

    if (null === $sid) {
        //show all surveys (maybe in pages?)

        xoops_cp_header(); ?>
        <table style="width: 100%;">
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Admin</th>
                <th>Active</th>
                <th>Expires</th>
                <th>Delete</th>
            </tr>
            <?php
            $sql = 'SELECT `sid`,`title`,`adminid`,`active`,`expires` FROM `' . $xoopsDB->prefix('xsurvey_surveys') . '` ORDER BY `sid`';

        $res = $xoopsDB->query($sql);

        //TODO: test if result is ok, no error

        while (false !== ($row = $xoopsDB->fetchArray($res))) {
            echo '<tr>';

            echo '<td>' . $row['sid'] . '</td>';

            echo '<td><a href="index.php?act=view&what=survey&sid=' . $row['sid'] . '">' . $row['title'] . '</a></td>';

            echo '<td><a href="' . XOOPS_URL . '/userinfo.php?uid=' . $row['adminid'] . '" target="_blank">' . XoopsUser::getUnameFromId($row['adminid']) . '</a></td>';

            echo '<td><a href="index.php?act=activate&what=survey&sid=' . $row['sid'] . '">' . ('true' == $row['active'] ? 'Yes' : 'No') . '</a></td>';

            echo '<td>' . $row['expires'] . '</td>';

            echo '<td><a href="index.php?act=delete&what=survey&sid=' . $row['sid'] . '">Delete</a></td>';

            echo '</tr>';
        } ?>
        </table>
        <p><a href="<?php echo XOOPS_URL; ?>/modules/xsurvey/admin/index.php?act=add&what=survey">Add survey</a></p>
        <?php
        xoops_cp_footer();
    } else {
        //show one specific survey (detailed)

        xoops_cp_header();

        $sql = 'SELECT * FROM `' . $xoopsDB->prefix('xsurvey_surveys') . "` WHERE `sid`='" . (int)$sid . "' LIMIT 1";

        $res = $xoopsDB->query($sql);

        //TODO: Check wether $res is a valid result
        $data = $xoopsDB->fetchArray($res); ?>
        <table width='100%' class='outer' cellspacing='1'>
            <tr>
                <th colspan='2'>Survey details</th>
            </tr>
            <tr valign='top' align='left'>
                <td class='head'>Title:</td>
                <td class='even'><a href="<?php echo XOOPS_URL . '/modules/xsurvey/index.php?sid=' . $data['sid']; ?>" target="_blank"><?php echo $data['title']; ?></a></td>
            </tr>
            <tr valign='top' align='left'>
                <td class='head'>Description:</td>
                <td class='even'><?php echo $data['description']; ?></td>
            </tr>
            <tr valign='top' align='left'>
                <td class='head'>Admin:</td>
                <td class='even'><a href="<?php echo XOOPS_URL . '/userinfo.php?uid=' . $data['adminid']; ?>" target="_blank"><?php echo XoopsUser::getUnameFromId($data['adminid']); ?></a></td>
            </tr>
            <tr valign='top' align='left'>
                <td class='head'>Welcome text:</td>
                <td class='even'><?php echo $data['welcome']; ?></td>
            </tr>
            <tr valign='top' align='left'>
                <td class='head'>Expires:</td>
                <td class='even'><?php echo $data['expires']; ?></td>
            </tr>
            <tr valign='top' align='left'>
                <td class='head'>Private:</td>
                <td class='even'><?php echo('false' == $data['private'] ? 'Yes' : 'No'); ?></td>
            </tr>
            <tr valign='top' align='left'>
                <td class='head'>Fax:</td>
                <td class='even'><?php echo $data['faxto']; ?></td>
            </tr>
            <tr valign='top' align='left'>
                <td class='head'>Format:</td>
                <td class='even'><?php echo $data['format']; ?></td>
            </tr>
            <tr valign='top' align='left'>
                <td class='head'>End URL:</td>
                <td class='even'><a href="<?php echo $data['url']; ?>" target="_blank"><?php echo $data['url']; ?></a></td>
            </tr>
            <tr valign='top' align='left'>
                <td class='head'>End URL description:</td>
                <td class='even'><?php echo $data['urldescription']; ?></td>
            </tr>
        </table>
        <?php
        //Get all question types
        $types = ListQuestionTypes();

        //OK, get the questions

        $sql = 'SELECT `qid`, `type`, `title` FROM `' . $xoopsDB->prefix('xsurvey_questions') . "` WHERE `sid`='" . (int)$sid . "' ORDER BY `qid`";

        //TODO: check if res is a valid resultset
        $res = $xoopsDB->query($sql); ?>
        <p>&nbsp;</p>
        <table width='100%' class='outer' cellspacing='1'>
            <tr>
                <th colspan='4'>Questions</th>
            </tr>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Type</th>
                <th>Delete</th>
            </tr>
            <?php
            //Now we can loop through all the questions related to this survey
            while (false !== ($row = $xoopsDB->fetchArray($res))) {
                ?>
                <tr>
                    <td><?php echo $row['qid']; ?></td>
                    <td><?php echo $row['title']; ?></td>
                    <td><?php echo $types[$row['type']]; ?></td>
                    <td><a href="index.php?act=delete&what=question&qid=<?php echo $row['qid']; ?>">Delete</a></td>
                </tr>
                <?php
            } ?>
        </table>
        <p><a href="<?php echo XOOPS_URL; ?>/modules/xsurvey/admin/index.php?act=add&what=question&sid=<?php echo $sid; ?>">Add question</a></p>
        <?php
        xoops_cp_footer();
    }
}

?>
