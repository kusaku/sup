<table rules="NONE" cols="6" cellspacing="0" border="0" width="100%" style="font-family: arial; font-size:16px;">
	<colgroup>
		<col width="67"><col width="153"><col width="188"><col width="55"><col width="62"><col width="71"></colgroup>
	<tbody>
		<tr>
			<td height="47" align="CENTER" colspan="6">
				<b>
					<font size="4">
						Акт № <?= $package->getNumber(); ?>
						от <?php echo LangUtils::dateFormated(time());?>
					</font>
				</b>
			</td>
		</tr>
		<tr>
			<td align="left" colspan="6">
				<b>Исполнитель:</b>&nbsp;
				<?php echo $jur_person->title?>
			</td>
		</tr>
		<tr>
			<td align="left" colspan="6">
				<b>Заказчик:</b>&nbsp;
				<?php echo $package->client->fio?>
			</td>
		</tr>
		<tr>
			<td height="17" align="left" colspan="6">
				<br></td>
		</tr>
		<tr>
			<td valign="middle" height="18" align="CENTER" style="border-top: 2px solid #222222; border-bottom: 2px solid #222222; border-left: 2px solid #222222;">
				<b>№</b>
			</td>
			<td valign="middle" align="CENTER" style="border-top: 2px solid #222222; border-bottom: 2px solid #222222; border-left: 2px solid #222222;">
				<b>Товар</b>
			</td>
			<td valign="middle" align="CENTER" style="border-top: 2px solid #222222; border-bottom: 2px solid #222222; border-left: 2px solid #222222;">
				<b>Цена</b>
			</td>
			<td valign="middle" align="CENTER" style="border-top: 2px solid #222222; border-bottom: 2px solid #222222; border-left: 2px solid #222222;">
				<b>Кол-во</b>
			</td>
			<td valign="middle" align="CENTER" style="border-top: 2px solid #222222; border-bottom: 2px solid #222222; border-left: 2px solid #222222;">
				<b>Ед.</b>
			</td>
			<td valign="middle" align="CENTER" style="border: 2px solid #222222;">
				<b>Сумма</b>
			</td>
		</tr>
		<?php $count = 0; $summ = 0; 
		foreach ($services as $arService): 
			$summ += $arService['price'] * $arService['quant']; ?>
			<tr>
				<td valign="TOP" height="34" align="center" style="border-bottom: 1px solid #222222; border-left: 1px solid #222222;">
					<?= ++$count; ?>
				</td>
				<td valign="TOP" align="left" style="border-bottom: 1px solid #222222; border-left: 1px solid #222222;">
					<?php echo $arService['name']?>
				</td>
				<td valign="TOP" align="right" style="border-bottom: 1px solid #222222; border-left: 1px solid #222222;">
					<?php echo LangUtils::money($arService['price']); ?>
				</td>
				<td valign="TOP" align="right" style="border-bottom: 1px solid #222222; border-left: 1px solid #222222;">
					<?php echo number_format($arService['quant'], 2, ',', ' '); ?>
				</td>
				<td valign="TOP" align="CENTER" style="border-bottom: 1px solid #222222; border-left: 1px solid #222222;">шт.</td>
				<td valign="TOP" align="right" style="border-bottom: 1px solid #222222; border-left: 1px solid #222222; border-right: 1px solid #222222;">
					<?php echo LangUtils::money($arService['price'] * $arService['quant']); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		<tr>
			<td colspan="5" align="right">ИТОГО:</td>
			<td align="right">
				<?= LangUtils::money($summ); ?>
			</td>
		</tr>
		<tr>
			<td height="17" align="right" colspan="5">В том числе НДС:</td>
			<td valign="middle" align="right">
				<?= LangUtils::money($summ * .18/1.18); ?>
			</td>
		</tr>
		<tr>
			<td height="17" align="left" colspan="6"></td>
		</tr>
		<tr>
			<td hight="17" align="left" colspan="6">
				<u>
					<font size="1">
						Всего наименований <?= $count; ?>, на сумму <?= LangUtils::money($summ); ?> руб.
					</font>
				</u>
			</td>
		</tr>
		<tr>
			<td valign="TOP" height="17" align="left" colspan="6">
				<b>Сумма: <?= LangUtils::num2str($summ)?> </b>
			</td>
		</tr>
		<tr>
			<td height="35px" align="left" colspan="6"> </td>
		</tr>
		<tr>
			<td height="35px" align="left" colspan="6" style="border-bottom: 1px solid #222222;">Вышеперечисленные услуги выполнены полностью и в срок. Заказчик претензий по объему, качеству и срокам оказания услуг не имеет.</td>
		</tr>
		<tr>
			<td height="35px" align="left" colspan="6"> </td>
		</tr>
		<tr><td colspan="6">
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td align="left" valign="bottom" style="height: 68px;"><b><i><?php echo $jur_person->director_position?></i></b></td>
					<td align="left" valign="bottom" style="width:42mm;border-bottom:1px solid black;<?php if($jur_person->sign_url!=''):?>background:url(<?php echo $jur_person->sign_url?>) left bottom no-repeat;<?php endif?>"> </td>
					<td align="left" valign="bottom" style="white-space: nowrap;"><b><i>/<?php echo $jur_person->director_fio?>/</i></b></td>
				</tr>
				<?php if($jur_person && $jur_person->stamp_url!=''):?>
				<tr>
					<td></td>
					<td><img alt="" src="<?php echo $jur_person->stamp_url?>" style="margin-top:-5mm;width: 42mm; height: 42mm;"/></td>
					<td></td>
				</tr>
				<?php endif?>
			</table>
		</td></tr>
		<!--tr>
		<td align="left" colspan="6">
		<b><i>Главный бухгалтер __________________ /Абакумов С.Ю./</i></b>
		<img style="height: 60px; width: 100px; position: relative; top: 8px; left: -256px;" src="/images/signature.png" alt=""></td>
		</tr-->
	</tbody>
</table>