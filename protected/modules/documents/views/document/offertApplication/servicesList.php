

<p style="text-align:center;"><font face="Arial, sans-serif" size="2">1. <b>Состав работ, сроки</b></font></p>
<table border="0" cellspacing="0" cellpadding="0" width="100%" style="border:1px solid black;border-collapse:collapse;">
	<col width="5%"/>
	<col width="65%"/>
	<col width="15%"/>
	<col width="15%"/>
	<tr>
		<td style="padding:3px 5px;border-right:1px solid black;"><font face="Arial, sans-serif" size="2">№</font></td>
		<td style="padding:3px 5px;border-right:1px solid black;"><font face="Arial, sans-serif" size="2">Услуга:</font></td>
		<td style="padding:3px 5px;border-right:1px solid black;"><font face="Arial, sans-serif" size="2">Сроки (рабочие дни)</font></td>
		<td style="padding:3px 5px;"><font face="Arial, sans-serif" size="2">Сумма в рублях</font></td>
	</tr>
	<tr>
		<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;"><font face="Arial, sans-serif" size="2">1.</font></td>
		<?php if($arDescriptions=$product->service->descriptions):?>
			<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;">
				<font face="Arial, sans-serif" size="2">Веб-сайт (<?php echo ($arDescriptions[0]->document_title!='')?$arDescriptions[0]->document_title:$arDescriptions[0]->title?>)</font>
			</td>
			<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;">
				<font face="Arial, sans-serif" size="2"><?php echo $arDescriptions[0]->days?></font>
			</td>
		<?php else:?>
			<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;">
				<font face="Arial, sans-serif" size="2">Веб-сайт (<?php echo $product->service->name?>)</font>
			</td>
			<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;"><font face="Arial, sans-serif" size="2">7</font></td>
		<?php endif?>
		<td style="padding:3px 5px;border-top:1px solid black;">
			<font face="Arial, sans-serif" size="2"><?php echo LangUtils::money($product->price*$product->quant,false)?></font>
		</td>
	</tr>
	<?php $i=1;?>
	<?php foreach($services as $arService):?>
		<tr>
			<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;">
				<font face="Arial, sans-serif" size="2"><?php echo ++$i;?>.</font>
			</td>
			<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;">
				<font face="Arial, sans-serif" size="2"><?php echo $arService['title']?></font>
			</td>
			<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;">
				<font face="Arial, sans-serif" size="2"><?php echo $arService['days']?></font>
			</td>
			<td style="padding:3px 5px;border-top:1px solid black;">
				<font face="Arial, sans-serif" size="2"><?php echo LangUtils::money($arService['price']*$arService['quant'],false)?></font>
			</td>
		</tr>
	<?php endforeach?>
	<tr>
		<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;"></td>
		<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;"><font face="Arial, sans-serif" size="2">ИТОГО:</font></td>
		<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;"></td>
		<td style="padding:3px 5px;border-top:1px solid black;"><font face="Arial, sans-serif" size="2"><?php echo LangUtils::money($package->summ,false)?></font></td>
	</tr>
</table>
<p style="text-align:right;font-weight:bold;"><font face="Arial, sans-serif" size="2">
	Итого с учётом НДС (18%): <?php echo LangUtils::money($package->summ)?> (<?php echo LangUtils::num2str($package->summ,true)?>) рублей.
</font></p>