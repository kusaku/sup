<h1>Редактирование промокода</h1>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm'); ?>

	<p class="note">Поля, отмеченные <span class="required">*</span> необходимы.</p>

	<?php echo CHtml::errorSummary($model); ?>

	<div class="row">
		ID: <?php echo $model->id; ?>
	</div>

	<div class="row">
		Значение: <?php echo $model->value; ?>
	</div>

	<div class="row">
		Тип: <?php echo Yii::t('infocode', $model->type); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'descr'); ?>
		<?php echo $form->textArea($model,'descr',array()); ?>
		<?php echo $form->error($model,'descr'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Сохранить'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->