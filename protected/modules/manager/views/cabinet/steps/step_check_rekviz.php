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
			<a href="#tabs-status">Печать документов юридического лица</a>
		</li>
	</ul>
	<div id="tabs-status" style="padding:10px;">
		<div class="buttons">
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/contract',array('id'=>$package->id))?>" title="Посмотреть актуальную версию договора" target="_blank">
					<img src="/images/docs2/buttons/contractFull.png" alt="" />Договор</a>
			</div>
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/contract',array('id'=>$package->id,'format'=>'pdf'))?>" title="Открыть в браузере в формате PDF" target="_blank">
					<img src="/images/docs2/buttons/file_extension_pdf.png" alt="" />Посмотреть в PDF</a>
			</div>
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/contract',array('id'=>$package->id,'format'=>'pdf','download'=>1))?>" title="Скачать в формате PDF" target="_blank">
					<img src="/images/docs2/buttons/file_extension_pdf_save.png" alt="" />Скачать в PDF</a>
			</div>
		</div><br/>
		<div class="buttons">
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/contractOriginal',array('id'=>$package->id))?>" title="Посмотреть неподписанную версию договора" target="_blank">
					<img src="/images/docs2/buttons/contractFullOriginal.png" alt="" />Оригинал договора</a>
			</div>
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/contractOriginal',array('id'=>$package->id,'format'=>'pdf'))?>" title="Открыть в браузере в формате PDF" target="_blank">
					<img src="/images/docs2/buttons/file_extension_pdf.png" alt="" />Посмотреть в PDF</a>
			</div>
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/contractOriginal',array('id'=>$package->id,'format'=>'pdf','download'=>1))?>" title="Скачать в формате PDF" target="_blank">
					<img src="/images/docs2/buttons/file_extension_pdf_save.png" alt="" />Скачать в PDF</a>
			</div>
		</div><br/>
		<div class="buttons">
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/bill',array('id'=>$package->id))?>" title="Посмотреть счёт на оплату" target="_blank">
					<img src="/images/docs2/buttons/bill.png" alt="" />Счёт на оплату</a>
			</div>
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/bill',array('id'=>$package->id,'format'=>'pdf'))?>" title="Открыть в браузере в формате PDF" target="_blank">
					<img src="/images/docs2/buttons/file_extension_pdf.png" alt="" />Посмотреть в PDF</a>
			</div>
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/bill',array('id'=>$package->id,'format'=>'pdf','download'=>1))?>" title="Скачать в формате PDF" target="_blank">
					<img src="/images/docs2/buttons/file_extension_pdf_save.png" alt="" />Скачать в PDF</a>
			</div>
		</div>
	</div>
</div>