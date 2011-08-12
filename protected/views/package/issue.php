<?php
$issue = Redmine::getIssue($issue_id);
$issueNotClosed = $issue->status['id'] != 8 && $issue->status['id'] != 5;
$issueNotClosed = true;

print $issue->subject.' ('.$issue->done_ratio.'%)';
if ( $issueNotClosed ) print '<a onClick="redmineCloseIssue('.$issue->id.');" class="grayButton" style="clear: both; float: right;">Закрыть задачу</a>';
print '<br>';
print 'Задача в Redmine #'.$issue->id;
print '<div class="progressBar"><div class="progressStat" style="width:'.$issue->done_ratio.'%">'.$issue->done_ratio.'%</div></div>';
print 'Иполнитель: '.$issue->assigned_to['name'].'<br>';
print 'Описание: '.str_replace("\n", '<br>', $issue->description).'<br>';
print '<hr>';

if ($issue->journals->journal)
foreach ($issue->journals->journal as $journal)
{
	print $journal->user['name'].' ('.date('d-m-Y H:i', strtotime($journal->created_on)).')<br>';
	print nl2br(htmlspecialchars($journal->notes));
	print '<hr>';
}

if ( $issueNotClosed )
print '<textarea class="redmineMessage" id ="redmineMessageInput'.$issue->id.'" pack="'.$pack_id.'" serv="'.$serv_id.'"></textarea> <br>'.
		'<a onClick="redmineSendMessage('.$issue->id.');" class="orangeButton" style="clear: both; float: right;">Опубликовать</a>';
?>
