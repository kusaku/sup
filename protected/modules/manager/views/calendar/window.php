<?php
/**
 * @var CalendarController $this
 * @var array $statuses
 * @var Calendar[] $list
 */
echo CHtml::openTag('div',array('class'=>'formWindow','style'=>'width:600px;'));
echo CHtml::tag('div',array('class'=>'formHead'),'Уведомления');
echo CHtml::openTag('div',array('class'=>'formBody','style'=>'padding-top:0px;'));
echo CHtml::openTag('div',array('class'=>'tabscontainer'));
echo CHtml::openTag('ul');
echo CHtml::tag('li',array('id'=>'tab0'),CHtml::tag('a',array('href'=>'#tabs-0'),'Новые ('.intval($statuses[1]['cnt']).')'));
echo CHtml::tag('li',array('id'=>'tab1'),CHtml::tag('a',array('href'=>'#tabs-1'),'Прочитанные ('.intval($statuses[0]['cnt']).')'));
echo CHtml::tag('li',array('id'=>'tab2'),CHtml::tag('a',array('href'=>'#tabs-2'),'Все'));
echo CHtml::closeTag('ul');
//new
echo CHtml::openTag('div',array('id'=>'tabs-0','class'=>'loaded'));
echo CHtml::openTag('div',array('class'=>'scrollPanel','style'=>'height:200px;'));
$this->renderPartial('list',array('list'=>$list));
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');
//old
echo CHtml::openTag('div',array('id'=>'tabs-1'));
echo CHtml::tag('div',array('class'=>'scrollPanel','style'=>'height:200px;'),'');
echo CHtml::closeTag('div');
//all
echo CHtml::openTag('div',array('id'=>'tabs-2'));
echo CHtml::tag('div',array('class'=>'scrollPanel','style'=>'height:200px;'),'');
echo CHtml::closeTag('div');
echo CHtml::closeTag('div').CHtml::closeTag('div').CHtml::closeTag('div');