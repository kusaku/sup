<?php 
$issue = Redmine::readIssue($issue_id, false);
$issueNotClosed = ($issue['status']['id'] != 8 or $issue['status']['id'] != 5);
?>
<div id="redmineIssue<?= $issue['id'];?>">
	<h3 style="margin-bottom:10px;">
		<div style="float:left;"><?= $issue['subject']; ?></div>
		<div style="float:left;margin-left:10px;" class="progressBar">
			<div class="progressStat" style="width:<?= $issue['done_ratio']; ?>%">
				<?= $issue['done_ratio']; ?>%
			</div>
		</div>	
	</h3>
	<div style="width: 48%; float:right">
		<b>описание:</b>
		<?= nl2br(@$issue['description']); ?>
	</div>
	<div style="width: 48%; float:left">
		<b>исполнитель:</b>
		<?= @$issue['assigned_to']['name']; ?>
	</div>	
	<?php if (is_array($issue['journals'])): ?>
	<div style="clear:both;"><b>переписка в редмайне:</b></div>	
	<?php foreach ($issue['journals'] as $journal): ?>
	<?php if (@$journal['notes']): ?>
		<div>
			<b><?= $journal['user']['name']; ?></b> @ <?= date('d-m-Y H:i', strtotime($journal['created_on'])); ?>
		</div>
		<div style="margin-bottom:10px;">
			<?= $journal['notes']; ?>
		</div>
	<?php endif; ?>
	<?php endforeach; ?>
	<?php endif; ?>
	<?php if ($issueNotClosed): ?>
	<textarea class="redmineMessage" id ="redmineMessageInput<?= $issue['id'];?>" pack="<?= $pack_id;?>" serv="<?= $serv_id;?>"></textarea>
	<div style="width:95%;">
		<a onClick="redmineCloseIssue(<?= $issue['id']?>);" class="orangeButton">Закрыть задачу</a>
		<a onClick="redmineSendMessage(<?= $issue['id']?>);" class="orangeButton" style="float:right;">Опубликовать</a>	
	</div>
	<?php endif; ?>
</div>