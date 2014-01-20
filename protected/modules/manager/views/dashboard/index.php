<?php
/**
 * @var DashboardController $this
 * @var People $people
 * @var string $period
 * @var string $date
 * @var string $date_from
 * @var array $data
 */
if(!is_null($people)) {
	$people_id=$people->id;
} else {
	$people_id=0;
}
echo CHtml::script('dashboardReady()');
echo CHtml::openTag('div',array('class'=>'dashTools'));
echo CHtml::form($this->getRoute(),'get');
echo CHtml::openTag('fieldset');
echo CHtml::label('Менеджер','people_id');
$this->widget('manager.widgets.UserselectWidget', array('group_id'=>4,'selected'=>$people_id));
echo CHtml::closeTag('fieldset').CHtml::openTag('fieldset');
echo CHtml::label('с начала:','period');
echo CHtml::link('года','#',array('rel'=>'year','class'=>$period=='year'?'selected':''));
echo CHtml::link('месяца','#',array('rel'=>'month','class'=>$period=='month'?'selected':''));
echo CHtml::link('недели','#',array('rel'=>'week','class'=>$period=='week'?'selected':''));
echo CHtml::link('дня','#',array('rel'=>'day','class'=>$period=='day'?'selected':''));
if(!in_array($period, array('year','month','week','day'))) {
	echo CHtml::link('выбрать','#',array('rel'=>'choose','class'=>'selected'));
	echo CHtml::textField('period',$period,array('class'=>'datapicker'));
} else {
	echo CHtml::link('выбрать','#',array('rel'=>'choose'));
	echo CHtml::textField('period',$period,array('class'=>'datapicker hidden'));
}
echo CHtml::closeTag('fieldset').CHtml::openTag('fieldset');
echo CHtml::label('к дате:','date');
echo CHtml::textField('date',$date,array('class'=>'datapicker'));
echo CHtml::closeTag('fieldset');
echo CHtml::submitButton('Вывести',array('class'=>"orangeButton",'name'=>"showme"));
echo CHtml::endForm();
echo CHtml::closeTag('div');
echo CHtml::openTag('div',array('class'=>'reportLabel')).CHtml::openTag('p');
echo 'Статистика для ';
if(is_null($people)) {
	echo CHtml::tag('b',array(),'всех менеджеров');
} else {
	echo CHtml::tag('b',array(),$people->fio);
}
echo ' с ';
switch ($period) {
	case 'year':
		echo CHtml::tag('b',array(),'начала года ('.date('d.m.Y H:i',strtotime($date_from)).')');
	break;
	case 'month':
		echo CHtml::tag('b',array(),'начала месяца ('.date('d.m.Y H:i',strtotime($date_from)).')');
	break;
	case 'week':
		echo CHtml::tag('b',array(),'начала недели ('.date('d.m.Y H:i',strtotime($date_from)).')');
	break;
	case 'day':
		echo CHtml::tag('b',array(),'начала дня ('.date('d.m.Y H:i',strtotime($date_from)).')');
	break;
	default:
		echo CHtml::tag('b',array(),date('d.m.Y H:i',strtotime($date_from)));
	break;
}
echo ' по '.date('d.m.Y H:i',strtotime($date));
echo CHtml::closeTag('p').CHtml::closeTag('div');

//Отчёт по созданным сайтам
echo CHtml::openTag('div',array('class'=>'reportBlock sites'));
echo CHtml::openTag('div',array('class'=>'tabButtons'));
echo CHtml::tag('h4',array(),'Заказы:');
echo CHtml::openTag('ul',array('class'=>'tabs'));
echo CHtml::tag('li',array('rel'=>'tabnew','class'=>'active'),CHtml::link('Новые','#'));
echo CHtml::tag('li',array('rel'=>'tabpaying'),CHtml::link('Платёжка','#'));
echo CHtml::tag('li',array('rel'=>'tabpaid'),CHtml::link('Оплаты','#'));
echo CHtml::closeTag('ul').CHtml::closeTag('div');
echo CHtml::openTag('ul',array('class'=>'tabsContent'));

