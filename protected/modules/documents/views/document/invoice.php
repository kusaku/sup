<table rules="NONE" cols="6" cellspacing="0" border="0" width="100%">
	<col width="67"><col width="153"><col width="188"><col width="55"><col width="62"><col width="71">
	<tbody>
		<tr>
			<td colspan="5" width="597"><b>ПОСТАВЩИК:  <?php echo $jur_person->title?></b></td>
			<td valign="top" rowspan="3"><img src="/images/fabricalogo.jpg" style="float: right; margin: 5px 5px 0 5px;"></td>
		</tr>
		<tr><td colspan="5"><b>ИНН <?php echo $jur_person->inn?></b></td></tr>
		<tr><td colspan="5"><b>КПП <?php echo $jur_person->kpp?></b></td></tr>
		<tr><td colspan="6" style="font-weight:bold;">Адрес: <?php echo $jur_person->address?></td></tr>
		<tr><td colspan="6"><b>Р/с <?php echo $jur_person->settlement_account?></b></td></tr>
		<tr><td colspan="6"><b><?php echo $jur_person->bank_title?></b></td></tr>
		<tr><td colspan="6"><b>к/с <?php echo $jur_person->correspondent_account?></b></td></tr>
		<tr><td colspan="6"><b>БИК <?php echo $jur_person->bank_bik?></b></td></tr>
		<tr>
			<td height="17" align="left" colspan="6"><br></td>
		</tr>
		<tr>
			<td height="47" align="CENTER" colspan="6">
				<b>
					<font size="4">
						Счет № <?= $package->getNumber(); ?>
						<br>
						От <?php echo LangUtils::dateFormated(time());?>
					</font>
				</b>
			</td>
		</tr>
		<tr>
			<td height="17" align="left" colspan="6"><br></td>
		</tr>
		<tr><td colspan="6"><b>ЗАКАЗЧИК:  <?php echo $client_jur_person->title?></b></td></tr>
		<tr><td colspan="6" style="font-weight:bold;">Адрес: <?php echo $client_jur_person->address?></td></tr>
		<tr><td colspan="6"><b>ИНН <?php echo $client_jur_person->inn?></b></td></tr>
		<?php if($client_jur_person->type=='ip'):?>
			<tr><td colspan="6"><b>ЕГРИП <?php echo $client_jur_person->egrip?></b></td></tr>
		<?php else:?>
			<tr><td colspan="6"><b>КПП <?php echo $client_jur_person->kpp?></b></td></tr>
		<?php endif?>
		<tr><td colspan="6"><b>Р/с <?php echo $client_jur_person->settlement_account?></b></td></tr>
		<tr><td colspan="6"><b><?php echo $client_jur_person->bank_title?></b></td></tr>
		<tr><td colspan="6"><b>к/с <?php echo $client_jur_person->correspondent_account?></b></td></tr>
		<tr><td colspan="6"><b>БИК <?php echo $client_jur_person->bank_bik?></b></td></tr>
		<tr>
			<td height="30px" align="left">
				<br></td>
			<td align="right" colspan="5">
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
			if($arService['id']==162) continue;
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
						Всего наименований <?= $count; ?>, на сумму <?= number_format($summ, 2, ',', ' '); ?> руб.
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
		<tr><td colspan="6">
			<table border="0" cellspacing="0" cellpadding="0">
				<tr>
					<td align="left" valign="bottom" style="height: 68px;"><b><i><?php echo $jur_person->director_position?></i></b></td>
					<td align="left" valign="bottom" style="width:48mm;border-bottom:1px solid black;<?php if($jur_person->sign_url!=''):?>background:url(<?php echo $jur_person->sign_url?>) left bottom no-repeat;<?php endif?>"> </td>
					<td align="left" valign="bottom" style="white-space: nowrap;"><b><i>/<?php echo $jur_person->director_fio?>/</i></b></td>
				</tr>
				<?php if($jur_person && $jur_person->stamp_url!=''):?>
				<tr>
					<td></td>
					<td><img alt="" src="<?php echo $jur_person->stamp_url?>" style="margin-top:-5mm;width: 48mm; height: 48mm;"/></td>
					<td></td>
				</tr>
				<?php endif?>
			</table>
		</td></tr>
	</tbody>
</table>