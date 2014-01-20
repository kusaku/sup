<?php
/**
 * @var LastNoticeWidget $this
 * @var Calendar[] $notices
 * @var string $listHash
 * @var integer $mode
 */
echo CHtml::openTag('div',array('class'=>'noticesRow'));
if($mode==1) {
	echo CHtml::tag('div',array('class'=>'notifier notice'),CHtml::image('/images/t.gif','',array('width'=>32,'height'=>32)));
} else {
	echo CHtml::tag('div',array('class'=>'notifier ok'),CHtml::image('/images/t.gif','',array('width'=>32,'height'=>32)));
}
echo CHtml::openTag('div',array('class'=>'list','id'=>$listHash));
$this->widget('manager.widgets.LastNoticeListWidget',array('list'=>$notices));
echo CHtml::closeTag('div');
echo CHtml::tag('div',array('class'=>'link'),CHtml::link(CHtml::image('/images/icons/viewstack.png','',array('title'=>'Все уведомления')),'#notices'));
echo CHtml::closeTag('div');