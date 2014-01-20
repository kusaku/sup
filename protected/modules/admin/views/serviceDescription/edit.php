<?php
/**
 * @var FSActiveForm $form
 * @var Service $service
 * @var ServiceDescriptionEditForm $model
 * @var ServiceDescriptionController $this
 */
$form = $this->beginWidget('FSActiveForm', array('id'=>'service-description-form', 'htmlOptions'=>array('class'=>'formBody')));
echo CHtml::openTag('div',array('class'=>'formRows'));
$arRA=array('class'=>'formRow');
echo $form->formRowHeader('Описание услуги: '.$service->name,array('class'=>'formRow fullRow'));
echo $form->formRowTextField($model,'title',$arRA);
echo $form->formRowTextField($model,'document_title',$arRA);
echo $form->formRowTextarea($model,'content',$arRA);
echo $form->formRowTextarea($model,'description',$arRA);
echo $form->formRowTextField($model,'link',$arRA);
echo $form->formRowTextField($model,'icon',$arRA);
echo $form->formRowTextField($model,'days',$arRA);
echo $form->formRowTextField($model,'category',$arRA);
echo $form->formRowSubmit('сохранить',array('class'=>'formRow'));
echo $form->formRowLink('Назад','/admin/serviceDescription',array('class'=>'formRow'));
echo CHtml::closeTag('div');
$this->endWidget();
