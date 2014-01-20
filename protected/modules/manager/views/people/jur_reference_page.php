<?php
/**
 * @var $this PeopleController
 * @var $user People
 * @var $model JurPersonReferenceForm
 */
echo CHtml::openTag('div',array('style'=>'width:800px;'));
echo CHtml::openTag('div',array('class'=>'formWindow','style'=>'margin-bottom:-12px;'));
if($model->id>0) {
	$sTitle='Редактирование реквизитов #'.$model->id.' пользователя '.$user->fio.' ('.$user->mail.')';
} else {
	$sTitle='Создание реквизитов: '.$user->fio.' ('.$user->mail.')';
}
echo CHtml::tag('div',array('class'=>'formHead'),$sTitle);
echo CHtml::openTag('div',array('class'=>'formBody','style'=>'padding:10px;'));
/**
 * @var $form FSActiveForm
 */
$form=$this->beginWidget('FSActiveForm',array('id'=>'jur-reference-form','action'=>Yii::app()->createUrl('manager/people/jur_reference',array('id'=>$user->id))));
echo $form->hiddenField($model,'id');
$arRA=array('class'=>'formRow');
$arTypes=JurPersonReferenceForm::getTypeList();
$arSources=JurPersonReferenceForm::getSourceList();
$arParams=array();
if($model->internal) {
	$arParams['disabled']=true;
}
echo CHtml::openTag('div',array('class'=>'formRows'));
echo $form->formRowTextField($model,'title',$arRA,$arParams);
echo $form->formRowTextField($model,'inn',$arRA,$arParams);
echo $form->formRowTextField($model,'address',$arRA,$arParams);
echo $form->formRowTextField($model,'real_address',$arRA,$arParams);
echo $form->formRowTextField($model,'settlement_account',$arRA,$arParams);
echo $form->formRowTextField($model,'correspondent_account',$arRA,$arParams);
echo $form->formRowTextField($model,'bank_title',$arRA,$arParams);
echo $form->formRowTextField($model,'bank_bik',$arRA,$arParams);
echo $form->formRowTextField($model,'director_fio',$arRA,$arParams);
echo $form->formRowDropDownList($model,'type',$arTypes,$arRA,$arParams);
echo CHtml::openTag('div',array('class'=>'ltdData panel'.($model->type!='ltd'?' hidden':'')));
echo $form->formRowTextField($model,'kpp',$arRA,$arParams);
echo $form->formRowTextField($model,'director_position',$arRA,$arParams);
echo $form->formRowDropDownList($model,'director_source',$arSources,$arRA,$arParams);
echo $form->formRowTextField($model,'director_source_info',$arRA,$arParams);
echo CHtml::closeTag('div');
echo CHtml::openTag('div',array('class'=>'ipData panel'.($model->type!='ip'?' hidden':'')));
echo $form->formRowTextField($model,'egrip',$arRA,$arParams);
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');
echo CHtml::openTag('div',array('class'=>'buttons','style'=>'padding:10px 0;'));
if($model->internal==0) {
	echo CHtml::tag('a',array('class'=>'buttonSave','href'=>'#!'),'Сохранить');
}
echo CHtml::tag('a',array('class'=>'buttonCancel','href'=>'#!'),'Закрыть');
if(isset($save) && $save=='ok') {
	echo CHtml::tag('span',array('class'=>'save-result'),'Запись успешно сохранена');
}
echo CHtml::closeTag('div');
echo CHtml::tag('div',array('style'=>"overflow:hidden;visibility: hidden;height: 1px;"),CHtml::submitButton('сохранить'));
$this->endWidget();
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');

