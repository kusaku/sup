<?php
/**
 * @var $branch ServiceTree[]
 * @var $this ServiceTreeBranchWidget
 */
echo CHtml::openTag('ul',array('class'=>'treeBranch'));
$i=0;
$iSize=count($branch);
foreach($branch as $obLeaf) {
	echo CHtml::openTag('li');
	echo CHtml::openTag('span',array('class'=>'row'));
	echo CHtml::tag('span',array('class'=>'id'),$obLeaf->service->id);
	if($obLeaf->hide_on_site==1) {
		echo CHtml::image('/images/icons/bullet_red.png','Не отображать на сайтах',array('style'=>'float:left;'));
	} else {
		echo CHtml::image('/images/icons/bullet_green.png','Отображать на сайтах',array('style'=>'float:left;'));
	}
	echo CHtml::tag('a',array('class'=>'title','href'=>Yii::app()->createUrl('admin/serviceTree/edit',array('id'=>$obLeaf->id))),$obLeaf->service->getTitle());
	echo CHtml::openTag('span',array('class'=>'leaf-buttons'));
		if($i>0)
			echo CHtml::tag('a',array('class'=>'up','href'=>Yii::app()->createUrl('admin/serviceTree/up',array('id'=>$obLeaf->id))),'');
		if(($i+1)<$iSize)
			echo CHtml::tag('a',array('class'=>'down','href'=>Yii::app()->createUrl('admin/serviceTree/down',array('id'=>$obLeaf->id))),'');
		echo CHtml::tag('a',array('class'=>'delete','href'=>Yii::app()->createUrl('admin/serviceTree/delete',array('id'=>$obLeaf->id))),'');
		echo CHtml::tag('a',array('class'=>'addChild','href'=>Yii::app()->createUrl('admin/serviceTree/edit',array('id'=>0,'parent'=>$obLeaf->id))),'');
	echo CHtml::closeTag('span');
	echo CHtml::closeTag('span');
	$this->widget('admin.widgets.ServiceTreeBranchWidget',array('arLeafs'=>$obLeaf->childs,'depth'=>$depth+1));
	echo CHtml::closeTag('li');
	$i++;
}
echo CHtml::closeTag('ul');