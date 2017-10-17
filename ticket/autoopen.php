<?php
/*********************************************************************
    open.php

    New tickets handle.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
$browser = getBrowser();
function getBrowser(){
 $sys = $_SERVER['HTTP_USER_AGENT'];
 if(stripos($sys, "NetCaptor") > 0){
  $exp[0] = "NetCaptor";
  $exp[1] = "";
 }elseif(stripos($sys, "Firefox/") > 0){
  preg_match("/Firefox\/([^;)]+)+/i", $sys, $b);
  $exp[0] = "Mozilla Firefox";
  $exp[1] = $b[1];
 }elseif(stripos($sys, "MAXTHON") > 0){
  preg_match("/MAXTHON\s+([^;)]+)+/i", $sys, $b);
  preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
 // $exp = $b[0]." (IE".$ie[1].")";
  $exp[0] = $b[0]." (IE".$ie[1].")";
  $exp[1] = $ie[1];
 }elseif(stripos($sys, "MSIE") > 0){
  preg_match("/MSIE\s+([^;)]+)+/i", $sys, $ie);
  //$exp = "Internet Explorer ".$ie[1];
  $exp[0] = "Internet Explorer";
  $exp[1] = $ie[1];
 }elseif(stripos($sys, "Netscape") > 0){
  $exp[0] = "Netscape";
  $exp[1] = "";
 }elseif(stripos($sys, "Opera") > 0){
  $exp[0] = "Opera";
  $exp[1] = "";
 }elseif(stripos($sys, "Chrome") > 0){
   $exp[0] = "Chrome";
   $exp[1] = "";
 }else{
  $exp = "未知浏览器";
  $exp[1] = "";
 }
 return $exp;
}
$lineid = $_GET['lineid'];
$stuser= $_GET['stuser'];
$contractname= $_GET['contractname'];
$sitecontact = $_GET['sitecontact'];
$sitephone = $_GET['sitephone'];
 if ($browser[0] == "Internet Explorer" ) {

     $lineid =  mb_convert_encoding("$lineid", "UTF-8", "gb2312");
     $stuser= mb_convert_encoding("$stuser", "UTF-8", "gb2312");
     $contractname =  mb_convert_encoding("$contractname", "UTF-8", "gb2312");
     $sitecontact = mb_convert_encoding("$sitecontact", "UTF-8", "gb2312");
        }
$email=$_GET['email'];
$subject="${contractname}-${lineid}-[${stuser}]";

$message="-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-<br />线路ID: $lineid<br />合同名: $contractname <br />系统中的联络人资料:$sitecontact-$sitephone<br />报障人：  报障来电：  <br />-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-<br />";

require('client.inc.php');
define('SOURCE','Web'); //Ticket source.
$ticket = null;
$errors=array();
if ($_POST) {
    $vars = $_POST;
    $vars['deptId']=$vars['emailId']=0; //Just Making sure we don't accept crap...only topicId is expected.
    if ($thisclient) {
        $vars['uid']=$thisclient->getId();
    } elseif($cfg->isCaptchaEnabled()) {
        if(!$_POST['captcha'])
            $errors['captcha']=__('Enter text shown on the image');
        elseif(strcmp($_SESSION['captcha'], md5(strtoupper($_POST['captcha']))))
            $errors['captcha']=__('Invalid - try again!');
    }

    $tform = TicketForm::objects()->one()->getForm($vars);
    $messageField = $tform->getField('message');
    $attachments = $messageField->getWidget()->getAttachments();
    if (!$errors && $messageField->isAttachmentsEnabled())
        $vars['cannedattachments'] = $attachments->getClean();

    // Drop the draft.. If there are validation errors, the content
    // submitted will be displayed back to the user
    Draft::deleteForNamespace('ticket.client.'.substr(session_id(), -12));
    //Ticket::create...checks for errors..
    if(($ticket=Ticket::create($vars, $errors, SOURCE))){
        $msg=__('Support ticket request created');
        // Drop session-backed form data
        unset($_SESSION[':form-data']);

        // post response - if any
        $response = null;
        if($vars['response'] && $thisstaff->canPostReply()) {

            $vars['response'] = $ticket->replaceVars($vars['response']);
            // $vars['cannedatachments'] contains the attachments placed on
            // the response form.
            $response = $ticket->postReply($vars, $errors, false);
        }
        //Send Notice to user --- if requested AND enabled!!
        $dept=$ticket->getDept();
        if(($tpl=$dept->getTemplate())
                && ($msg=$tpl->getNewTicketNoticeMsgTemplate())
                && ($email=$dept->getEmail())) {

            $message = (string) $ticket->getLastMessage();
            if($response) {
                $message .= ($cfg->isHtmlThreadEnabled()) ? "<br><br>" : "\n\n";
                $message .= $response->getBody();
            }

            if($vars['signature']=='mine')
                $signature=$thisstaff->getSignature();
            elseif($vars['signature']=='dept' && $dept && $dept->isPublic())
                $signature=$dept->getSignature();
            else
                $signature='';

            $attachments =($cfg->emailAttachments() && $response)?$response->getAttachments():array();

            $msg = $ticket->replaceVars($msg->asArray(),
                    array(
                        'message'   => $message,
                        'signature' => $signature,
                        'response'  => ($response) ? $response->getBody() : '',
                        'recipient' => $ticket->getOwner(), //End user
                        'staff'     => $thisstaff,
                        )
                    );

            $references = $ticket->getLastMessage()->getEmailMessageId();
            if (isset($response))
                $references = array($response->getEmailMessageId(), $references);
            $options = array(
                'references' => $references,
                'thread' => $ticket->getLastMessage()
            );
            $email->send($ticket->getOwner(), $msg['subj'], $msg['body'], $attachments,
                $options);
        }

        //Logged in...simply view the newly created ticket.
        if($thisclient && $thisclient->isValid()) {
            session_write_close();
            session_regenerate_id();
            @header('Location: tickets.php?id='.$ticket->getId());
        }
    }else{
        $errors['err']=$errors['err']?$errors['err']:__('Unable to create a ticket. Please correct errors below and try again!');
    }
}

//page
$nav->setActiveNav('new');
if ($cfg->isClientLoginRequired()) {
    if (!$thisclient) {
        require_once 'secure.inc.php';
    }
    elseif ($thisclient->isGuest()) {
        require_once 'login.php';
        exit();
    }
}

require(CLIENTINC_DIR.'header.inc.php');
if($ticket
        && (
            (($topic = $ticket->getTopic()) && ($page = $topic->getPage()))
            || ($page = $cfg->getThankYouPage())
        )) {
    // Thank the user and promise speedy resolution!
    echo Format::viewableImages($ticket->replaceVars($page->getBody()));
}
else {
    require(CLIENTINC_DIR.'autoopen.inc.php');
}
require(CLIENTINC_DIR.'footer.inc.php');
?>
