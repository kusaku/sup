<?php
$obProductData=$package->getProductEx(true);
?>
<pagebreak resetpagenum="1" />
<sethtmlpagefooter name="contractFooter" page="all" value="on" />
<sethtmlpageheader name="contractHeader" page="all" value="on" show-this-page="1"/>
<p style="text-align:right;"><font face="Arial, sans-serif" size="2"><b>Приложение №1 от <?php echo LangUtils::dateFormated(strtotime($package->dt_change))?><br/>
к Договору №<?php echo $package->getNumber()?> от <?php echo LangUtils::dateFormated(strtotime($package->dt_beg))?></b></font></p>
<p><br/></p>

<p style="text-align:center;text-transform:uppercase;"><font face="Arial, sans-serif" size="2">Перечень и условия предоставления услуг</font></p>
<p><br/></p>

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
		<?php if($arDescriptions=$product->descriptions):?>
			<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;">
				<font face="Arial, sans-serif" size="2">Веб-сайт (<?php echo $arDescriptions[0]->title?>)</font>
			</td>
			<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;">
				<font face="Arial, sans-serif" size="2"><?php echo $arDescriptions[0]->days?></font>
			</td>
		<?php else:?>
			<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;">
				<font face="Arial, sans-serif" size="2">Веб-сайт (<?php echo $product->name?>)</font>
			</td>
			<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;"><font face="Arial, sans-serif" size="2">7</font></td>
		<?php endif?>
		<td style="padding:3px 5px;border-top:1px solid black;">
			<font face="Arial, sans-serif" size="2"><?php echo LangUtils::money($obProductData->price*$obProductData->quant,false)?></font>
		</td>
	</tr>
	<?php $i=2;?>
	<?php foreach($package->servPack as $obServiceData):?>
		<?if($obServiceData->serv_id==$obProductData->serv_id) continue;?>
		<tr>
			<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;">
				<font face="Arial, sans-serif" size="2"><?php echo $i++;?>.</font>
			</td>
			<?php if($arDescriptions=$obServiceData->service->descriptions):?>
				<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;">
					<font face="Arial, sans-serif" size="2">Веб-сайт (<?php echo $arDescriptions[0]->title?>)</font>
				</td>
				<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;">
					<font face="Arial, sans-serif" size="2"><?php echo $arDescriptions[0]->days?></font>
				</td>
			<?php else:?>
				<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;">
					<font face="Arial, sans-serif" size="2"><?php echo $obServiceData->service->name?></font>
				</td>
				<td style="padding:3px 5px;border-top:1px solid black;border-right:1px solid black;"></td>
			<?php endif?>
			<td style="padding:3px 5px;border-top:1px solid black;">
				<font face="Arial, sans-serif" size="2"><?php echo LangUtils::money($obServiceData->price*$obServiceData->quant,false)?></font>
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
<p><font face="Arial, sans-serif" size="2">В пакет создания веб-сайта входит:</font></p>
<ul style="list-style: circle;padding-left:40px;">
	<li><font face="Arial, sans-serif" size="2">Регистрация доменного имени в зоне .RU / .РФ (1 год)</font></li> 
	<li><font face="Arial, sans-serif" size="2">Хостинг (3 месяца)</font></li>
	<li><font face="Arial, sans-serif" size="2">Готовый дизайн сайта (макет)</font></li>
	<li><font face="Arial, sans-serif" size="2">3 страницы сайта-визитки</font></li>
	<li><font face="Arial, sans-serif" size="2">Система управления сайтом (CMS)</font></li> 
	<li><font face="Arial, sans-serif" size="2">Счетчик посещаемости сайта</font></li>
	<li><font face="Arial, sans-serif" size="2">Регистрация в поисковых системах</font></li>
	<li><font face="Arial, sans-serif" size="2">Статейный обмен</font></li>
</ul>


<p style="text-align:center;"><font face="Arial, sans-serif" size="2">2. <b>Дополнительные условия к Договору</b></font></p>
<p><font face="Arial, sans-serif" size="2"><b>Исполнитель обязан:</b></font></p>
<p style="text-align: justify;font-family: Arial, sans-serif;font-size:13px;"><font face="Arial, sans-serif" size="2">
	<b>2.1.</b> Предложить Заказчику на выбор 5 (пять) макетов дизайна по заданной тематике.
</font></p>
<p style="text-align: justify;font-family: Arial, sans-serif;font-size:13px;"><font face="Arial, sans-serif" size="2">
	<b>2.1.1.</b> Макеты дизайна предоставляются Заказчику посредством отправки на адрес электронной почты Заказчика, указанный в Личном кабинете клиента.
</font></p>
<p style="text-align: justify;font-family: Arial, sans-serif;font-size:13px;"><font face="Arial, sans-serif" size="2">
	<b>2.2.</b> Внести по желанию Заказчика однократно изменения, не влекущие изменения структуры сайта, его функциональных возможностей и/или 
	цветового решения, в созданный сайт в течение 5 (пяти) рабочих дней с момента предоставления перечня работ. Перечень изменений предоставляется Заказчиком в течение 14 дней с 
	момента сдачи проекта.
</font></p>

<p><font face="Arial, sans-serif" size="2"><b>Заказчик обязан:</b></font></p>
<p style="text-align: justify;font-family: Arial, sans-serif;font-size:13px;"><font face="Arial, sans-serif" size="2">
	<b>2.3.</b> Утвердить 1 (один) макет дизайна в течение 5 рабочих дней. Сайт разрабатывается на основе  утвержденного Заказчиком макета.
