<?php
/*********************************************************************
    MysqlConn.php
    Dixon
    2017-9-4
    
    数据库连接
    
**********************************************************************/
$host="localhost";
$db_user="root";
$db_pass="both-win";
$db_name="ticket";
$timezone="Asia/Shanghai";

$conn=mysql_connect($host,$db_user,$db_pass) or die("数据库链接错误".mysql_error());
mysql_select_db($db_name,$conn) or die("数据库访问错误".mysql_error());
mysql_query("SET names UTF8");

header("Content-Type: text/html; charset=utf-8");
date_default_timezone_set($timezone); //上海时间

?>
