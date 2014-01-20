<?php
/**
 * @var People $client
 * @var PeopleController $this
 * @var PeopleContacts $model
 */
echo CHtml::openTag('div',array('class'=>'formWindow','style'=>'width:700px;'));
echo CHtml::tag('div',array('class'=>'formHead'),'Контактные данные клиента @'.$client->id.': '.$client->mail);
echo CHtml::openTag('div',array('class'=>'formBody contactsBody','style'=>'padding:0 0 7px; 0'));
if(count($client->contacts)>0) {
	echo CHtml::openTag('div',array('class'=>'leftColumn'));
	echo CHtml::openTag('div',array('class'=>'scrollPanel','style'=>'height:226px;')).CHtml::openTag('ul',array('class'=>'contactsList'));
	foreach($client->contacts as $obContact) {
		if($obContact->id==$model->id) {
			echo CHtml::tag('li',array('class'=>'contactItem active'),CHtml::tag('a',array('href'=>'#peopleContact_'.$client->id.'_'.$obContact->id,'class'=>'left'),LangUtils::escape(LangUtils::truncate($obContact->getTitle(),30))));
		} else {
			echo CHtml::tag('li',array('class'=>'contactItem'),CHtml::tag('a',array('href'=>'#peopleContact_'.$client->id.'_'.$obContact->id,'class'=>'left'),LangUtils::escape(LangUtils::truncate($obContact->getTitle(),30))));
		}
	}
	echo CHtml::closeTag('ul').CHtml::closeTag('div');
	echo CHtml::openTag('div',array('class'=>'buttons'));
	echo CHtml::tag('a',array('class'=>"buttonNew buttonOrange"),'Добавить');
	echo CHtml::closeTag('div');
	echo CHtml::closeTag('div');
	echo CHtml::openTag('div',array('class'=>'rightColumn'));
	$this->renderPartial('contactsForm',array('model'=>$model));
	echo CHtml::closeTag('div');
} else {
	$this->renderPartial('contactsForm',array('model'=>$model));
}
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');
