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
			<a href="#tabs-status">Оплата в Qiwi</a>
		</li>
	</ul>
	<div id="tabs-status" style="padding:10px;">
		<?php
		$obWorkflow=$package->initWorkflow();
		$data=$obWorkflow->getData('man_pay_qiwi');
		if(is_array($data)):
		?>
		<p>
			Клиенту представлена форма оплаты в Qiwi со следующими данными.
		</p>
		<table>
			<tr>
				<td class="name">Номер телефона:</td>
				<td class="value"><b><?php echo preg_replace('#^(\d{3})(\d{3})(\d{2})(\d{2})$#','($1) $2-$3-$4',$data['idto'])?></b></td>
			</tr>
			<tr>
				<td class="name">Сумма:</td>
				<td class="value qiwiSummHint"><b><?php echo round($package->summ/9.6)*10;?> р. (Включая комиссию Qiwi 4%)</b></td>
			</tr>
			<tr>
				<td class="name">Назначение:</td>
				<td class="value"><b><?php echo 'Оплата заказа '.$package->getNumber()?></b></td>
			</tr>
		</table>
		<?php else:?>
		<p>Клиент не заполнял форму на оплату через Qiwi.</p>
		<?php endif?>
	</div>
</div>