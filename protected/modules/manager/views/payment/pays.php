<?php
/**
 * @var Package $package
 */?>
<div style="width:800px;">
	<div class="formWindow" style="margin-bottom:-12px;">
		<div class="formHead">Платежи по заказу #<?php echo $package->id?></div>
		<div class="formBody" style="padding:10px;">
			<table class="tablesorter" style="border:1px solid #B6C3C7;">
				<thead>
					<tr>
						<th>ID</th>
						<th>Сумма</th>
						<th>Дата</th>
						<th>Описание</th>
						<th>Состояние платежа</th>
						<th>Действие</th>
					</tr>
				</thead>
				<tbody>
					<?php if (count($package->payments)): ?>
						<?php foreach ($package->payments as $payment): ?>
							<tr>
								<td><?php echo CHtml::link($payment->id,'#payment_'.$payment->id.'_'.$package->id,array('class'=>'payform'))?></td>
								<td><?php echo CHtml::link(number_format($payment->amount,2,',',' '),'#payment_'.$payment->id.'_'.$package->id,array('class'=>'payform'));?></td>
								<td><?php echo date('d.m.Y', strtotime($payment->dt));?></td>
								<td><?php echo $payment->description?></td>
								<td><?php switch($payment->ptype_id) {
									case 1: echo 'оплата'.($payment->dt_pay!=NULL?' ('.date('d.m.Y H:i',strtotime($payment->dt_pay)).')':'');break;
									case 2: echo 'возврат';break;
									default: echo 'платёжка';break;
								}?></td>
								<td>
									<?php if ($payment->ptype_id ==0 and (Yii::app()->user->checkAccess('topmanager') or Yii::app()->user->checkAccess('admin'))): ?>
										<a class="approve" rel="<?php echo $payment->id?>">подтвердить</a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach?>
					<?php else:?>
						<tr>
							<td colspan="6">Для этого заказа еще не было платежей.</td>
						</tr>
					<?php endif?>
				</tbody>
			</table>
			<div class="buttons" style="padding:10px 0;">
				<a href="#payment_0_<?php echo $package->id?>" class="paymentAdd payform">Добавить оплату</a>
			</div>
			<?php if($package->invoices):?>
			<div style="border-top:1px solid black;padding:5px;">
				<div>Автоматически выставленные счета (ЛКК):</div>
				<ol>
					<?php foreach($package->invoices as $obInvoice):?>
					<li>Счёт №<b><?php echo $obInvoice->id?></b> от <b><?php echo $obInvoice->summ?> р.</b> на сумму <b><?php echo $obInvoice->date_add?></b>
						<?php if($obInvoice->method):?>, оплата при помощи: <b>"<?php echo $obInvoice->method->title?>"</b><?php endif?></li>
					<?php endforeach?>
				</ol>
			</div>
			<?php endif?>
		</div>
	</div>
</div>
