<?php
/**
 * @var CalendarController $this
 * @var Calendar[] $list
 */
if(is_array($list) && count($list)>0) {
	echo CHtml::openTag('ul',array('class'=>'formList noticeList'));
	$i=0;
	foreach($list as $obNotice) {
		$sClass='';
		if($obNotice->status==1) {
			$sClass.=' new';
		} else {
			$sClass.=' read';
		}
		if($obNotice->interval>0) {
			$sClass.=' repeat';
		}
		if($i++%2==0) {
			$sClass.=' odd';
		}
		echo CHtml::openTag('li',array('id'=>'notice'.$obNotice->id,'class'=>$sClass));
		echo CHtml::tag('div',array('class'=>'date'),date('d.m.Y',strtotime($obNotice->date)));
		$sDoneLink='';
		if($obNotice->status==1) {
			$sDoneLink=CHtml::link(CHtml::image('/images/icons/tick.png','',array('title'=>'Подтвердить прочтение')),'#noticesReady_'.$obNotice->id,array('class'=>'noticeReady'));
		}
		$sEditLink=CHtml::link(CHtml::image('/images/icons/wrench_orange.png','',array('title'=>'Отредактировать')),'#notice_'.$obNotice->id,array('class'=>'noticeEdit'));
		$sDelayMenu=$this->widget('manager.widgets.NoticeDelayMenuWidget',array('notice'=>$obNotice),true);
		echo CHtml::tag('div',array('class'=>'icons'),$sDoneLink.$sEditLink.$sDelayMenu);
		echo CHtml::tag('div',array('class'=>'message'),$obNotice->message);
		echo CHtml::closeTag('li');
	}
	echo CHtml::closeTag('ul');
}