<table width="720" bordercolor="#000000" style="border:#000000 1px solid;font-family: monospace,serif; font-size:14px; line-height:24px;" cellpadding="0" cellspacing="0">
	<tr>
		<td width="220" valign="top" height="250" align="center" style="border-bottom:#000000 1px solid; border-right:#000000 1px solid;">
			&nbsp;<strong>Извещение</strong>
		</td>
		<td valign="top" style="border-bottom:#000000 1px solid; border-right:#000000 1px solid; background:url(/images/fabricalogo_small.png) right top no-repeat; padding:5px 0 5px 10px">
			<strong>Получатель: </strong>
			<span style="font-size:90%"><?php echo $jur_person->title?></span>&nbsp;&nbsp;&nbsp;<br/>
			<strong>ИНН:</strong><?php echo $jur_person->inn?>&nbsp;&nbsp;<span style="font-size:12px"> &nbsp;</span>
			<strong>P/сч.:</strong><?php echo $jur_person->settlement_account?><br/>
			<strong>в:</strong><span style="font-size:90%"><?php echo $jur_person->bank_title?></span><br/>
			<strong>БИК:</strong><?php echo $jur_person->bank_bik?>&nbsp; <strong>К/сч.:</strong><?php echo $jur_person->correspondent_account?><br/>
			<strong>Платеж:</strong>
			<span style="font-size:90%">
				<?php echo $title?>
			</span>
			<br/>
			<strong>Плательщик:</strong>
			<span style="font-size:90%"> ___________________________________________</span>
			<br/>
			<strong>Адрес плательщика:</strong>
			<span style="font-size:90%"> ___________________________________</span><br/>
			<strong>Сумма:</strong>
			<?= (int) $package->summ?> руб. <?= round(($package->summ - (int) $package->summ) * 100)?> коп. &nbsp;&nbsp;&nbsp;&nbsp;<strong><br/>
			<br/>
			Подпись:_______________&nbsp; Дата: &quot;___&quot;&nbsp;_________&nbsp;<?= date('Y')?> г.   
			<br/>
		</td>
	</tr>
	<tr>
		<td width="220" valign="top" height="250" align="center" style="border-bottom:#000000 1px solid; border-right:#000000 1px solid;">
			&nbsp;<strong>Квитанция</strong>
		</td>
		<td valign="top" style="border-bottom:#000000 1px solid; border-right:#000000 1px solid; background:url(/images/fabricalogo_small.png) right top no-repeat; padding:5px 0 5px 10px">
			<strong>Получатель: </strong>
			<span style="font-size:90%"><?php echo $jur_person->title?></span>&nbsp;&nbsp;&nbsp;<br/>
			<strong>ИНН:</strong><?php echo $jur_person->inn?>&nbsp;&nbsp;<span style="font-size:12px"> &nbsp;</span>
			<strong>P/сч.:</strong><?php echo $jur_person->settlement_account?><br/>
			<strong>в:</strong><span style="font-size:90%"><?php echo $jur_person->bank_title?></span><br/>
			<strong>БИК:</strong><?php echo $jur_person->bank_bik?>&nbsp; <strong>К/сч.:</strong><?php echo $jur_person->correspondent_account?><br/>
			<strong>Платеж:</strong>
			<span style="font-size:90%">
				<?php echo $title?>
			</span>
			<br/>
			<strong>Плательщик:</strong>
			<span style="font-size:90%"> ___________________________________________</span>
			<br/>
			<strong>Адрес плательщика:</strong>
			<span style="font-size:90%"> ___________________________________</span><br/>
			<strong>Сумма:</strong>
			<?= (int) $package->summ?> руб. <?= round(($package->summ - (int) $package->summ) * 100)?> коп. &nbsp;&nbsp;&nbsp;&nbsp;<strong><br/>
			<br/>
			Подпись:_______________&nbsp; Дата: &quot;___&quot;&nbsp;_________&nbsp;<?= date('Y')?> г.   
			<br/>
		</td>
	</tr>
</table>
