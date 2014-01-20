<?php
/**
 * @var CActiveDataProvider $list
 * @var PaymentController $this
 */
$this->widget('zii.widgets.grid.CGridView',array('dataProvider'=>$list,'columns'=>array(
	'id'=>array('class'=>'CLinkColumn','header'=>'ID','labelExpression'=>'$data->id','urlExpression'=>'"#payment_".$data->id."_".$data->package_id'),
	'package_id'=>array('class'=>'CLinkColumn','header'=>'ID заказа','labelExpression'=>'"#".$data->package_id','urlExpression'=>'"#package_".$data->package_id."_".($data->package?$data->package->client_id:0)'),
	'name'=>array('header'=>'Наименование платежа','name'=>'name'),
	'summ'=>array('header'=>'Сумма','name'=>'amount'),
	'date'=>array('header'=>'Дата добавления','name'=>'dt'),
	'date_pay'=>array('header'=>'Дата поступ. на Р/С','name'=>'dt_pay'),
	'description'=>array('header'=>'Описание','name'=>'description'),
	'status'=>array('header'=>'Состояние платежа','value'=>'$data->ptype_id==1?"оплата".($data->dt_pay!=NULL?" (".date("d.m.Y H:i",strtotime($data->dt_pay)).")":""):"платёжка"'),
	'links'=>array('class'=>'CLinkColumn','header'=>'','label'=>'Все платежи','urlExpression'=>'"#payments_".$data->package_id'),
),'template'=>'{items}'));