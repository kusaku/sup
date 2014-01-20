<?php
/**
 * @var NoticeActionMenuWidget $this
 * @var array $list
 */
echo CHtml::link(CHtml::image('/images/icons/cog.png','',array('title'=>'Все действия уведомления')),'#notices',array('class'=>'noticesAction'));
echo CHtml::openTag('ul',array('class'=>'noticeContextMenu'));
foreach($list as $sLink) {
	echo CHtml::tag('li',array('class'=>'item'),$sLink);
}
echo CHtml::closeTag('ul');