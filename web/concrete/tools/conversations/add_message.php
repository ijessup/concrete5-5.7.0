<?php defined('C5_EXECUTE') or die("Access Denied.");
$ax = Loader::helper('ajax');
$vs = Loader::helper('validation/strings');
$ve = Loader::helper('validation/error');
$cnvMessageSubject = null;
if (Loader::helper('validation/numbers')->integer($_POST['cnvID'])) {
	$cn = Conversation::getByID($_POST['cnvID']);
}
if (!is_object($cn)) {
	$ve->add(t('Invalid conversation.'));
}
if (!Loader::helper('validation/token')->validate('add_conversation_message', $_POST['token'])) {
	$ve->add(t('Invalid conversation post token.'));
}
if (!$vs->notempty($_POST['cnvMessageBody'])) {
	$ve->add(t('Your message cannot be empty.'));
}

if (Loader::helper('validation/numbers')->integer($_POST['cnvMessageParentID']) && $_POST['cnvMessageParentID'] > 0) {
	$parent = ConversationMessage::getByID($_POST['cnvMessageParentID']);
	if (!is_object($parent)) {
		$ve->add(t('Invalid parent message.'));
	}
}

if (Loader::helper('validation/banned_words')->hasBannedWords($_POST['cnvMessageBody'])) {
	$ve->add(t('Banned words detected.'));
}

if ($ve->has()) {
	$ax->sendError($ve);
} else {
	$msg = $cn->addMessage($cnvMessageSubject, $_POST['cnvMessageBody'], $parent);
	if($_POST['attachments'] && count($_POST['attachments'])) {
		foreach($_POST['attachments'] as $attachmentID) {
			ConversationMessage::attachFile(File::getByID($attachmentID), $msg->cnvMessageID);
		}
	}
	$ax->sendResult($msg);
}