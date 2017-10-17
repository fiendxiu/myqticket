<?php
require('../client.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');

$num=$_GET['ticketNumber'];
$filename=$_GET['filename'];
$poster=$_GET['poster'];
$ticket=Ticket::lookupByNumber($num);
    $content = "http://192.168.38.254/ticketcdr/index.php?filename=".$filename;
    $content = "<a href=".$content."><img src='./images/record.png' alt='录音链接'></a>";
    $message = array('title'=>'录音','note'=>$content);
    $ticket->postNote($message,$errors,$poster);

?>

