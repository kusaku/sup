<?php
/**
 * @var CalendarController $this
 * @var Calendar $event
 * @var FSActiveForm $form
 */
echo CHtml::openTag('div',array('class'=>'formWindow','style'=>'width:450px;'));
echo CHtml::tag('div',array('class'=>'formHead'),'Событие с напоминанием.');
echo CHtml::openTag('div',array('class'=>'formBody'));
$form=$this->beginWidget('FSActiveForm',array('method'=>'POST','id'=>'event-edit-form'));
echo $form->hiddenField($event,'id');
echo $form->hiddenField($event,'people_id');
echo CHtml::openTag('div',array('class'=>'formRows'));
$arRA=array('class'=>'formRow fullRow');
echo $form->formRowDateField($event,'date','d.m.Y',$arRA);
echo $form->formRowTextarea($event,'message',array('class'=>'formRow fullRow doubleHeight'));
echo $form->formRowDropDownList($event,'interval',$this->getIntervals(),$arRA);
echo CHtml::closeTag('div');
echo CHtml::openTag('div',array('class'=>'buttons'));
echo CHtml::tag('a',array('class'=>'buttonSave buttonOrange','title'=>'Сохранить уведомление'),'Сохранить');
echo CHtml::tag('a',array('class'=>'buttonSaveClose buttonOrange','title'=>'Сохранить уведомление и закрыть окно'),'Сохранить и закрыть');
echo CHtml::tag('a',array('class'=>'buttonCancel buttonGray','title'=>'Закрыть окно редактирования'),'Закрыть');
echo CHtml::tag('div',array('style'=>"overflow:hidden;visibility: hidden;height: 1px;"),CHtml::submitButton('сохранить'));
if(isset($save) && $save=='ok') {
	echo CHtml::tag('span',array('class'=>'save-result'),'Запись успешно сохранена');
}
echo CHtml::closeTag('div');
$this->endWidget();
echo CHtml::closeTag('div');echo CHtml::closeTag('div');
