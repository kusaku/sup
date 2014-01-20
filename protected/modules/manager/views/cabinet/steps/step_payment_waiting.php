<?php
/**
 * @var PackageWorkflowStep $step
 * @var Package $package
 * @var CabinetController $this
 */
?>
<div class="tabscontainer modal">
	<ul>
		<li>
			<a href="#tabs-comments">Комментарии</a>
		</li>
		<li>
			<a href="#tabs-status">Состояние шага</a>
		</li>
	</ul>
	<div id="tabs-comments">
		<?php $this->widget('manager.widgets.CommentsWidget',array('key'=>'package_'.$package->id.'_'.$step->text_ident));?>
	</div>
	<div id="tabs-status">
		<?php
		if($package->payment_id<20 && $package->invoice) {
			if($package->invoice->pay_method_id==3) {
				echo 'Ожидаем оплату банковской квитанции';
			} elseif($package->invoice->pay_method_id==4) {
				echo 'Ожидаем оплату счёта и получение копии платёжного поручения';
			} elseif($package->invoice->method->category_id==6) {
				//Robokassa
				echo 'Ожидаем оплаты счёта в Robokassa. Номер счёта: '.$package->invoice->id;
			} elseif($package->invoice->method->category_id==5) {
				echo 'Ожидаем оплаты счёта в Qiwi. Номер выставленного счёта начинается на: '.$package->getNumber().'-'.$package->invoice->id;
			}
		} elseif($package->payment_id==20) {
			echo 'Платёж пользователя условно зачислен';
		} elseif($package->payment_id>20) {
			echo 'Платёж пользователя считается выполненным';
		}
		if($package->invoice) {
			if($package->invoice->method->payer_type=='man') {
				?><div class="buttons">
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
				</div><?
			} else {
				?><div class="buttons">
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
				</div><?
			}
		}?>
	</div>
</div>