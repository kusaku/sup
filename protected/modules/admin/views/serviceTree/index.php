<?php
/**
 * @var $root ServiceTree[]
 * @var $this ServiceTreeController
 */
echo CHtml::openTag('ul',array('class'=>'treeBranch'));
echo CHtml::openTag('li');
echo CHtml::openTag('span',array('class'=>'row'));
echo CHtml::tag('span',array('class'=>'id'),0);
echo CHtml::tag('a',array('class'=>'title'),'Корень');
echo CHtml::openTag('span',array('class'=>'leaf-buttons'));
echo CHtml::tag('a',array('class'=>'addChild','href'=>Yii::app()->createUrl('admin/serviceTree/edit',array('id'=>0,'parent'=>0))),'');
echo CHtml::closeTag('span');
echo CHtml::closeTag('span');
$this->widget('admin.widgets.ServiceTreeBranchWidget',array('arLeafs'=>$root,'depth'=>0));
echo CHtml::closeTag('li');
echo CHtml::closeTag('ul');
