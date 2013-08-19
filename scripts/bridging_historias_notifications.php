<?php
/* Script to fix a problem in Acquia's Drupal Commons software, where email notifications of new content
are not sent to members of private groups.  
Set as a new action, to the trigger "New content is created."
Use a new module to alter the mail as wished; ours is called "BH_notifications."
Marco Battistella and Laura Henze.   8/15/2013
*/

$uids = array();
if (is_array($context['node']->og_group_ref['und'])) {
  foreach ($context['node']->og_group_ref['und'] as $og_group_ref) {
    $group_id = $og_group_ref['target_id'];
    $result = db_query('SELECT flag_content.uid FROM flag_content WHERE flag_content.content_id  = :cid AND flag_content.fid = 5 ', array(':cid' => $group_id));
    foreach ($result as $record) {
      $uids[$record->uid] = $record->uid;
    }  
  }
}
$message_body = '';
if (is_array($context['node']->body['und'])) {
  foreach ($context['node']->body['und'] as $m) {
    $message_body = $m['value']."\n";
  }
}

$options = array('absolute' => TRUE);
$nid = $context['node']->nid;
$url = url('node/' . $nid, $options);
$sender = array();
$users = user_load_multiple($uids, $conditions = array(), $reset = FALSE);
	
foreach ($users as $u) {
  if ($u->uid == $context['node']->uid) {
    $sender['realname'] = $u->realname;
    $sender['email'] = $u->mail;
  }
  $email_message = 'Hi '.$u->realname.', '. $context['node']->nid . "  " . $sender['realname'].' created the '.$context['node']->type.' “'.$context['node']->title.'” on bridging historias on '.date('D, m/d/Y - h:i').':'.$message_body.'Permalink: '.$url.' <br />Add a comment at '.$url .'#comment-form';

  $headers = array(
	'MIME-Version' => '1.0',
	'Content-Type' => 'text/html; charset=UTF-8; format=flowed',
	'Content-Transfer-Encoding' => '8Bit',
	'X-Mailer' => 'Drupal'
  );
  $module = 'BH_notifications';
  $key = 'contact_message';
	
  $email = $wheretoNow;
  $language = language_default();
  $params = array(
	'subject' => t('New Post in Bridging Historias: '.$context['node']->title),
	'body' => t($email_message)
  );
  $from = NULL;
  $send = TRUE;
  $message = drupal_mail($module, $key, $u->mail, $language, $params, $from, $send);

}




