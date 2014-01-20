<?php
/**
 * @var People $user
 */
echo CHtml::openTag('ul',array('class'=>'userContextMenu userContextMenu'.$user->id));
echo CHtml::openTag('li',array('class'=>'createPackage'));
echo CHtml::tag('a',array('href'=>'#package_0_'.$user->id,'title'=>'Создать новый заказ для данного клиента','rel'=>'auto'),'Создать заказ');
echo CHtml::closeTag('li');
if($user->partner_data) {
	echo CHtml::openTag('li',array('class'=>'partnerCard'));
	echo CHtml::tag('a',array('href'=>'#partnerCard_'.$user->id,'title'=>'Отредактировать данные партнёра','rel'=>'auto'),'Карточка партнёра');
	echo CHtml::closeTag('li');
} else {
	echo CHtml::openTag('li',array('class'=>'clientCard'));
	echo CHtml::tag('a',array('href'=>'#clientCard_'.$user->id,'title'=>'Просмотреть заказы клиента','rel'=>'auto'),'Карточка клиента');
	echo CHtml::closeTag('li');
}
echo CHtml::openTag('li',array('class'=>'jurPersonCard'));
echo CHtml::tag('a',array('href'=>'#jurPersonCard_'.$user->id,'title'=>'Отредактировать данные юридического лица','rel'=>'auto'),'Реквизиты');
echo CHtml::closeTag('li');
echo CHtml::openTag('li',array('class'=>'loggerForm'));
echo CHtml::tag('a',array('href'=>'#loggerForm_'.$user->id,'title'=>'Просмотреть журнал','rel'=>'auto'),'Журнал');
echo CHtml::closeTag('li');
echo CHtml::openTag('li',array('class'=>'contacts'));
echo CHtml::tag('a',array('href'=>'#peopleContact_'.$user->id,'title'=>'Список контактных лиц','rel'=>'auto'),'Контактные лица');
echo CHtml::closeTag('li');
echo CHtml::openTag('li',array('class'=>'documents separator'));
echo CHtml::tag('a',array('target'=>'_blank','href'=>'/manager/docs3/'.$user->id,'title'=>'Посмотреть все документы пользователя'),'Просмотреть документы');
echo CHtml::closeTag('li');
echo CHtml::openTag('li',array('class'=>'newpassword separator'));
echo CHtml::tag('a',array('href'=>'#peoplePassword_'.$user->id,'rel'=>'auto','title'=>'Сгенерировать новый пароль и уведомить пользователя'),'Отправить новый пароль');
echo CHtml::closeTag('li');
echo CHtml::closeTag('ul');