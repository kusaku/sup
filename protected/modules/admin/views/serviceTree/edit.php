<?php
/**
 * @var $this ServiceTreeController
 * @var $model ServiceTreeEditForm
 * @var $form FSActiveForm
 */
$form = $this->beginWidget('FSActiveForm', array('id'=>'service-tree-form', 'htmlOptions'=>array('class'=>'formBody')));
echo CHtml::openTag('div',array('class'=>'formRows'));
echo $form->formRowHeader('Услуга в дереве',array('class'=>'formRow fullRow'));
echo $form->hiddenField($model,'id');
if($model->parent_id>0) {
	$obParent=ServiceTree::model()->findByPk($model->parent_id);
	$arData=CHtml::listData(ServiceTree::model()->findAllByAttributes(array('parent_id'=>$obParent->parent_id)),'id','service.name');
} else {
	$arData=array_merge(array(0=>'Корень'),CHtml::listData(ServiceTree::model()->findAllByAttributes(array('parent_id'=>0)),'id','service.name'));
}
echo $form->formRowDropDownList($model,'parent_id',$arData,array('class'=>'formRow fullRow'));

$arServices=Service::model()->findAllByAttributes(array('parent_id'=>0));
$arItems=array();
foreach($arServices as $obServiceParent) {
	$arItems[]=$obServiceParent;
	foreach($obServiceParent->childs as $obService) {
		$obService->name='--'.$obService->name;
		$arItems[]=$obService;
	}
}
echo $form->formRowDropDownList($model,'service_id',CHtml::listData($arItems,'id','name'),array('class'=>'formRow fullRow'));
echo $form->formRowTextField($model,'order',array('class'=>'formRow fullRow'));
echo $form->formRowCheckbox($model,'hide_on_site',array('class'=>'formRow fullRow'));
echo $form->formRowSubmit('сохранить',array('class'=>'formRow'));
echo $form->formRowLink('Назад','/admin/serviceTree',array('class'=>'formRow'));
echo CHtml::closeTag('div');
$this->endWidget();