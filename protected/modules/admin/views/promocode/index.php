<h1>
	Список промокодов
</h1>
<?php $this->widget('zii.widgets.grid.CGridView', array(
	'id'=>'infocodes-grid',
	'dataProvider'=> $obModel->search(),
	'filter'=>$obModel,
	'columns'=>array(
		array(
			'name'=>'id',
		),
		array(
			'name'=>'value',
		),
		array(
			'name'=>'type',
			'value'=>'Yii::t("infocode",$data["type"])',
			'filter'=>array('partner'=>'Партнерский','other'=>'Другой'),
		),
		array(
			'name'=>'created',
			'value'=>'Yii::app()->dateFormatter->formatDateTime($data["created"],"medium",null)',
		),
		array(
			'name'=>'descr',
		),
		array(
			'type'=>'html',
			'value'=>'CHtml::link(
				CHtml::image(
					"/images/icons/comment_edit.png",
					"",
					array("title"=>"Редактировать описание")
				),
				Yii::app()->createUrl("admin/promocode/edit",array("id"=>$data["id"]))
			)',

		),
	),
	'selectableRows' => 0,
	'emptyText' => Yii::t('sup','Нет таких инфокодов.'),
	'template' => '{pager}{summary}{items}{pager}{summary}'
)); ?>