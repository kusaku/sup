<?php
/**
 * @var Package $package
 * @var Payment $payment
 * @var PaymentController $this
 */
echo CHtml::openTag('div',array('class'=>'formWindow','style'=>'width:600px;'));
if($payment->id>0) {
	$sTitle='Редактирование оплаты №'.$payment->id.' к заказу #'.$package->id;
} else {
	$sTitle='Добавление оплаты к заказу #'.$package->id;
}
echo CHtml::tag('div',array('class'=>'formHead'),$sTitle);
echo CHtml::openTag('div',array('class'=>'formBody'));
/**
 * @var FSActiveForm $form
 */
$arRA=array('class'=>'formRow');
$form=$this->beginWidget('FSActiveForm',array('id'=>'payment_form','action'=>Yii::app()->createUrl('manager/payment/save')));
echo $form->hiddenField($payment,'id');
echo $form->hiddenField($payment,'package_id');
echo CHtml::openTag('div',array('class'=>'formRows'));
echo $form->formRowTextarea($payment,'description',array('class'=>'formRow fullRow'));
echo $form->formRowTextField($payment,'amount',$arRA);
echo $form->formRowDateField($payment,'dt','d.m.Y',$arRA);
if(Yii::app()->user->checkAccess('topmanager') or Yii::app()->user->checkAccess('admin')) {
	echo $form->formRowCheckbox($payment,'ptype_id',$arRA);
	echo $form->formRowDateField($payment,'dt_pay','d.m.Y',$arRA);
}
echo CHtml::closeTag('div');
echo CHtml::openTag('div',array('class'=>'buttons'));
if($payment->isNewRecord) {
	echo CHtml::tag('a',array('class'=>'buttonSave buttonOrange','title'=>'Добавить платёж'),'Добавить платёж');
	echo CHtml::tag('a',array('class'=>'buttonSaveClose buttonOrange','title'=>'Добавить платёж и закрыть окно'),'Добавить и закрыть');
} else {
	echo CHtml::tag('a',array('class'=>'buttonSave buttonOrange','title'=>'Сохранить изменения'),'Сохранить');
	echo CHtml::tag('a',array('class'=>'buttonSaveClose buttonOrange','title'=>'Сохранить изменения и закрыть окно'),'Сохранить и закрыть');
	if(Yii::app()->user->checkAccess('topmanager') or Yii::app()->user->checkAccess('admin')) {
		echo CHtml::tag('a',array('class'=>'buttonDelete buttonGray','title'=>'Удалить платёж'),'Удалить');
	}
}
echo CHtml::tag('a',array('class'=>'buttonCancel buttonGray','title'=>'Закрыть окно редактирования'),'Закрыть');
echo CHtml::tag('div',array('style'=>"overflow:hidden;visibility: hidden;height: 1px;"),CHtml::submitButton('сохранить'));
if(isset($save) && $save=='ok') {
	echo CHtml::tag('span',array('class'=>'save-result'),'Запись успешно сохранена');
}
echo CHtml::closeTag('div');
$this->endWidget();
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');