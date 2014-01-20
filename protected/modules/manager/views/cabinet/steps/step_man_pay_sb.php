<?php
/**
 * @var Package $package
 * @var CabinetController $this
 * @var PackageWorkflowStep $step
 */
?>
<div class="tabscontainer modal">
	<ul>
		<li>
			<a href="#tabs-status">Печать квитанции сбербанка</a>
		</li>
	</ul>
	<div id="tabs-status" style="padding:10px;">
		<p>
			Клиенту подготовлена квитанция на сумму: <b><?php echo $package->summ;?> руб.</b>.
		</p>
		<div class="buttons">
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/receipt',array('id'=>$package->id))?>" title="Посмотреть квитанцию на оплату" target="_blank">
					<img src="/images/docs2/buttons/receipt.png" alt="" />Квитанция на оплату</a>
			</div>
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/receipt',array('id'=>$package->id,'format'=>'pdf'))?>" title="Открыть в браузере в формате PDF" target="_blank">
					<img src="/images/docs2/buttons/file_extension_pdf.png" alt="" />Посмотреть в PDF</a>
			</div>
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/receipt',array('id'=>$package->id,'format'=>'pdf','download'=>1))?>" title="Скачать в формате PDF" target="_blank">
					<img src="/images/docs2/buttons/file_extension_pdf_save.png" alt="" />Скачать в PDF</a>
			</div>
		</div>
	</div>
</div>