<?php
/*********************************************************************
    ticketAPI.php

    New tickets handle.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
$linecid = $_GET['lineid'];
preg_match('/\d+[A-Z][a-z]?/',$linecid,$match);
$siteid = $match[0];
$stuser= $_GET['stuser'];
$contractname= $_GET['contractname'];
$sitecontact = $_GET['sitecontact'];
$sitephone = $_GET['sitephone'];
$linewanip = $_GET['linewanip'];
 if ($browser[0] == "Internet Explorer" ) {

     $stcid =  mb_convert_encoding("$stcid", "UTF-8", "gb2312");
     $stuser= mb_convert_encoding("$stuser", "UTF-8", "gb2312");
     $sitename =  mb_convert_encoding("$sitename", "UTF-8", "gb2312");
     $sitecontact = mb_convert_encoding("$sitecontact", "UTF-8", "gb2312");
        }
$email=$_GET['email'];
$namebuf=explode('@', $email);
$name=$namebuf[0];
$subject="${contractname}-${siteid}-[${stuser}]";

$message="-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-<br />线路ID: $linecid<br />WANIP: $linewanip<br />合同名: $contractname <br />系统中的联络人资料:$sitecontact-$sitephone<br />报障人：ZABBIX  报障来电：-  <br />-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-<br />ZABBIX系统检测到线路断线。";
require('client.inc.php');
define('SOURCE','Web'); //Ticket source.
$ticket = null;
$errors=array();
$vars = array(__CSRFToken__=>'9da2256940db332e20ae301dd03f0ddf4aeaef3d', a=>open, topicId=>10, email=>$email, name=>$name, phone=>'', phone-ext=>'', subject=>$subject, message=>$message, draft_id=>248);
    $ticket=Ticket::create($vars, $errors, SOURCE);
        // Drop session-backed form data
        unset($_SESSION[':form-data']);
echo $ticket->getNumber();
?>
