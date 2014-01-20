<?php
/**
 * @var PeopleController $this
 * @var PeopleContacts $model
 * @var FSActiveForm $form
 */
$form=$this->beginWidget('FSActiveForm',array('method'=>'POST','id'=>'contact-form'));
echo $form->hiddenField($model,'id');
echo $form->hiddenField($model,'people_id');
echo CHtml::openTag('div',array('class'=>'formRows'));
$arRA=array('class'=>'formRow fullRow');
if($model->id<1) {
	echo $form->formRowHeader('Добавление контакта',$arRA);
} else {
	echo $form->formRowHeader('Редактирование контакта',$arRA);
}
echo $form->formRowTextField($model,'fio',$arRA);
echo $form->formRowTextField($model,'email',$arRA);
echo $form->formRowTextField($model,'phone',$arRA);
echo $form->formRowTextField($model,'mobile',$arRA);
echo $form->formRowTextarea($model,'comment',$arRA);
echo CHtml::tag('div',array('style'=>'clear:both;'));
echo CHtml::closeTag('div');
echo CHtml::openTag('div',array('class'=>'buttons'));
echo CHtml::tag('a',array('class'=>'buttonSave buttonOrange','href'=>'#!'),'Сохранить');
echo CHtml::tag('a',array('class'=>'buttonCancel buttonGray','href'=>'#!'),'Закрыть');
if($model->id>0) {
	echo CHtml::tag('a',array('class'=>'buttonDelete buttonGray','href'=>'#!'),'Удалить');
}
echo CHtml::closeTag('div');
echo CHtml::tag('div',array('style'=>"overflow:hidden;visibility: hidden;height: 1px;"),CHtml::submitButton('сохранить'));
$this->endWidget();