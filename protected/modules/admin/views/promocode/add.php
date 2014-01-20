<h1>Добавление промокода</h1>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm'); ?>

	<p class="note">Поля, отмеченные <span class="required">*</span> необходимы.</p>

	<?php echo CHtml::errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'value'); ?>
		<?php echo $form->textField($model,'value',array()); ?>
		<?php echo $form->error($model,'value'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'descr'); ?>
		<?php echo $form->textArea($model,'descr',array()); ?>
		<?php echo $form->error($model,'descr'); ?>
	</div>



	<div class="row">
		<?php echo $form->labelEx($model,'type'); ?>
		<?php echo $form->dropDownList($model,'type',$model->getTypes()); ?>
		<?php echo $form->error($model,'type'); ?>
	</div>

	<?php /*

	<div class="row">
		<?php echo $form->labelEx($model,'content'); ?>
		<?php echo CHtml::activeTextArea($model,'content',array('rows'=>10, 'cols'=>70)); ?>
		<p class="hint">You may use <a target="_blank" href="http://daringfireball.net/projects/markdown/syntax">Markdown syntax</a>.</p>
		<?php echo $form->error($model,'content'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'tags'); ?>
		<?php $this->widget('CAutoComplete', array(
			'model'=>$model,
			'attribute'=>'tags',
			'url'=>array('suggestTags'),
			'multiple'=>true,
			'htmlOptions'=>array('size'=>50),
		)); ?>
		<p class="hint">Please separate different tags with commas.</p>
		<?php echo $form->error($model,'tags'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'status'); ?>
		<?php echo $form->dropDownList($model,'status',Lookup::items('PostStatus')); ?>
		<?php echo $form->error($model,'status'); ?>
	</div>
*/ ?>

	<div class="row buttons">
		<?php echo CHtml::submitButton('Добавить'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->