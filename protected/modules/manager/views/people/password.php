<?php
/**
 * @var PeoplePasswordForm $model
 * @var People $people
 * @var PeopleController $this
 * @var FSActiveForm $form
 */
echo CHtml::openTag('div',array('class'=>'formWindow','style'=>'width:400px;'));
echo CHtml::tag('div',array('class'=>'formHead'),'Генерация пароля для пользователя');
echo CHtml::openTag('div',array('class'=>'formBody','style'=>'padding:0 0 10px;'));
$form=$this->beginWidget('FSActiveForm',array('method'=>'POST','id'=>'password-form'));
echo $form->hiddenField($model,'id');
echo CHtml::tag('p',array('style'=>'padding:0 10px;'),'Вы действительно хотите сгенерировать новый пароль для пользователя <a href="#people_'.$people->id.'">@'.$people->id.'</a> <b>'.$people->mail.'</b>?');
echo CHtml::openTag('div',array('class'=>'formRows'));
echo $form->formRowCheckbox($model,'notice_email',array('class'=>'formRow fullRow'),array(),array('style'=>'width:315px;'));
echo CHtml::closeTag('div');
echo CHtml::openTag('div',array('class'=>'buttons'));
echo CHtml::tag('a',array('class'=>'buttonSave buttonOrange','title'=>'Сгенерировать новый пароль'),'сгенерировать');
echo CHtml::tag('a',array('class'=>'buttonCancel buttonGray','title'=>'Закрыть окно'),'Закрыть');
echo CHtml::tag('div',array('style'=>"overflow:hidden;visibility: hidden;height: 1px;"),CHtml::submitButton('сохранить'));
if(isset($save) && $save=='ok') {
	echo CHtml::tag('span',array('class'=>'save-result'),'Новый пароль установлен');
}
echo CHtml::closeTag('div');
$this->endWidget();
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');