<?php
/**
 * @var Package $package
 * @var CabinetController $this
 * @var PackageWorkflowStep $step
 * @var JurPersonReference $obJurPerson
 */
$obJurPerson=null;
$arRA=array('class'=>'formRow');
if($package->client->jur_person_id>0) {
	$obJurPerson=$package->client->jur_person;
}
echo CHtml::openTag('div',array('class'=>'tabscontainer modal'));
echo CHtml::openTag('ul');
echo CHtml::tag('li',array(),CHtml::tag('a',array('href'=>'#tabs-status'),'Форма заполнения данных юридического лица'));
if($obJurPerson) {
	echo CHtml::tag('li',array(),CHtml::tag('a',array('href'=>'#tabs-jur-person'),'Реквизиты юридического лица клиента'));
}
echo CHtml::closeTag('ul');
echo CHtml::openTag('div',array('id'=>'tabs-status','class'=>'formBody','style'=>'padding:10px;'));
$obWorkflow=$package->initWorkflow();
$data=$obWorkflow->getData($step->primaryKey);
if(is_array($data)) {
	echo CHtml::tag('p',array(),'Клиент указал в форме следующие данные:');
	$obForm=new JurPersonReferenceForm('safe');
	$obForm->attributes=$data;
	echo CHtml::openTag('div',array('class'=>'formRows'));
	foreach($obForm->getActiveAttributeNames() as $sField) {
		if($sField=='type') {
			$arTypes=JurPersonReference::getTypeList();
			if(isset($arTypes[$obForm->$sField])) {
				echo CHtml::tag('div',$arRA,CHtml::activeLabel($obForm,$sField).CHtml::tag('span',array(),$arTypes[$obForm->$sField]));
			} else {
				echo CHtml::tag('div',$arRA,CHtml::activeLabel($obForm,$sField).CHtml::tag('span',array(),''));
			}
		} elseif($sField=='director_source') {
			$arSources=JurPersonReference::getSourceList();
			if(isset($arSources[$obForm->$sField])) {
				echo CHtml::tag('div',$arRA,CHtml::activeLabel($obForm,$sField).CHtml::tag('span',array(),$arSources[$obForm->$sField]));
			} else {
				echo CHtml::tag('div',$arRA,CHtml::activeLabel($obForm,$sField).CHtml::tag('span',array(),''));
			}
		} else {
			echo CHtml::tag('div',$arRA,CHtml::activeLabel($obForm,$sField).CHtml::tag('span',array(),$obForm->$sField));
		}
	}
	echo CHtml::closeTag('div');
} else {
	echo "Клиент ещё не заполнял реквизиты юридического лица для данного заказа.";
}
echo CHtml::closeTag('div');
if($obJurPerson) {
	echo CHtml::openTag('div',array('id'=>'tabs-jur-person','class'=>'formBody','style'=>'padding:10px;'));
	echo CHtml::openTag('div',array('class'=>'formRows'));
	echo CHtml::tag('div',array('class'=>'formRow fullRow'),CHtml::tag('a',array('href'=>'#jurPersonCard_'.$package->client_id),'Редактировать реквизиты'));
	foreach($obJurPerson->getActiveAttributeNames() as $sField) {
		if($sField=='type') {
			$arTypes=JurPersonReference::getTypeList();
			echo CHtml::tag('div',$arRA,CHtml::activeLabel($obJurPerson,$sField).CHtml::tag('span',array(),$arTypes[$obJurPerson->$sField]));
		} elseif($sField=='director_source') {
			$arSources=JurPersonReference::getSourceList();
			echo CHtml::tag('div',$arRA,CHtml::activeLabel($obJurPerson,$sField).CHtml::tag('span',array(),$arSources[$obJurPerson->$sField]));
		} else {
			echo CHtml::tag('div',$arRA,CHtml::activeLabel($obJurPerson,$sField).CHtml::tag('span',array(),$obJurPerson->$sField));
		}
	}
	echo CHtml::closeTag('div');
	echo CHtml::closeTag('div');
}
echo CHtml::closeTag('div');