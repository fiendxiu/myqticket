<?php
if(!defined('OSTCLIENTINC')) die('Access Denied!');
$info=array();
if($thisclient && $thisclient->isValid()) {
    $info=array('name'=>$thisclient->getName(),
                'email'=>$thisclient->getEmail(),
                'phone'=>$thisclient->getPhoneNumber());
}

//$info=($_POST && $errors)?Format::htmlchars($_POST):$info;
$info=($_POST && $errors)?Format::htmlchars($_POST):($_GET ? Format::htmlchars($_GET) : $info);
if(!($info['subject'])){
  $info['subject'] = $subject;
}
if(!($info['message'])){
  $info['message'] = $message;
}
if(!($info['email'])){
  $info['email']=$email;
}
$namebuf=explode('@', $email);
$name=$namebuf[0];
if(!($info['name'])){
$info['name']=$name;
}
$form = null;
if (!$info['topicId'])
    $info['topicId'] = $cfg->getDefaultTopicId();

if ($info['topicId'] && ($topic=Topic::lookup($info['topicId']))) {
    $form = $topic->getForm();
    if ($_POST && $form) {
        $form = $form->instanciate();
        $form->isValidForClient();
    }
}

?>
<h1><?php echo __('Open a New Ticket');?></h1>
<p><?php echo __('Please fill in the form below to open a new ticket.');?></p>
<form id="ticketForm" method="post" action="opennew.php" enctype="multipart/form-data">
  <?php csrf_token(); ?>
  <input type="hidden" name="a" value="open">
  <table width="800" cellpadding="1" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td class="required"><?php echo __('Help Topic');?>:</td>
        <td>
            <select id="topicId" name="topicId" onchange="javascript:
                    var data = $(':input[name]', '#dynamic-form').serialize();
                    $.ajax(
                      'ajax.php/form/help-topic/' + this.value,
                      {
                        data: data,
                        dataType: 'json',
                        success: function(json) {
                          $('#dynamic-form').empty().append(json.html);
                          $(document.head).append(json.media);
                        }
                      });">
                <option value="" selected="selected">&mdash; <?php echo __('Select a Help Topic');?> &mdash;</option>
                <?php
                if($topics=Topic::getPublicHelpTopics()) {
                    foreach($topics as $id =>$name) {
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $id, ($info['topicId']==$id)?'selected="selected"':'', $name);
                    }
                } else { ?>
                    <option value="0" ><?php echo __('General Inquiry');?></option>
                <?php
                } ?>
            </select>
            <font class="error">*&nbsp;<?php echo $errors['topicId']; ?></font>
        </td>
    </tr>
<?php
        if (!$thisclient) {
            $uform = UserForm::getUserForm()->getForm($_POST);
            if ($_POST) $uform->isValid();
        //    $uform->render(false);
?>
<!--/******************************************************************************/-->
<tr><td colspan="2"><hr />
    <div class="form-header" style="margin-bottom:0.5em">
    <h3><?php echo Format::htmlchars($uform->getTitle()); ?></h3>
    <em><?php echo Format::htmlchars($uform->getInstructions()); ?></em>
    </div>
    </td></tr>
<tr><td>
    <label for="<?php echo $uform->getField('email')->getFormName(); ?>" class="<?php
                    if ($uform->getField('email')->get('required')) echo 'required'; ?>">
                <?php echo Format::htmlchars($uform->getField('email')->get('label')); ?>:</label>
    </td><td>
    <input type="text" size="40" maxlength="64" name="email" value="<?php echo $info['email']?>"/>
    <font class="error">*</font>
    <?php
            foreach ($uform->getField('email')->errors() as $e) { ?>
                <br />
                <font class="error"><?php echo $e; ?></font>
            <?php }
            $uform->getField('email')->renderExtras('client');
            ?>
    </td></tr>
<tr><td>
    <label for="<?php echo $uform->getField('name')->getFormName(); ?>" class="<?php
                    if ($uform->getField('name')->get('required')) echo 'required'; ?>">
                <?php echo Format::htmlchars($uform->getField('name')->get('label')); ?>:</label>
    </td><td>
    <input type="text" size="40" maxlength="64" name="name" value="<?php echo $info['name']?>"/>
    <font class="error">*</font>
    <?php
            foreach ($uform->getField('name')->errors() as $e) { ?>
                <br />
                <font class="error"><?php echo $e; ?></font>
            <?php }
            $uform->getField('name')->renderExtras('client');
            ?>
    </td></tr>
<tr><td>
    <label for="<?php echo $uform->getField('phone')->getFormName(); ?>" class="<?php
                    if ($uform->getField('phone')->get('required')) echo 'required'; ?>">
                <?php echo Format::htmlchars($uform->getField('phone')->get('label')); ?>:</label>
    </td><td>
    <input type="text" name="phone" value="<?php echo $info['phone']?>"/>
    <?php echo __('Ext'); ?>: <input type="text" name="phone-ext" value="<?php echo $info['phone-ext']?>" size="5"/>
    <?php
            foreach ($uform->getField('phone')->errors() as $e) { ?>
                <br />
                <font class="error"><?php echo $e; ?></font>
            <?php }
            $uform->getField('phone')->renderExtras('client');
            ?>
    </td></tr>
<?php
        }
        else { ?>
            <tr><td colspan="2"><hr /></td></tr>
        <tr><td><?php echo __('Email'); ?>:</td><td><?php echo $thisclient->getEmail(); ?></td></tr>
        <tr><td><?php echo __('Client'); ?>:</td><td><?php echo $thisclient->getName(); ?></td></tr>
        <?php } ?>
    </tbody>
    <tbody id="dynamic-form">
        <?php if ($form) {
            include(CLIENTINC_DIR . 'templates/dynamic-form.tmpl.php');
        } ?>
    </tbody>
    <tbody><?php
        $tform = TicketForm::getInstance();
        if ($_POST) {
            $tform->isValidForClient();
        }
    //    $tform->render(false); ?>
