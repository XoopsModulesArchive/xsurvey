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
 * $Id: questiontypes.php,v 1.1 2006/02/22 16:02:19 mikhail Exp $
 *****************************************************************************/

function ListQuestionTypes()
{
    //First: include all Question types, so we can call getQuestionDescription

    $files = [];

    $dir = XOOPS_ROOT_PATH . '/modules/xsurvey/class/question/';

    //TODO: make this more fail-sensitive

    $dirhandle = opendir($dir);

    while (false !== ($file = readdir($dirhandle))) {
        //TODO: Add filename check (Question*.class.php)

        if (true === is_file($dir . $file)) {
            $files[] = $file;
        }
    }

    closedir($dirhandle);

    //Now lets get all question types and their description

    $types = [];

    foreach ($files as $file) {
        require_once $dir . $file;

        $type = mb_substr($file, 8, mb_strlen($file) - 8 - 10);

        $className = 'Question' . $type;

        $tmp = new $className();

        $desc = $tmp->getQuestionDescription();

        $types[$type] = $desc;
    }

    return $types;
}
