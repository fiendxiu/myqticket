<?php
require('../client.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');

$num=$_GET['ticketNumber'];
$signal=$_GET['signal'];
$lineid=$_GET['lineid'];
$ticket=Ticket::lookupByNumber($num);
if ($signal==0) {
    $content = "LINE_ID: ".$lineid." --    本次ISO电话未接通.";
    $message = array('title'=>'Hang Up','note'=>$content);
    $ticket->postNote($message,$errors,$poster);
}
else if ($signal==1) {
    $content = "LINE_ID: ".$lineid." --    ISO已接通电话并联系了客户，等待ISO更新故障原因.";
    $message = array('title'=>'Called','note'=>$content);
    $ticket->postNote($message,$errors,$poster);
}
else if ($signal==2) {
    $content = "LINE_ID: ".$lineid." --    ISO已知悉电话，但主动挂机，未拨打客户电话.";
    $message = array('title'=>'Hang Up','note'=>$content);
    $ticket->postNote($message,$errors,$poster);
}
else if ($signal==3) {
    $content = "LINE_ID: ".$lineid." --    ZABBIX系统检测到线路再次断线。";
    $message = array('title'=>'Checked Line-Down Again','note'=>$content);
    $ticket->postNote($message,$errors,$poster);
}
?>
