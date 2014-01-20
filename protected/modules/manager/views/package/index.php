<?php
/**
 * @var PackageController $this
 * @var integer $client_id
 * @var People $client
 */
if ($client = People::model()->with('infocode','partner_data')->findByPk($client_id)) {
	echo CHtml::openTag('ul',array('id'=>'ul'.$client->id,'class'=>'columnsBody'));
	//$condition = Yii::app()->user->checkAccess('admin') ? null : 'packages.manager_id = '.Yii::app()->user->id;
	/*if (Yii::app()->user->checkAccess('topmanager')) {
	 $ c*ondition = 'packages.payment_id IN (18, 20)';
} else {*/
	$condition = NULL;
	//}
	$packages = $client->packages(array(
		'condition'=>$condition,
		'order'=>'dt_change DESC',
		'with'=>array('infocode','pay_status','manager','site'),
	));
	$arClientStyle=array();
	if($client->partner_data) {
		$arClientStyle['class']='partner';
	}
	echo CHtml::openTag('li',$arClientStyle);
	if (count($packages) > 1) {
		echo CHtml::link('','',array('class'=>'lessClick'));
	}
	echo CHtml::openTag('div',array('class'=>'clientInfo')).CHtml::openTag('div',array('class'=>'client-info-inner'));
	$sClientTooltip='<strong>Имя</strong>: '. $client->fio
		.'<br /><strong>Компания</strong>: '. $client->firm
		.'<br /><strong>E-mail</strong>: <a href="mailto:'.$client->mail.'">&lt;'. $client->mail.'&gt;</a>'
		.'<br /><strong>Телефон</strong>: '.$client->phone;
	echo CHtml::link($client->mail, '#people_'.$client->id, array('class' => 'name-text','data-tooltip' =>$sClientTooltip));
	if($client->infocode) {
		$sTooltip='<strong>Промокод клиента</strong>: '. $client->infocode->value.'<br /><strong>Описание</strong>: '. $client->infocode->descr;
		echo CHtml::tag('span',array('class'=>'infocode','data-tooltip'=>$sTooltip),$client->infocode->value);
	} elseif($sPromocodes=$client->proxyPromo()) {
		echo CHtml::tag('span',array('class'=>'infocode'),$sPromocodes);
	}
	echo CHtml::closeTag('div');
	echo CHtml::link('Новый заказ','#package_0_'.$client->id,array('title'=>'Создать новый заказ'));
	if($client->partner_data) {
		echo CHtml::link('Карточка партнёра','#partnerCard_'.$client->id,array('title'=>'Отредактировать данные партнёра'));
	} else {
		echo CHtml::link('Карточка клиента','#clientCard_'.$client->id,array('title'=>'Просмотреть заказы клиента'));
	}
	echo CHtml::link('Журнал','#loggerForm_'.$client->id,array('title'=>'Просмотреть журнал'));
	//Меню пользователя
	$this->widget('manager.widgets.UserMenuWidget',array('user'=>$client));
	echo CHtml::closeTag('div');
	//Подготовка к выводу списка заказов
	$class = '';
	$style = '';
	$htmlOpt = array(
		'class'=>'more type popover-hover',
		'style'=>'float:left;text-align:left;max-width: 131px;',
	);
	$arColors=array(
		0=>'red',1=>'red',10=>'grey',15=>'blue',40=>'lightgreen',50=>'lightgreen',60=>'green',70=>'green'
	);
	$arPaymentColors=array(
		17=>'grey',20=>'lightgreen',30=>'green'
	);
	if(is_array($packages) && count($packages)>0) {
		foreach ($packages as $package) {
			$color = 'orange';
			if(isset($arColors[$package->status_id])) {
				$color=$arColors[$package->status_id];
			} elseif(isset($arPaymentColors[$package->payment_id])) {
				$color=$arPaymentColors[$package->payment_id];
			}
			echo CHtml::openTag('div',array('class'=>'projectBox '.$color.' '.($package->status_id<2?'':$class)));
			//Первая колонка (информация о заказе)
			echo CHtml::openTag('div',array('class'=>'projectType'));
			if($package->infocode){
				$htmlOpt['data-tooltip']='<strong>Промокод заказа</strong>: ' . $package->infocode->value.'<br /><strong>Описание</strong>: '.$package->infocode->descr;
			}
			echo CHtml::link('#'.$package->id.'<br />'. LangUtils::truncate($package->name,14) .'&nbsp;','#package_'.$package->id.'_'. $client->id,$htmlOpt);
			echo CHtml::openTag('strong');
			echo number_format($package->summ, 0, ',', ' ').' руб.<br/>';
			if($package->pay_status) {
				echo CHtml::link($package->pay_status->name,'#payments_'.$package->id);
			}
			echo CHtml::closeTag('strong');
			if ($package->manager_id != Yii::app()->user->id) {
				echo CHtml::tag('span',array(),'('.($package->manager?$package->manager->fio:'не присвоен менеджеру').')');
			}
			echo CHtml::closeTag('div');
			//Вторая колонка (состояние заказа)
			$this->widget('manager.widgets.PackageStatusInfoWidget',array('package'=>$package,'client'=>$client));
			//Третя колонка (информация о домене и даты)
			echo CHtml::openTag('div',array('class'=>'projectDomain'));
			if (isset($package->site)) {
				echo CHtml::link($package->site->url,'#site_'.$package->site_id);
			}
			echo CHtml::openTag('p');
			echo CHtml::tag('span',array('title'=>'Дата создания заказа'),'С: '.date('d.m H:i', strtotime($package->dt_beg))).CHtml::tag('br');
			echo CHtml::tag('span',array('title'=>'Дата изменения заказа'),'И: '.date('d.m H:i', strtotime($package->dt_change)));
			echo CHtml::closeTag('p');
			echo CHtml::closeTag('div');
			echo CHtml::tag('div',array('class'=>'projectDate act'),$package->descr);
			echo CHtml::closeTag('div');
			$class = 'forhide';
		}
	} else {
		echo CHtml::tag('div',array('class'=>'projectBox'),CHtml::tag('div',array('class'=>'projectType'),'').
			CHtml::tag('div',array('class'=>'projectState'),'').
			CHtml::tag('div',array('class'=>'projectDomain'),'').
			CHtml::tag('div',array('class'=>'projectDate act'),''));
	}
	echo CHtml::closeTag('li').CHtml::closeTag('ul');
} else {
	echo CHtml::tag('ul',array('class'=>'columnsBody'),CHtml::tag('li',array(),CHtml::tag('strong',array(),'Клиент @'.$client_id.' не существует, и данных по нему нет.')));
}
