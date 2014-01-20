<?php
/**
 * @var FSActiveForm $form
 * @var AdvertReportForm $model
 * @var ReportController $this
 */
$form = $this->beginWidget('FSActiveForm', array('id'=>'advert-report-form', 'htmlOptions'=>array('class'=>'formBody','style'=>'width:500px;')));
echo CHtml::openTag('div',array('class'=>'formRows'));
$arRA=array('class'=>'formRow');
echo $form->formRowHeader('Укажите диапазон дат',array('class'=>'formRow fullRow'));
echo $form->formRowDateField($model,'dt_from','d.m.Y H:i:s',$arRA);
echo $form->formRowDateField($model,'dt_to','d.m.Y H:i:s',$arRA);
echo $form->formRowSubmit('Сгенерировать',array('class'=>'formRow'));
echo CHtml::tag('div',array('style'=>'clear:both;'));
echo CHtml::closeTag('div');
echo CHtml::script('$("#AdvertReportForm_dt_from, #AdvertReportForm_dt_to").datetimepicker({
				showOtherMonths: true,
				selectOtherMonths: true,
				dateFormat: "dd.mm.yy",
				timeFormat: "hh:mm:ss",
				changeMonth: true,
				changeYear: true
			});');
$this->endWidget();