</font></p>
<p style="text-align: justify;font-family: Arial, sans-serif;font-size:13px;"><font face="Arial, sans-serif" size="2">
	<b>2.4.</b> Перед началом выполнения работ предоставить Исполнителю в электронном виде заполненный Бриф 
	по форме Исполнителя.
</font></p>
<p style="text-align: justify;font-family: Arial, sans-serif;font-size:13px;"><font face="Arial, sans-serif" size="2">
	<b>2.5.</b> В течение 5 рабочих дней рассмотреть предоставленные Исполнителем результаты выполнения работ согласно настоящему приложению.
</font></p>

<p><font face="Arial, sans-serif" size="2"><b>Исполнитель вправе:</b></font></p>
<p style="text-align: justify;font-family: Arial, sans-serif;font-size:13px;"><font face="Arial, sans-serif" size="2">
	<b>2.6.</b> Установить на каждой странице сайта логотип и текстовую гиперссылку с указанием на создателя сайта или требовать установки такой 
	ссылки впоследствии.
</font></p>

<p><font face="Arial, sans-serif" size="2"><b>Интеллектуальная собственность:</b></font></p>
<p style="text-align: justify;font-family: Arial, sans-serif;font-size:13px;"><font face="Arial, sans-serif" size="2">
	<b>2.7.</b> Заказчику предоставляется право на использование программы для 
	ЭВМ "MODX Revolution", v.2.2.0, 2012 года выпуска, представляющую собой систему управления Сайтом, на условиях лицензии «GNU General Public License, Version 2, June 1991», 
	доступной для ознакомления по адресу http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt. Исполнитель осуществляет установку и настройку системы 
	управления Сайтом в соответствии с настоящим приложением.
</font></p>

<table width="100%" cellpadding="5" cellspacing="0">
	<colgroup><col width="50%"><col width="50%"></colgroup>
	<tbody>
		<tr valign="TOP">
			<td style="border: none; padding: 0cm">
				<p style="margin-top: 0cm; margin-bottom: 0cm" align="LEFT">
					<font face="Arial, sans-serif" size="2"><b>Исполнитель:</b></font>
				</p>
			</td>
			<td style="; border: none; padding: 0cm">
				<p style="margin-top: 0cm" align="LEFT">
					<font face="Arial, sans-serif" size="2"><b>Заказчик:</b></font>
				</p>
			</td>
		</tr>
		<tr valign="TOP">
			<?php if($package->jur_person):?>
				<td style="border: none; padding: 0cm;" valign="top">
					<p class="western">
						<font face="Arial, sans-serif" size="2"><?php echo $package->jur_person->director_position?>:</font>
					</p>
					<p class="western" align="JUSTIFY"><br></p>
					<p class="western" align="JUSTIFY"><br></p>
					<p class="western">
						<font face="Arial, sans-serif" size="2">
							________________/<?php echo $package->jur_person->director_fio?>/
						</font><br/>
						<?php if(!isset($nostamps)):?><img style="height: 60px; width: 100px; margin-top:-60px;" src="/images/signature.png" alt=""/><?php endif?>
					</p>
					<p class="western"><br></p>
					<p class="western"><br></p>
					<p class="western"><font face="Arial, sans-serif" size="2">М.П.</font></p>
				</td>
			<?php else:?>
				<td style="border: none; padding: 0cm" valign="top">
					<p class="western">
						<font face="Arial, sans-serif" size="2">И.о. генерального директора:</font>
					</p>
					<p class="western">
						<font face="Arial, sans-serif" size="2">
							________________/Захарьев Д.Л./
						</font>
						<br/>
						<?php if(!isset($nostamps)):?><img style="height: 60px; width: 100px; margin-top:-60px;" src="/images/signature.png" alt=""/><?php endif?>
					</p>
					<p class="western"><br></p>
					<p class="western"><br></p>
					<p class="western"><font face="Arial, sans-serif" size="2">М.П.</font></p>
				</td>
			<?php endif?>
			<td style="; border: none; padding: 0cm" width="318" valign="top">
				<p class="western" align="JUSTIFY">
					<?php if($client->jur_person):?>
						<font face="Arial, sans-serif" size="2"><?php echo $client->jur_person->director_position?>:</font>
					<?php else:?>
						<font face="Arial, sans-serif" size="2">Генеральный директор:</font>
					<?php endif?>
				</p>
				<p class="western" align="JUSTIFY"><br></p>
				<p class="western" align="JUSTIFY"><br></p>
				<p style="margin-top: 0cm; margin-bottom: 0cm" align="LEFT">
					<font face="Arial, sans-serif" size="2">
						________________________/________________________/
					</font>
				</p>
				<p class="western"><br></p>
				<p class="western"><br></p>
				<p class="western"><font face="Arial, sans-serif" size="2">М.П.</font></p>
			</td>
		</tr>
	</tbody>
</table>
<?php if(!isset($nostamps)):?>
	<?php if($package->jur_person && $package->jur_person->stamp_url!=''):?>
		<img alt="" src="<?php echo $package->jur_person->stamp_url?>" style="width: 42mm; height: 42mm;margin-top: -15mm;"/>
	<?php else:?>
		<img alt="" src="/images/stamp.png" style="width: 42mm; height: 42mm;margin-top: -15mm;"/>
	<?php endif?>
<?php endif?>

<sethtmlpagefooter name="contractFooter" page="all" value="on" />
<sethtmlpageheader name="contractHeader" page="all" value="on" show-this-page="1"/>
