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
			<a href="#tabs-status">Оплата в Robokassa</a>
		</li>
	</ul>
	<div id="tabs-status" style="padding:10px;">
		<?php
		if($package->invoice):
			?>
			<p>
				Клиенту представлена форма оплаты в Robokassa со следующими данными.
			</p>
			<table>
				<tr>
					<td class="name">Сумма:</td>
					<td class="value"><b><?php echo round($package->summ/9.6)*10;?> р. (Включая комиссию 4%)</b></td>
				</tr>
				<tr>
					<td class="name">Назначение:</td>
					<td class="value"><b><?php echo 'Оплата заказа '.$package->getNumber().'-'.$package->invoice->id?></b></td>
				</tr>
			</table>
		<?php else:?>
			<p>Клиент не заполнял форму на оплату через Robokassa.</p>
		<?php endif?>
	</div>
</div>