$arProducts=array(
	'vizitka'=>array(
		'id'=>4,
		'title'=>'Сайт-визитка',
		'graph'=>1,
	),
	'official'=>array(
		'id'=>5,
		'title'=>'Официальный сайт',
		'graph'=>1,
	),
	'corporate'=>array(
		'id'=>33,
		'title'=>'Корпоративный сайт',
		'graph'=>1,
	),
	'smallbiz'=>array(
		'id'=>126,
		'title'=>'Пакет "Малый Бизнес"',
		'graph'=>1,
	),
	'premium'=>array(
		'id'=>143,
		'title'=>'Премиум-сайт',
		'graph'=>0,
	),
	'teacher'=>array(
		'id'=>144,
		'title'=>'Сайт-учителя',
		'graph'=>0,
	),
	'shop'=>array(
		'id'=>'shop',
		'title'=>'Магазины (все)',
		'graph'=>1,
	),
	'shop-base'=>array(
		'id'=>148,
		'title'=>'Пакет "Быстрый старт"',
		'graph'=>0,
	),
	'shop-optimal'=>array(
		'id'=>149,
		'title'=>'Пакет "Оптимальный"',
		'graph'=>0,
	),
	'shop-extended'=>array(
		'id'=>150,
		'title'=>'Пакет "Расширенный"',
		'graph'=>0,
	),
	'shop-old'=>array(
		'id'=>6,
		'title'=>'Старый магазин',
		'graph'=>0,
	),
	'extras'=>array(
		'id'=>0,
		'title'=>'Другие услуги',
		'graph'=>0,
	),
);

foreach($data['products'] as $key=>$rec) {
	echo CHtml::openTag('li',array('id'=>"tab".$key, 'style'=>"display:none;"));
	echo CHtml::openTag('div',array('class'=>'diagrams')).CHtml::openTag('ul',array('class'=>'graph'));
	foreach($arProducts as $type=>$arDat) {
		if($arDat['graph']==0) continue;
		echo CHtml::openTag('li',array('class'=>$type));
		echo CHtml::tag('span',array('style'=>'height:'.round($rec[$arDat['id']]['total']/($rec['total']+1)*140).'px;'),'&nbsp;');
		echo CHtml::tag('em',array('class'=>'extra','style'=>'height:'.round($rec[$arDat['id']]['summ']/($rec['summ']+1)*140).'px;'),'&nbsp;');
		echo CHtml::closeTag('li');
	}

	echo CHtml::closeTag('ul').CHtml::closeTag('div');
	echo CHtml::openTag('div',array('class'=>'diagramLegend')).CHtml::openTag('ul',array('class'=>'information'));
	foreach($arProducts as $type=>$arDat) {
		echo CHtml::openTag('li',array('class'=>$type));
		echo CHtml::tag('p',array('class'=>'title'),$arDat['title'].': '.CHtml::tag('span',array(),$rec[$arDat['id']]['total']));
		//echo CHtml::tag('p',array('class'=>'escort'),'Сопр: '.CHtml::tag('span',array(),13));
		echo CHtml::tag('p',array('class'=>'summ_total'),'Всего: '.CHtml::tag('span',array(),number_format($rec[$arDat['id']]['summ_total'],0,',',' '))
			.' - '.CHtml::tag('span',array(),number_format($rec[$arDat['id']]['summ_excluded'],0,',',' '))
			.' = '.CHtml::tag('span',array(),number_format($rec[$arDat['id']]['summ'],0,',',' ').' руб.'));
		echo CHtml::tag('div',array('style'=>'clear:both;'));
		echo CHtml::closeTag('li');
	}
	//
	echo CHtml::openTag('li',array('class'=>'total'));
	echo CHtml::tag('p',array('class'=>'title'),'Итого: '.CHtml::tag('span',array(),$rec['total']));
	//echo CHtml::tag('p',array('class'=>'escort'),'Сопр: '.CHtml::tag('span',array(),13));
	echo CHtml::tag('p',array('class'=>'summ_total'),'Сумма: '.CHtml::tag('span',array(),number_format($rec['summ_total'],0,',',' '))
			.' - '.CHtml::tag('span',array(),number_format($rec['summ_excluded'],0,',',' '))
			.' = '.CHtml::tag('span',array(),number_format($rec['summ'],0,',',' ').' руб.'));
	echo CHtml::closeTag('li');
	echo CHtml::closeTag('ul').CHtml::closeTag('div');
	echo CHtml::closeTag('li');
}
echo CHtml::closeTag('ul').CHtml::closeTag('div');

