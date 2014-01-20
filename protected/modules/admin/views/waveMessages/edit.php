<?php
/**
 * @var WaveMessagesController $this
 * @var WaveMessageTemplates $model
 */
?>
<h1><?php if($model->id==0):?>Создании сообщения<?php else:?>Редактирование сообщения<?php endif?></h1>
<?php
/**
 * @var CActiveForm $form
 */
$form = $this->beginWidget('CActiveForm', array('id'=>'wave-message-template-form', 'htmlOptions'=>array('class'=>'form'))); ?>
	<?php echo $form->hiddenField($model,'id');?>
	<div class="row">
		<?php echo $form->label($model, 'content'); ?>
		<?php echo $form->textArea($model,'content');?>
	</div>
	<?php echo CHtml::submitButton('сохранить'); ?>
<?php $this->endWidget(); ?>