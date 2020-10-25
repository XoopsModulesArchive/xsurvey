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
 * $Id: questionyesno.class.php,v 1.1 2006/02/22 16:02:19 mikhail Exp $
 *****************************************************************************/

class QuestionYesNo     //Template for this one will be "xsurvey_question_yesno.html" en "xsurvey_question_yesno_print.html"
{
    //Should return question type, ie Question(Type).class.php (Type part).

    //Value used to store the question type in the DB

    public function getQuestionType()
    {
        return 'YesNo';
    }

    //Should return a small description of this question type, used in the dropdown box of the "Add question" page

    public function getQuestionDescription()
    {
        return 'Yes/no question';
    }

    //Should return an array containing XoopsForms items, used on the wizard page where the user can tweak some settings of this question type

    //processOptionsForm will get the $_POST of this

    //return null if no additional options are necessary

    public function &getOptionsForm()
    {
        $ret = [];

        $ret[] = new XoopsFormText('Test', 'testtext', 50, 50);

        return $ret;
    }

    //Process the results of the form given by getOptionsForm. Returned data will be serialized and stored in database, given to getTemplateData and getCreateFields

    public function &processOptionsForm($data)
    {
        $ret = [];

        $ret['testing'] = $data['testtext'];

        return $ret;
    }

    //Return an array containing data which describes what fields this question needs in the survey answers table

    public function &getCreateFields($data)
    {
        $ret = [];

        //We only need one row

        $ret[] = [];

        $ret[0]['name'] = 'Answer';

        $ret[0]['type'] = 'enum(\'yes\',\'no\')';

        $ret[0]['default'] = 'no';

        //add more fields if necessary

        return $ret;
    }

    //Return an an array containing data used by the Smarty template, in the variable "question".

    //$data is the array returned by processOptionsForm

    public function &getTemplateData($data)
    {
        $ret = [];

        $ret['extrafield'] = $data['testing'];    //Will be accessible in Smarty like "question.extrafield"

        return $ret;
    }

    //Should return an array describing data to be stored in the DB. $data is the result of the POST call of the template

    public function &getStoreData($data)
    {
        $val = $data['answer'];   //where 'answer' is the name of two radio buttons in the template, one with value "yes", other "no"

        $ret = [];

        $ret['Answer'] = $val;    //Answer is column name, see getCreateFields

        return $ret;
    }

    //Function returns description off all fields, used as header in reports etc

    public function &getFieldDescriptions()
    {
        $ret = [];

        $ret['Answer'] = 'Answer';

        //Can contain more fields if there are more rows

        return $ret;
    }
}
