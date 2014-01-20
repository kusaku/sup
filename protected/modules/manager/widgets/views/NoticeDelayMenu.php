<?php
/**
 * @var NoticeDelayMenuWidget $this
 * @var Calendar $obNotice
 */
echo CHtml::link(CHtml::image('/images/icons/clock.png','',array('title'=>'Напомнить позднее')),'#notices',array('class'=>'noticesDelay'));
echo CHtml::openTag('ul',array('class'=>'noticeContextMenu'));
echo CHtml::tag('li',array('class'=>'noticeDelay15minutes'),CHtml::link('Отложить на 15 минут','#noticeDelay_'.$obNotice->id.'_15'));
echo CHtml::tag('li',array('class'=>'noticeDelay30minutes'),CHtml::link('Отложить на 30 минут','#noticeDelay_'.$obNotice->id.'_30'));
echo CHtml::tag('li',array('class'=>'noticeDelay60minutes'),CHtml::link('Отложить на 1 час','#noticeDelay_'.$obNotice->id.'_60'));
echo CHtml::tag('li',array('class'=>'noticeDelay120minutes'),CHtml::link('Отложить на 2 часа','#noticeDelay_'.$obNotice->id.'_120'));
echo CHtml::tag('li',array('class'=>'noticeDelay240minutes'),CHtml::link('Отложить на 4 часа','#noticeDelay_'.$obNotice->id.'_240'));
echo CHtml::tag('li',array('class'=>'noticeDelay360minutes'),CHtml::link('Отложить на 6 часов','#noticeDelay_'.$obNotice->id.'_360'));
echo CHtml::tag('li',array('class'=>'noticeDelay1440minutes'),CHtml::link('Отложить на 1 день','#noticeDelay_'.$obNotice->id.'_1440'));
echo CHtml::tag('li',array('class'=>'noticeDelay2880minutes'),CHtml::link('Отложить на 2 дня','#noticeDelay_'.$obNotice->id.'_2880'));
echo CHtml::tag('li',array('class'=>'noticeDelay7200minutes'),CHtml::link('Отложить на 5 дней','#noticeDelay_'.$obNotice->id.'_7200'));
echo CHtml::tag('li',array('class'=>'noticeDelay10080minutes'),CHtml::link('Отложить на неделю','#noticeDelay_'.$obNotice->id.'_10080'));
echo CHtml::closeTag('ul');