echo CHtml::openTag('div',array('class'=>'reportBlock sites')).CHtml::openTag('table',array('class'=>'tablesorter'));
echo CHtml::openTag('thead').CHtml::openTag('tr');
echo CHtml::tag('th',array(),CHtml::tag('span',array(),'Менеджер'));
echo CHtml::tag('th',array(),CHtml::tag('span',array(),'Взятые'));
echo CHtml::tag('th',array('class'=>"{sorter: 'myprice'}"),CHtml::tag('span',array(),'На сумму'));
echo CHtml::tag('th',array(),CHtml::tag('span',array(),'Есть платёжка'));
echo CHtml::tag('th',array('class'=>"{sorter: 'myprice'}"),CHtml::tag('span',array(),'На сумму'));
echo CHtml::tag('th',array(),CHtml::tag('span',array(),'Оплачено'));
echo CHtml::tag('th',array('class'=>"{sorter: 'myprice'}"),CHtml::tag('span',array(),'На сумму'));
echo CHtml::tag('th',array(),CHtml::tag('span',array(),'В работе'));
echo CHtml::tag('th',array('class'=>"{sorter: 'myprice'}"),CHtml::tag('span',array(),'На сумму'));
echo CHtml::closeTag('tr').CHtml::closeTag('thead');
echo CHtml::openTag('tbody');
foreach ($data['managers'] as $id=>$manager) {
	echo CHtml::openTag('tr');
	if(!is_null($manager['people'])) {
		echo CHtml::tag('td',array(),$manager['people']->fio);
	} else {
		echo CHtml::tag('td',array(),'Менеджер #'.$id.' не найден');
	}
	echo CHtml::tag('td',array(),CHtml::tag('span',array(),$manager['new']['total']));
	echo CHtml::tag('td',array(),CHtml::tag('span',array(),number_format($manager['new']['summ'],0,',',' ')));
	echo CHtml::tag('td',array(),CHtml::tag('span',array(),$manager['paying']['total']));
	echo CHtml::tag('td',array(),CHtml::tag('span',array(),number_format($manager['paying']['summ'],0,',',' ')));
	echo CHtml::tag('td',array(),CHtml::tag('span',array(),$manager['paid']['total']));
	echo CHtml::tag('td',array(),CHtml::tag('span',array(),number_format($manager['paid']['summ'],0,',',' ')));
	echo CHtml::tag('td',array(),CHtml::tag('span',array(),0));
	echo CHtml::tag('td',array(),CHtml::tag('span',array(),0));
	echo CHtml::closeTag('tr');
}
echo CHtml::closeTag('tbody');
echo CHtml::openTag('tfoot');
echo CHtml::openTag('tr');
echo CHtml::tag('td',array(),'Итого');
echo CHtml::tag('td',array(),CHtml::tag('span',array(),$data['summary']['new']['total']));
echo CHtml::tag('td',array(),CHtml::tag('span',array(),number_format($data['summary']['new']['summ'],0,',',' ')));
echo CHtml::tag('td',array(),CHtml::tag('span',array(),$data['summary']['paying']['total']));
echo CHtml::tag('td',array(),CHtml::tag('span',array(),number_format($data['summary']['paying']['summ'],0,',',' ')));
echo CHtml::tag('td',array(),CHtml::tag('span',array(),$data['summary']['paid']['total']));
echo CHtml::tag('td',array(),CHtml::tag('span',array(),number_format($data['summary']['paid']['summ'],0,',',' ')));
echo CHtml::tag('td',array(),CHtml::tag('span',array(),0));
echo CHtml::tag('td',array(),CHtml::tag('span',array(),0));
echo CHtml::closeTag('tr');
echo CHtml::closeTag('tfoot');
echo CHtml::closeTag('table').CHtml::closeTag('div');
