<?php
/**
 * @var People $client
 */
echo CHtml::openTag('div',array('class'=>'formWindow','style'=>'width:700px;'));
echo CHtml::tag('div',array('class'=>'formHead'),'Журнал клиента @'.$client->id.': '.$client->mail);
echo CHtml::openTag('div',array('class'=>'formBody','style'=>'padding:0 0 10px;'));
if (count($records = Logger::get($client->id))) {
	echo CHtml::openTag('div',array('class'=>'loggerBody'));
	$arDates=array();
	foreach($records as $record) {
		$date=date('d.m.Y',strtotime($record->dt));
		if(isset($arDates[$date])) {
			$arDates[$date]++;
		} else {
			$arDates[$date]=1;
		}
	}

	echo CHtml::openTag('div',array('class'=>'leftColumn')).CHtml::openTag('ul',array('class'=>'loggerDates'));
	foreach($arDates as $sDate=>$iCount) {
		echo CHtml::tag('li',array('class'=>'dateItem'),CHtml::tag('a',array('href'=>'#date_'.str_replace('.','_',$sDate),'class'=>'left'),$sDate).CHtml::tag('a',array('href'=>'#date_'.$sDate,'class'=>'right'),$iCount));
	}
	echo CHtml::closeTag('ul').CHtml::closeTag('div');

	echo CHtml::openTag('div',array('class'=>'rightColumn')).CHtml::openTag('ul',array('class'=>'loggerTexts'));
	$sDate='';
	foreach($records as $record) {
		$iTime=strtotime($record->dt);
		if($sDate!=date('d.m.Y',$iTime)) {
			$sDate=date('d.m.Y',$iTime);
			echo CHtml::tag('li',array('class'=>'date'),CHtml::tag('a',array('id'=>'date_'.str_replace('.','_',$sDate)),$sDate));
		}
		echo CHtml::openTag('li',array('class'=>'item'));
		echo CHtml::tag('span',array('class'=>'time'),date('H:i',$iTime));
		echo CHtml::tag('a',array('class'=>'author','href'=>'#people_'.$record->manager->id),$record->manager->fio);
		echo CHtml::tag('p',array('class'=>'message'),$record->getFormatedText());
		echo CHtml::closeTag('li');
	}
	echo CHtml::closeTag('ul').CHtml::closeTag('div');


	echo CHtml::closeTag('div');
}
echo CHtml::openTag('form',array('action'=>'/manager/logger/put','method'=>'post','id'=>'megaform'));
echo CHtml::textArea('info');
echo CHtml::hiddenField('client_id',$client->id);
echo CHtml::closeTag('form');
echo CHtml::openTag('div',array('class'=>'buttons'));
echo CHtml::tag('a',array('class'=>"buttonSave buttonOrange"),'Добавить запись');
echo CHtml::tag('a',array('class'=>"buttonCancel buttonGray"),'Закрыть');
echo CHtml::tag('span',array('class'=>'save-result'),'Для отправки сообщения воспользуйтесь Ctrl+Enter');
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');