<tr><td colspan="2"><hr />
    <div class="form-header" style="margin-bottom:0.5em">
    <h3><?php echo Format::htmlchars($tform->getTitle()); ?></h3>
    <em><?php echo Format::htmlchars($tform->getInstructions()); ?></em>
    </div>
    </td></tr>
<tr><td>
    <label for="<?php echo $tform->getField('subject')->getFormName(); ?>" class="<?php
                    if ($tform->getField('subject')->get('required')) echo 'required'; ?>">
                <?php echo Format::htmlchars($tform->getField('subject')->get('label')); ?>:</label>
    </td><td>
    <input type="text" size="40" maxlength="64" name="subject" value="<?php  echo $info['subject']?>"/>
    <font class="error">*</font>
    <?php
            foreach ($tform->getField('subject')->errors() as $e) { ?>
                <br />
                <font class="error"><?php echo $e; ?></font>
            <?php }
            $tform->getField('subject')->renderExtras('client');
            ?>
    </td></tr>
<tr><td>
    <div style="margin-bottom:0.5em;margin-top:0.5em"><strong><?php
        echo Format::htmlchars($tform->getField('message')->get('label'));
        ?></strong>:</div></td></tr>
<tr><td colspan="2">
    <textarea style="width:100%;" name="message"
            placeholder="<?php echo Format::htmlchars($tform->getField('message')->getWidget()->field->get('hint')); ?>"
            data-draft-namespace="ticket.client"
            data-draft-object-id="<?php echo substr(session_id(), -12); ?>"
            class="richtext draft draft-delete ifhtml"
            cols="21" rows="8" style="width:80%;"><?php
    //        echo Format::htmlchars($tform->getField('message')->getWidget()->value); 
            echo $info['message'];
    ?></textarea>
    <?php
        $config = $tform->getField('message')->getWidget()->field->getConfiguration();
        if (!$config['attachments'])
            return;
        $attachments = $tform->getField('message')->getWidget()->getAttachments($config);
        print $attachments->render($client);
        foreach ($attachments->getMedia() as $type=>$urls) {
            foreach ($urls as $url)
                Form::emitMedia($url, $type);
        } ?>
    <font class="error">*</font>
    <?php
            foreach ($tform->getField('message')->errors() as $e) { ?>
                <br />
                <font class="error"><?php echo $e; ?></font>
            <?php }
            $tform->getField('message')->renderExtras('client');
            ?>
    </td></tr>
<?php /**********************************************************************************************/ ?>
    </tbody>
    <tbody>
    <?php
    if($cfg && $cfg->isCaptchaEnabled() && (!$thisclient || !$thisclient->isValid())) {
        if($_POST && $errors && !$errors['captcha'])
            $errors['captcha']=__('Please re-enter the text again');
        ?>
    <tr class="captchaRow">
        <td class="required"><?php echo __('CAPTCHA Text');?>:</td>
        <td>
            <span class="captcha"><img src="captcha.php" border="0" align="left"></span>
            &nbsp;&nbsp;
            <input id="captcha" type="text" name="captcha" size="6" autocomplete="off">
            <em><?php echo __('Enter the text shown on the image.');?></em>
            <font class="error">*&nbsp;<?php echo $errors['captcha']; ?></font>
        </td>
    </tr>
    <?php
    } ?>
    <tr><td colspan=2>&nbsp;</td></tr>
    </tbody>
  </table>
<hr/>
  <p style="text-align:center;">
        <input type="submit" value="<?php echo __('Create Ticket');?>">
        <input type="reset" name="reset" value="<?php echo __('Reset');?>">
        <input type="button" name="cancel" value="<?php echo __('Cancel'); ?>" onclick="javascript:
            $('.richtext').each(function() {
                var redactor = $(this).data('redactor');
                if (redactor && redactor.opts.draftDelete)
                    redactor.deleteDraft();
            });
            window.location.href='index.php';">
  </p>
</form>
