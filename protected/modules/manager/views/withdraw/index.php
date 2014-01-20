<h1>
	Список запросов на вывод средств
</h1>

<div id="flashes">
	<?php $this->widget('Message')?>
</div>

<?php echo CHtml::errorSummary($obWithdrawForm); ?>



<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'infocodes-grid',
	'dataProvider'=> new CActiveDataProvider('Withdraw',array(
		'sort'=>array('defaultOrder' => array(
			'id' => CSort::SORT_DESC,
		))
    )),
	'columns'=>array(
		array(
			'name'=>'id',
		),
		array(
			'name'=>'id_partner',
		),
		array(
			'name'=>'summ',
			'value'=>'Yii::app()->numberFormatter->formatCurrency($data["summ"],"RUR")',
		),
		array(
			'name'=>'ts_add',
			'value'=>'Yii::app()->dateFormatter->formatDateTime($data["ts_add"])',
		),
		array(
			'name'=>'ts_process',
			'value'=>'$data["ts_process"] ? Yii::app()->dateFormatter->formatDateTime($data["ts_process"]) : ""',
		),
		array(
			'name'=>'status',
			'value'=>'Yii::t("withdraw",$data["status"])',
		),
		array(
			'type'=>'raw',
			'value'=>'$data["status"] == Withdraw::STAT_REQUESTED ?
				CHtml::beginForm(Yii::app()->createUrl("manager/withdraw"))
				. CHtml::hiddenField("WithdrawForm[id]", $data["id"])
				. CHtml::imageButton("/images/icons/accept.png",array(
					"name"=>"WithdrawForm[".Withdraw::STAT_APPROVED."]",
					"title"=>"Подтвердить",
				))
				. " "
				. CHtml::imageButton("/images/icons/cross.png",array(
					"name"=>"WithdrawForm[".Withdraw::STAT_REJECTED."]",
					"title"=>"Отклонить",
				))
				. CHtml::endForm()
				: ""',
		),
	),
	'selectableRows' => 0,
	'emptyText' => Yii::t('sup','There are no withdraws.'),
	'template' => '{items}{pager}'
));
?>