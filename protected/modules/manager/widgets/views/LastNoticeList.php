<?php
/**
 * @var LastNoticeWidget $this
 * @var Calendar[] $notices
 */
if(count($notices)>0) {
	foreach($notices as $notice) {
		$sShortMessage=CHtml::tag('span',array(),LangUtils::truncate(strip_tags($notice->message)));
		$sDoneLink='';
		if($notice->status==1) {
			$sDoneLink=CHtml::link(CHtml::image('/images/icons/tick.png','',array('title'=>'Подтвердить прочтение')),'#noticesReady_'.$notice->id,array('class'=>'noticeReady'));
		}
		$sItem=$this->widget('manager.widgets.NoticeActionMenuWidget',array('message'=>$notice->message),true).$sShortMessage
			.$this->widget('manager.widgets.NoticeDelayMenuWidget',array('notice'=>$notice),true).$sDoneLink;
		if($notice->status==1) {
			echo CHtml::tag('div',array('class'=>'item new'),$sItem);
		} else {
			echo CHtml::tag('div',array('class'=>'item'),$sItem);
		}
	}
}