<?php if ($issue_id): ?>
<?php 
try {
	$issue = Redmine::readIssue($issue_id, false);
}
catch(CHttpException $e) {
	
?>
	<div id="redmineIssue" style="width:95%;margin: 0 auto;">
		<h3>Не удалось открыть задачу <a target="_blank" href="https://redmine.fabricasaitov.ru/issues/<?= $issue_id; ?>">#<?= $issue_id; ?></a></h3>
		<h3>Есть ли у Вас доступ в Redmine? Существует ли такая задача?</h3>
		<div style="clear:both;margin: 10px 0;background-color:#eeeeee">
			<pre><?= $e->getMessage(); ?></pre>
		</div>
		<div style="clear:both;margin: 10px 0;background-color:#eeeeee">
			<div>Связать с задачей</div>
			<label>Номер задачи:</label>
			<input id="input<?= $serv_id; ?>" size="6" maxlength="6" />
			<a onClick="bindRedmineIssue(<?= $pack_id; ?>, <?= $serv_id; ?>); $(this).attr('onClick', '');" class="orangeButton">Связать</a>
		</div>
	</div>
<?php 
return;
}

$issueIsOpen = !in_array($issue['status']['id'], array(
	5,8
));

?>
	<div id="redmineIssue" style="width:95%;margin: 0 auto;">
		<h3>
			<div style="float:left;">
				<?= $issue['subject']; ?>
			</div>
			<div style="float:left;margin-left:10px;">
				<a target="_blank" href="https://redmine.fabricasaitov.ru/issues/<?= $issue_id; ?>">#<?= $issue_id; ?></a>
			</div>
			<div style="float:left;margin-left:10px;" class="progressBar">
				<div class="progressStat" style="width:<?= $issue['done_ratio']; ?>%">
					<?= $issue['done_ratio']; ?>%
				</div>
			</div>
			<?php if ($issueIsOpen): ?>
			<a style="float:right;" onClick="redmineCloseIssue(<?= $issue_id?>, <?= $pack_id; ?>, <?= $serv_id; ?>); $(this).attr('onClick', '')" class="orangeButton">Закрыть задачу</a>
			<?php endif; ?>
		</h3>
		<?php if ($issue['description']): ?>
		<div style="clear:both;margin: 10px 0;background-color:#eeeeee">
			<b>описание:</b>
			<?= nl2br($issue['description']); ?>
		</div style="clear:both;">
		<?php endif; ?>
		<div style="clear:both;margin: 10px 0;background-color:#eeeeee">
			<b>исполнитель:</b>
			<?= @$issue['assigned_to']['name']; ?>
		</div>
		<?php if (count($issue['journals']) > 1): ?>
		<div style="clear:both;margin: 10px 0;background-color:#eeeeee">
			<b>сообщения:</b>
		</div>
		<?php foreach ($issue['journals'] as $journal): ?>
		<?php if (is_array($journal) and ! empty($journal['notes'])): ?>
		<div style="clear:both;margin: 10px 0;">
			<div>
				<b><?= $journal['user']['name']; ?></b>
				@ <?= date('d-m-Y H:i', strtotime($journal['created_on'])); ?>
			</div>
			<div style="margin-bottom:10px;">
				<?= nl2br($journal['notes']); ?>
			</div>
		</div>
		<?php endif; ?>
		<?php endforeach; ?>
		<?php endif; ?>
		<div style="clear:both;margin: 10px 0;background-color:#eeeeee">
			<b>добавить сообщение:</b>
			<textarea style="width:100%;height:100px;" id ="redmineMessageInput<?= $issue_id;?>"></textarea>
			<div>
				<a onClick="redmineSendMessage(<?= $issue_id?>, <?= $pack_id;?>, <?= $serv_id;?>); $(this).attr('onClick', '');" class="orangeButton" style="float:right;">Опубликовать</a>
			</div>
		</div>
	</div>	
<?php else : ?>
	<div id="redmineIssue" style="width:95%;margin: 0 auto;">
		<h3>В редмайне пока пусто</h3>
		<div style="clear:both;margin: 10px 0;background-color:#eeeeee">
			<div>Требуется выбрать мастера и отдать задачу в работу.</div>
			<label>Выберите мастера:</label>
			<?php 
			$this->renderPartial('/snippets/userselect', $serv_id ? array(
				'group_id'=>5,'selected'=>Serv2pack::getByIds($serv_id, $pack_id)->master_id,'index'=>'master'
			) : array(
				'group_id'=>4,'selected'=>Yii::app()->user->id,'index'=>'master'
			));
			?>
			<a onClick="newRedmineIssue(<?= $pack_id; ?>, <?= $serv_id; ?>); $(this).attr('onClick', '');" class="orangeButton">Отдать в работу</a>
		</div>
		<div style="clear:both;margin: 10px 0;background-color:#eeeeee">
			<div>Связать с задачей</div>
			<label>Номер задачи:</label>
			<input type="text" id="input<?= $serv_id; ?>" size="6" maxlength="6" />
			<a onClick="bindRedmineIssue(<?= $pack_id; ?>, <?= $serv_id; ?>); $(this).attr('onClick', '');" class="orangeButton">Связать</a>
		</div>
	</div>
<?php endif; ?>