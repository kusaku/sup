<?php
/**
 * @var CabinetController $this
 * @var WaveMessageTemplates[] $models
 * @var WaveMessageTemplates[] $mymodels
 */
echo CHtml::openTag('div',array('class'=>'formWindow','style'=>'width:600px;'));
$sTitle='Вставить шаблон сообщения';
echo CHtml::tag('div',array('class'=>'formHead'),$sTitle);
echo CHtml::openTag('div',array('class'=>'formBody','style'=>'padding-top:0px;'));
echo CHtml::openTag('div',array('class'=>'tabscontainer'));
echo CHtml::openTag('ul');
echo CHtml::tag('li',array('id'=>'tab0'),CHtml::tag('a',array('href'=>'#tabs-0'),'Общие'));
echo CHtml::tag('li',array('id'=>'tab1'),CHtml::tag('a',array('href'=>'#tabs-1'),'Мои'));
echo CHtml::closeTag('ul');
echo CHtml::openTag('div',array('style'=>'height:400px;overflow:auto;','id'=>'tabs-0'));
echo CHtml::openTag('div',array('class'=>'scrollPanel','style'=>'height:400px;'));
echo CHtml::openTag('ul',array('class'=>'formList'));
if(count($models)>0) {
	foreach($models as $obMessage) {
		echo CHtml::tag('li',array('class'=>'item'),CHtml::tag('a',array('class'=>'message'),nl2br(strip_tags($obMessage->content))).CHtml::hiddenField('',$obMessage->content));
	}
}
echo CHtml::closeTag('ul');
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');
echo CHtml::openTag('div',array('style'=>'height:400px;','id'=>'tabs-1'));
echo CHtml::openTag('div',array('class'=>'scrollPanel','style'=>'height:336px;width:600px;'));
echo CHtml::openTag('ul',array('class'=>'formList'));
if(count($mymodels)>0) {
	foreach($mymodels as $obMessage) {
		echo CHtml::tag('li',array('class'=>'item'),CHtml::tag('a',array('class'=>'message'),nl2br(strip_tags($obMessage->content))).
			CHtml::hiddenField('content',$obMessage->content).
			CHtml::hiddenField('id',$obMessage->id).
			CHtml::tag('a',array('class'=>'editMyMesssage','title'=>'Редактировать сообщение'),CHtml::image('/images/comments/pencil.png')).
			CHtml::tag('a',array('class'=>'deleteMyMesssage','title'=>'Удалить сообщение'),CHtml::image('/images/icons/cross.png')));
	}
}
echo CHtml::closeTag('ul');
echo CHtml::closeTag('div');
/**
 * @var CActiveForm $form
 */
$form=$this->beginWidget('CActiveForm',array('action'=>Yii::app()->createUrl('manager/cabinet/messageTemplateAdd'),'method'=>'post','htmlOptions'=>array('id'=>'message-form','class'=>'messageTemplateForm')));
echo $form->hiddenField($message,'id');
echo $form->textArea($message,'content');
echo CHtml::openTag('div',array('class'=>'buttons'));
echo CHtml::tag('a',array('class'=>"buttonSave buttonOrange"),'Добавить');
echo CHtml::tag('a',array('class'=>"buttonClose buttonGray"),'Закрыть');
echo CHtml::closeTag('div');
echo CHtml::openTag('div',array('class'=>'buttons buttonsUpdate','style'=>'display:none;'));
echo CHtml::tag('a',array('class'=>"buttonUpdate buttonOrange"),'Сохранить');
echo CHtml::tag('a',array('class'=>"buttonCancel buttonGray"),'Отмена');
echo CHtml::closeTag('div');
$this->endWidget();
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');

echo CHtml::closeTag('div');
echo CHtml::closeTag('div');