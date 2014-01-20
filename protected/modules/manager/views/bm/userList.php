<?php
/**
 * @var CArrayDataProvider $list
 * @var BMController $this
 * @var BMUserFilterForm $filter
 */
echo CHtml::openTag('div',array('class'=>'formWindow','style'=>'width:700px;'));
echo CHtml::tag('div',array('class'=>'formHead'),'Список пользователей BillManager');
echo CHtml::openTag('div',array('class'=>'formBody','style'=>'padding:0;'));
echo CHtml::openTag('div',array('class'=>'formRows'));
/**
 * @var FSActiveForm $obForm
 */
$obForm=$this->beginWidget('FSActiveForm',array('action'=>$this->createUrl('manager/bm'),'id'=>'filter'));
echo $obForm->formRowTextField($filter,'email',array('class'=>'formRow'));
echo $obForm->formRowTextField($filter,'name',array('class'=>'formRow'));
echo $obForm->formRowTextField($filter,'realname',array('class'=>'formRow'));
echo $obForm->formRowTextField($filter,'id',array('class'=>'formRow'));
echo $obForm->formRowCheckbox($filter,'disabled',array('class'=>'formRow'),array('value'=>'on','uncheckValue'=>'off'));
echo $obForm->formRowCheckbox($filter,'superuser',array('class'=>'formRow'),array('value'=>'on','uncheckValue'=>'off'));
echo CHtml::openTag('div',array('class'=>'buttons'));
echo CHtml::tag('a',array('class'=>"buttonFilter buttonOrange"),'Фильтр');
echo CHtml::tag('a',array('class'=>"buttonClearfilter buttonGray"),'Отменить');
if($filter->isActive()) {
	echo CHtml::tag('span',array('class'=>'save-result'),'записи отфильтрованы');
}
echo CHtml::tag('div',array('style'=>"overflow:hidden;visibility: hidden;height: 1px;"),CHtml::submitButton('сохранить'));
echo CHtml::closeTag('div');
$this->endWidget();
echo CHtml::closeTag('div');
$this->widget('zii.widgets.grid.CGridView',array('dataProvider'=>$list,'columns'=>array(
	'id'=>array('name'=>'id','header'=>'ID'),
	'name'=>array('name'=>'name','header'=>'Логин'),
	'realname'=>array('name'=>'realname','header'=>'ФИО'),
	'email'=>array('name'=>'email','header'=>'E-mail'),
	'disabled'=>array('name'=>'disabled','header'=>'Акт.','value'=>'$data->disabled=="yes"?"нет":"да"'),
	'superuser'=>array('name'=>'superuser','header'=>'Админ.','value'=>'$data->superuser=="yes"?"да":"нет"'),
	'hasPeople'=>array('class'=>'CLinkColumn','header'=>'Клиент','labelExpression'=>'$data->getPeopleName()','urlExpression'=>'"#people_".$data->getPeopleId()')
),'template'=>'{items} {pager}','pager'=>array('header'=>'')));
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');