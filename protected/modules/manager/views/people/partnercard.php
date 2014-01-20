<div class="wrapper">
	<div class="addClientWindow" id="sm_content">
		<div class="clientHead">
			Клиент: <span><?php echo $user->fio?></span>
		</div>
		<?php $form = $this->beginWidget('CActiveForm', array('id'=>'partner-form', 'enableClientValidation'=>true, 'clientOptions'=>array('validateOnSubmit'=>true))); ?>
			<table>
				<tr>
					<td>
						<table border="0" cellspacing="5" class="formTable">
							<col width="200"/>
							<col width="280"/>
							<tr>
								<td align="right"><?php echo $form->label($model, 'name');?></td>
								<td align="left"><?php echo $form->textField($model,'name',array('tabindex'=>1));?></td>
							</tr>
							<tr>
								<td align="right"><?php echo $form->label($model, 'date_sign');?></td>
								<td align="left"><?php echo $form->textField($model,'date_sign',array('tabindex'=>2));?></td>
							</tr>
							<tr>
								<td align="right"><?php echo $form->label($model, 'agreement_num');?></td>
								<td align="left"><?php echo $form->textField($model,'agreement_num',array('tabindex'=>3));?></td>
							</tr>
							<tr>
								<td align="right"><?php echo $form->label($model,'manager_id');?></td>
								<td align="left"><?php echo $form->dropDownList($model,'manager_id',$model->getPeopleList(),array('tabindex'=>4));?></td>
							</tr>
						</table>
					</td>
					<td valign="top">
						<table border="0" cellspacing="5" class="formTable">
							<col width="200"/>
							<col width="280"/>
							<tr>
								<td align="right"><?php echo $form->label($model, 'status');?></td>
								<td align="left"><?php echo $form->dropDownList($model,'status',Partner::getStatuses(),array('tabindex'=>5));?></td>
							</tr>
							<tr>
								<td align="right"><?php echo $form->label($model, 'status_comment');?></td>
								<td align="left"><?php echo $form->textArea($model,'status_comment',array('tabindex'=>6));?></td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<div class="buttons">
				<a class="buttonSave">Сохранить</a>
				<a class="buttonCancel">Отмена</a>
			</div>
		<?php $this->endWidget();?>
		<?php if(is_array($log) && count($log)>0):
		$arStatuses=Partner::getStatuses();
		$i=count($log);
			?>
		<div style="clear:both;" class="orderBlock" id="orderBlock0">
			<div class="header">
				<a onClick="CardShowHide(0)" class="arrow"></a>
				<a onClick="CardShowHide(0)">Журнал изменения статуса</a>
			</div>
			<div style="max-height:300px;overflow:auto;" class="orderPart">
				<?php foreach ($log as $record): ?>
				<div class="subPart" style="border-bottom:1px dashed black;padding-left:0px;">
					<div class="column1" style="width:25px;"><p>#<?php echo $i?></p></div>
					<div class="column1">
						<p class="label">Менеджер:</p>
						<p><?= People::getNameById($record->manager_id)?></p>
					</div>
					<div class="column1">
						<p class="label">Дата:</p>
						<p><?= $record->date; ?></p>
					</div>
					<div class="column1">
						<p class="label">Статус:</p>
						<p style="width:170px;"><?= $arStatuses[$record->status]; ?></p>
					</div>
					<?php if($record->comment!=''):?>
						<div style="clear:both;padding:5px 0px;">
							<?= $record->comment; ?>
						</div>
					<?php endif?>
				</div>
				<?php $i--;endforeach; ?>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>
