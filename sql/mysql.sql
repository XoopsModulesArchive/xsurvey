# phpMyAdmin SQL Dump
# version 2.5.6
# http://www.phpmyadmin.net
#
# Host: localhost
# Generation Time: Mar 12, 2004 at 10:45 AM
# Server version: 4.0.18
# PHP Version: 4.3.3
# 
# 

# --------------------------------------------------------

#
# Table structure for table `xsurvey_conditions`
#
# Creation: Mar 12, 2004 at 09:21 AM
# Last update: Mar 12, 2004 at 09:30 AM
#

#CREATE TABLE `xsurvey_conditions` (
#  `cid` int(11) NOT NULL auto_increment,
#  `qid` int(11) NOT NULL default '0',
#  `cqid` int(11) NOT NULL default '0',
#  `cfieldname` varchar(50) NOT NULL default '',
#  `method` char(2) NOT NULL default '',
#  `value` varchar(5) NOT NULL default '',
#  PRIMARY KEY  (`cid`)
#);

# --------------------------------------------------------

#
# Table structure for table `xsurvey_questions`
#
# Creation: Mar 12, 2004 at 09:21 AM
# Last update: Mar 12, 2004 at 09:33 AM
#

CREATE TABLE `xsurvey_questions` (
    `qid`      INT(11)     NOT NULL AUTO_INCREMENT,
    `sid`      INT(11)     NOT NULL DEFAULT '0',
    `type`     VARCHAR(20) NOT NULL DEFAULT 'YesNo',
    `title`    VARCHAR(50) NOT NULL DEFAULT '',
    `question` TEXT        NOT NULL,
    `help`     TEXT,
    `other`    BLOB,
    PRIMARY KEY (`qid`)
);

# --------------------------------------------------------

#
# Table structure for table `xoops_xsurvey_surveys`
#
# Creation: Mar 23, 2004 at 10:53 AM
# Last update: Mar 26, 2004 at 09:53 AM
#

CREATE TABLE `xsurvey_surveys` (
    `sid`            TINYINT(4)            NOT NULL AUTO_INCREMENT,
    `title`          VARCHAR(50)           NOT NULL DEFAULT '',
    `description`    TEXT,
    `adminid`        MEDIUMINT(8)          NOT NULL DEFAULT '0',
    `active`         ENUM ('true','false') NOT NULL DEFAULT 'false',
    `welcome`        TEXT,
    `expires`        DATE                           DEFAULT NULL,
    `private`        ENUM ('true','false') NOT NULL DEFAULT 'true',
    `faxto`          VARCHAR(20)                    DEFAULT NULL,
    `format`         ENUM ('OneByOne')     NOT NULL DEFAULT 'OneByOne',
    `url`            VARCHAR(255)                   DEFAULT NULL,
    `urldescription` TEXT,
    PRIMARY KEY (`sid`),
);
