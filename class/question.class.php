<?php
//Basic Question, class should be implemented by all Question types

class Question
{
    //Local XoopsDb handle, given by construction

    //var $xoopsDB=null;

    //Template to be included for this question

    //var $template="BaseQuestion";

    public function __construct($xoopsDB)
    {
        //if($xoopsDB!=null)
        //{
        //$this->xoopsDB=$xoopsDB;
        //}
    }

    public function GetInsertQuery($values)
    {
        //Check if the given values are 'good', if not, return false
        //if(!isset($values['myfield']) || is_numeric($values['myfield']))
        //{
        //My value is not set, or it is numeric -> no good
        //(this is just an example off course :-))
        //return false;
        //}

        //Create an associative array containig all data to be inserted
        //(row => value) using values $values
        //$ret=array();
        //$ret['test']=$values['myfield'];
        //return $ret;
    }

    public function GetTemplateName()
    {
        //Template will be: xsurvey_question_(this) and xsurvey_printquestion_(this)
        //ALWAYS ADD THIS TO xoops_version!!!
        //return $template;
    }
}
