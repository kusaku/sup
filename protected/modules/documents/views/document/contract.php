<sethtmlpagefooter name="contractFooter" page="all" value="on" />
<sethtmlpageheader name="contractHeader" page="all" value="on" show-this-page="1" />

<p align="CENTER">
	<font face="Verdana, sans-serif" size="2">
		<b>ДОГОВОР № <?= $package->getNumber(); ?></b>
	</font>
</p>
<p align="RIGHT">
	<table border="0" width="100%">
		<tr>
			<td align="left" class="western"><font face="Arial, sans-serif" size="2" color="#000000">г. Санкт-Петербург</font></td>
			<td align="right" class="western"><font face="Arial, sans-serif" size="2" color="#000000"><?php echo LangUtils::dateFormated(strtotime($package->dt_beg));?></font></td>
		</tr>
	</table>
</p>
<p><br></p>
<p align="JUSTIFY">
	<font face="Arial, sans-serif" size="2" color="#000000">
		<b><?php echo $jur_person->title?></b>, именуемое в дальнейшем «Исполнитель», уполномоченным лицом которого является 
			<?php echo $jur_person->director_position?> <?php echo $jur_person->director_fio?>, действующий на основании
			<?php switch($jur_person->director_source) {
					case 'charter':
						echo "Устава";
					break;
					case 'warrant':
						echo "Доверенности ".$jur_person->director_source_info;
					break;
					case 'order':
						echo "Приказа ".$jur_person->director_source_info;
					break;
					case 'protocol':
						echo "Протокола ".$jur_person->director_source_info;
					break;
					case 'text':
						echo $jur_person->director_source_info;
					break;
			}?>
		и 
		<b><?php echo trim($client_jur_person->title)?></b>,  именуемое в дальнейшем «Заказчик»,  уполномоченным лицом которого является
			<?php echo $client_jur_person->director_fio?>, действующий на основании <?php
			switch($client_jur_person->director_source) {
				case 'charter':
					echo "Устава";
				break;
				case 'warrant':
					echo "Доверенности ".$client_jur_person->director_source_info;
				break;
				case 'order':
					echo "Приказа ".$client_jur_person->director_source_info;
				break;
				case 'protocol':
					echo "Протокола ".$client_jur_person->director_source_info;
				break;
				case 'text':
					echo $client_jur_person->director_source_info;
				break;
			}?>,
		а вместе именуемые «Стороны», заключили настоящий Договор о нижеследующем.
	</font>
</p>
<p><br></p>

<p align="CENTER"><font face="Arial, sans-serif" size="2">1. <b>Термины</b></font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>1.1. Веб-сайт, сайт</b> - совокупность веб-страниц, объединенных общим корневым адресом, а также темой, логической структурой, оформлением.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>1.2. Веб-дизайн, дизайн</b> - визуальное оформление веб-страниц.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>1.3. Разработка дизайна сайта</b> - процесс создания визуально графического оформления веб-сайта.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>1.4. Оптимизация, поисковая оптимизация (SEO - search engine optimization)</b> - оптимизация HTML-кода, текста, структуры и внешних факторов сайта с целью поднятия его в выдаче поисковой системы.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>1.5. Поисковая система</b> - веб-сайт, предоставляющий возможность поиска информации в Интернете. 
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>1.6. Ссылка</b> - это запись, связывающая между собой части документа.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>1.7. Таргетинг</b> - точный охват целевой аудитории, осуществляемый по тематическим сайтам, по географии и по времени.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>1.8. Целевая аудитория</b> - обозначение потенциальных посетителей веб-узла, на которых в первую очередь ориентирован данный ресурс.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>1.9. Приложение</b> – документ, являющийся неотъемлемой частью Договора, в котором указан перечень работ по договору, стоимость работ и сроки завершения работ.
</font></p>
<p><br></p>

<p align="CENTER"><font face="Arial, sans-serif" size="2">2. <b>Предмет договора</b></font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>2.1.</b> Исполнитель обязуется оказать по заданию Заказчика, а Заказчик обязуется принять и оплатить услуги согласно Приложениям к настоящему Договору.
</font></p> 
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>2.2.</b> Все услуги, выходящие за рамки данного Договора оформляются дополнительными соглашениями к настоящему Договору.
</font></p>
<p><br/></p>

<p align="CENTER"><font face="Arial, sans-serif" size="2">3. <b>Права и обязанности сторон</b></font></p>
<p align="LEFT"><font face="Arial, sans-serif" size="2"><b>Исполнитель обязан:</b></font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>3.1.</b> До заключения договора предоставить Заказчику необходимую и достоверную информацию о предлагаемой услуге, ее видах, особенностях и 
	стоимости, а также сообщить Заказчику по его просьбе другие относящиеся к договору и соответствующей работе сведения. 
	Своими силами и средствами качественно оказать услуги в объеме, предусмотренном настоящим договором и Приложениями. 
	Сдать результат работ Заказчику в состоянии, соответствующем требованиям настоящего договора не позднее оговоренного в Приложениях срока.
</font></p> 
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>3.2.</b> По требованию Заказчика информировать его (Заказчика) о ходе проведения работ и промежуточных результатах. 
</font></p> 
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>3.3.</b> В течение 5 (пяти) рабочих дней со дня выполнения работ, уведомить Заказчика о выполнении работ путем направления письма-уведомления по адресу электронной почты 
		Заказчика, а также по адресу местонахождения Заказчика, путем направления акта выполненных работ. В случае отсутствия претензий со стороны Заказчика по результатам 
		выполненной работы (оказанной услуги) в течение 5 (пяти) календарных дней с момента получения письма-уведомления и акта, работа считается принятой в полном объеме и 
		услуга оказанной.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>3.4.</b> Сроки оказания услуги Исполнителем не включают сроки утверждения услуг Заказчиком.
</font></p> 
<p align="left"><font face="Arial, sans-serif" size="2">Заказчик обязан:</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>3.5.</b> Выполнять положения настоящего Договора. Заказчик согласен с тем, что услуги предоставляются ему на условиях, изложенных в настоящем Договоре и в Приложениях.
</font></p> 
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>3.6.</b> В разумный срок после заключения настоящего Договора предоставить Исполнителю в полном объеме необходимую документацию, материалы и информацию, достаточные для 
	оказания услуг согласно Приложениям.
	Если в процессе оказания услуг по Договору Исполнителем выявлено, что информационного наполнения и материалов, предоставленных Заказчиком, не достаточно для исполнения 
	договора, Заказчик обязуется предоставить в электронном виде такую информацию в течение 3-х рабочих дней по требованию Исполнителя.
</font></p>
<sethtmlpagefooter name="contractFooter" page="all" value="on" />
<sethtmlpageheader name="contractHeader" page="all" value="on" show-this-page="1"/>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>3.7.</b> Оплатить Исполнителю услуги, указанные в Приложениях к  настоящему Договору, в размерах, установленных в соответствующих Приложениях.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>3.8.</b> Рассмотреть и принять выполненный объем услуг в срок, установленный настоящим Договором.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>3.9.</b> При наличии возражений по качеству и объему оказанных услуг, Заказчик обязуется сообщить о них посредством электронной почты Исполнителю в срок не позднее 5 (пяти) рабочих 
	дней со дня отправления Исполнителем письма-уведомления, согласно п.3.3. Договора.
</font></p>
<p align="LEFT"><b><font face="Arial, sans-serif" size="2">Исполнитель вправе:</font></b></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>3.10.</b> В случае несвоевременной оплаты или несвоевременного предоставления информационных материалов (бриф, элементы фирменного стиля, фотографии, текстовый материал), 
	Исполнитель вправе по своему усмотрению:<br/>
	— требовать досрочного расторжения договора, и оплаты фактически оказанных услуг;<br/>
 	— увеличить срок оказания услуг по Договору соразмерно задержке;<br/>
	— удержать результат услуг.<br/>
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>3.11.</b> Исполнитель вправе привлекать к исполнению Договора на выполнение услуг третьих лиц без согласования с Заказчиком.
</font></p>
<p align="LEFT"><b><font face="Arial, sans-serif" size="2">Заказчик вправе:</font></b></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>3.12.</b> Заказчик имеет право проверять ход работы и качество услуг, оказываемых Исполнителем, не вмешиваясь в его деятельность.
		Требования, связанные с недостатками оказанных услуг, могут быть предъявлены Заказчиком при принятии работы или в ходе оказания услуг, либо, если невозможно 
		обнаружить недостатки при принятии работы, в течение 5 дней с момента принятия работы. Если Исполнитель нарушил сроки оказания услуги, или во время оказания 
		услуг стало очевидным, что работа не будет выполнена в срок, а также в случае обнаружения Заказчиком недостатков оказанной услуги, Заказчик по своему выбору вправе:<br/>
			— назначить Исполнителю новый срок;<br/>
			— требовать безвозмездного устранения недостатков выполненных работ.<br/>
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>3.13.</b> Заказчик вправе расторгнуть договор в любое время, уплатив Исполнителю часть стоимости услуг пропорционально части работы, выполненной до получения 
	извещения о расторжении указанного договора, и возместив Исполнителю расходы, произведенные им до этого момента в целях исполнения договора, если они не входят в 
	указанную часть цены услуги.
</font></p>
 <p><br/></p>

<sethtmlpagefooter name="contractFooter" page="all" value="on" />
<sethtmlpageheader name="contractHeader" page="all" value="on" show-this-page="1"/>

<p align="CENTER"><font face="Arial, sans-serif" size="2">4. <b>Стоимость и порядок расчетов</b></font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>4.1.</b> Стоимость Услуг указывается в Приложениях и в Личном кабинете Заказчика. 
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>4.2.</b> Стоимость Услуг устанавливается в рублях, с учетом налога на добавленную стоимость. В случае изменения установленной действующим законодательством ставки НДС, 
		стоимость Услуг изменяется соразмерно без дополнительного согласования Сторонами, если иное не оговаривается Исполнителем отдельно.
</font></p> 
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>4.3.</b> Услуги оплачиваются на основании счета, выставленного Заказчику. Срок действия счета устанавливается равным 10 (десять) банковских дней.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>4.4.</b> Оплата Услуг Заказчиком осуществляется на условиях предоплаты, если иное не оговорено отдельно в Заказе или дополнительных соглашениях к Договору, 
		в рублях на расчетный счет Исполнителя.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>4.5.</b> При оформлении Заказчиком платежных документов в разделе «Назначение платежа» обязательна ссылка на Номер Договора и указание о включении НДС в сумму платежа, 
	а в случае оплаты по счету – также на номер счета.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>4.6.</b> Возврат остатка неиспользованных средств Заказчика при расторжении Договора, либо в случае предъявления Заказчиком доводов о невозможности воспользоваться 
		услугами Исполнителя по каким-либо причинам, производится безналичным перечислением на расчетный счет Заказчика, с которого была произведена оплата услуг. 
		Возврат путем безналичного перечисления производится в 7-дневный срок, при наличии письменного заявления Заказчика с указанием полных реквизитов получателя.
</font></p>
<p><br/></p>

<sethtmlpagefooter name="contractFooter" page="all" value="on" />
<p align="CENTER"><font face="Arial, sans-serif" size="2">5. <b>Порядок и сроки исполнения обязательств</b></font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>5.1.</b> Исполнитель приступает к выполнению работ с момента поступления оплаты. После поступления оплаты, и предоставления Заказчиком в полном объеме необходимой 
	информации и материалов, достаточных для оказания Исполнителем услуг согласно Договору, Стороны согласуют иные детали оказания услуг, что указывается в Приложениях к 
	настоящему договору.
</font></p> 
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>5.2.</b> В случае необходимости расширения перечня услуг по настоящему Договору при развитии проекта, осуществляется разработка дополнительных программных модулей, 
	подключение баз данных, переход на другую программно-аппаратную платформу и т.д., что указывается сторонами в Приложениях.
</font></p> 
<p><br/></p>

<p align="CENTER"><font face="Arial, sans-serif" size="2">6. <b>Ответственность сторон</b></font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>6.1.</b> За неисполнение или ненадлежащее исполнение обязательств по настоящему Договору Стороны несут ответственность, предусмотренную законодательством Российской 
		Федерации с учетом условий, установленных настоящим Договором.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>6.2.</b> Исполнитель не несет юридической, материальной или иной ответственности за содержание, качество и соответствие действующему законодательству информации, 
		размещенной Заказчиком на Сайте.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>6.3.</b> Исполнитель не несёт ответственности по претензиям Заказчика к качеству соединения с сетью Интернет, связанным с качеством функционирования сетей 
		Интернет-провайдеров, с функционированием оборудования и программного обеспечения Заказчика, и другими обстоятельствами, находящимися вне компетенции Исполнителя.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>6.4.</b> В случае невозможности оказания услуг, возникшей по вине Заказчика, Заказчик возмещает Исполнителю фактически понесенные им расходы, но не более стоимости 
		оплаченных услуг.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>6.5.</b> За сделки, совершаемые с использованием сайта, Исполнитель ответственности не несёт.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>6.6.</b> В случае неисполнения или ненадлежащего исполнения одной из сторон обязательств по настоящему Договору виновная сторона возмещает другой стороне убытки в 
		размере 0,01% от общей стоимости договора за каждый рабочий день просрочки.
</font></p>
<p><br/></p>

<sethtmlpagefooter name="contractFooter" page="all" value="on" />
<sethtmlpageheader name="contractHeader" page="all" value="on" show-this-page="1"/>

<p align="CENTER"><font face="Arial, sans-serif" size="2">7. <b>Споры и разногласия</b></font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>7.1.</b> Все споры или разногласия, возникающие между сторонами по настоящему Договору или в связи с ним, разрешаются путем переговоров между сторонами.
	Претензионный порядок разрешения споров обязателен. Срок ответа на претензию установлен в 20 (двадцать) календарных дней с момента ее получения.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>7.2.</b> В случае невозможности разрешения разногласий путем переговоров они подлежат рассмотрению в судебном порядке, установленном законодательством РФ.
</font></p>
<p><br/></p>

<p align="CENTER"><font face="Arial, sans-serif" size="2">8. <b>Общие условия</b></font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>8.1.</b> Любые изменения и/или дополнения к настоящему Договору должны быть выполнены в письменной форме и подписаны обеими Сторонами.
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>8.2.</b> После заключения настоящего Договора все предыдущие соглашения, переговоры и переписка, как в устной, так и в письменной форме, касающиеся его предмета, 
	теряют силу.
</font></p>
<p><br/></p>

<p align="CENTER"><font face="Arial, sans-serif" size="2">9. <b>Конфиденциальность</b></font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>9.1.</b> Стороны настоящего Договора договорились о том, что вся технологическая и коммерческая информация, касающаяся их текущей деятельности и перспективных планов,
	уже полученная ими друг от друга, либо информация, которая будет ими получена друг от друга в течение срока действия Договора, является строго конфиденциальной и не 
	подлежит разглашению без письменного согласия Сторон.
</font></p>
<p><br/></p>

<sethtmlpagefooter name="contractFooter" page="all" value="on" />
<p align="CENTER"><font face="Arial, sans-serif" size="2">10. <b>Обстоятельства непреодолимой силы</b></font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>10.1.</b> Каждая из сторон освобождается от ответственности за полное или частичное невыполнение своих обязательств по настоящему Договору, если такое невыполнение 
	явилось результатом действия обстоятельств непреодолимой силы, возникших после подписания настоящего Договора. Обстоятельства непреодолимой силы включают в себя: 
	стихийные бедствия (пожары, наводнения, землетрясения и т.п.), военные действия,  изменение алгоритма ранжирования поисковыми системами (если об этом не было 
	официально объявлено не менее чем за пять рабочих дней), действия и/или нормативные акты федеральных и местных органов власти и организаций, ими уполномоченных, и 
	все другие события, которые компетентный арбитражный суд признает случаями непреодолимой силы.
</font></p>
<p><br/></p>

<p align="CENTER"><font face="Arial, sans-serif" size="2">11. <b>Срок действия Договора и порядок его расторжения</b></font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>11.1.</b> Настоящий Договор вступает в силу с момента подписания его Сторонами, является бессрочным, и действует до момента расторжения по инициативе одной из 
	Сторон с предварительным письменным уведомлением другой Стороны не менее чем за 10 (десять) календарных дней до предполагаемой даты расторжения, в соответствии с 
	условиями Договора, либо по обоюдному согласию. Дополнительными соглашениями к Договору Сторонами могут быть установлены иные сроки действия Договора или отдельных 
	приложений к нему. 
</font></p>
<p align="JUSTIFY"><font face="Arial, sans-serif" size="2">
	<b>11.2.</b> Настоящий Договор может быть прекращен досрочно по соглашению Сторон. 
</font></p>
<p><br/></p>

<sethtmlpagefooter name="contractFooter" page="all" value="on" />
<sethtmlpageheader name="contractHeader" page="all" value="on" show-this-page="1"/>
<p class="western" align="CENTER">
	<font face="Arial, sans-serif" size="2">
		12. <b>Реквизиты</b>
	</font>
</p>
<p class="western" align="CENTER"><br></p>
<table width="100%" cellpadding="5" cellspacing="0">
	<colgroup><col width="50%"><col width="50%"></colgroup>
	<tbody>
		<tr valign="TOP">
			<td style="border: none; padding: 0cm">
				<p style="margin-top: 0cm; margin-bottom: 0cm" align="LEFT">
					<font face="Arial, sans-serif" size="2"><b>Исполнитель:</b></font>
				</p>
				<p style="margin-top: 0cm; font-weight: normal" align="LEFT"><br></p>
			</td>
			<td style="; border: none; padding: 0cm">
				<p style="margin-top: 0cm" align="LEFT">
					<font face="Arial, sans-serif" size="2"><b>Заказчик:</b></font>
				</p>
			</td>
		</tr>
		<tr valign="TOP">
			<td style="border: none; padding: 0cm 0.5cm 0cm 0cm;" height="152" valign="top" width="49%">
				<p><font face="Arial, sans-serif" size="2"><?php echo $jur_person->title?></font></p>
				<p><font face="Arial, sans-serif" size="2">ИНН <?php echo $jur_person->inn?></font></p>
				<p><font face="Arial, sans-serif" size="2">КПП <?php echo $jur_person->kpp?></font></p>
				<p><font face="Arial, sans-serif" size="2">Адрес: <?php echo $jur_person->address?></font></p>
				<p><font face="Arial, sans-serif" size="2">Р/с <?php echo $jur_person->settlement_account?></font></p>
				<p><font face="Arial, sans-serif" size="2"><?php echo $jur_person->bank_title?></font></p>
				<p><font face="Arial, sans-serif" size="2">к/с <?php echo $jur_person->correspondent_account?></font></p>
				<p><font face="Arial, sans-serif" size="2">БИК <?php echo $jur_person->bank_bik?></font></p>
			</td>
			<td style="border: none; padding: 0cm;" height="152" valign="top" width="49%">
				<p class="western"><font face="Arial, sans-serif" size="2"><?php echo $client_jur_person->title?></font></p>
				<p class="western"><font face="Arial, sans-serif" size="2">ИНН <?php echo $client_jur_person->inn?></font></p>
				<?php if($client_jur_person->type=='ip'):?>
					<p class="western"><font face="Arial, sans-serif" size="2">ЕГРИП <?php echo $client_jur_person->egrip?></font></p>
				<?php else:?>
					<p class="western"><font face="Arial, sans-serif" size="2">КПП <?php echo $client_jur_person->kpp?></font></p>
				<?php endif?>
				<p class="western"><font face="Arial, sans-serif" size="2">Адрес: <?php echo $client_jur_person->address?></font></p>
				<p class="western"><font face="Arial, sans-serif" size="2">Р/с <?php echo $client_jur_person->settlement_account?></font></p>
				<p class="western"><font face="Arial, sans-serif" size="2"><?php echo $client_jur_person->bank_title?></font></p>
				<p class="western"><font face="Arial, sans-serif" size="2">к/с <?php echo $client_jur_person->correspondent_account?></font></p>
				<p class="western"><font face="Arial, sans-serif" size="2">БИК <?php echo $client_jur_person->bank_bik?></font></p>
			</td>
		</tr>
	</tbody>
</table>
<p class="western" align="CENTER">
	<font face="Arial, sans-serif" size="2">
		13. <b>Подписи</b>
	</font>
</p>
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
			<td style="border: none; padding: 0cm;" valign="top">
				<p class="western">
					<font face="Arial, sans-serif" size="2"><?php echo $jur_person->director_position?>:</font>
				</p>
				<p class="western" align="JUSTIFY"><br></p>
				<p class="western" align="JUSTIFY"><br></p>
				<p class="western">
					<font face="Arial, sans-serif" size="2">
						________________/<?php echo $jur_person->director_fio?>/
					</font><br/>
					<?php if($jur_person->sign_url!=''):?><img style="height: 60px; width: 100px; margin-top:-60px;" src="<?php echo $jur_person->sign_url?>" alt=""/><?php endif?>
				</p>
				<p class="western"><br></p>
				<p class="western"><br></p>
				<p class="western"><font face="Arial, sans-serif" size="2">М.П.</font></p>
			</td>
			<td style="; border: none; padding: 0cm" width="318" valign="top">
				<p class="western" align="JUSTIFY">
					<font face="Arial, sans-serif" size="2"><?php echo $client_jur_person->director_position?>:</font>
				</p>
				<p class="western" align="JUSTIFY"><br></p>
				<p class="western" align="JUSTIFY"><br></p>
				<p style="margin-top: 0cm; margin-bottom: 0cm" align="LEFT">
					<font face="Arial, sans-serif" size="2">
						________________________/<?php echo $client_jur_person->director_fio?>/
					</font>
				</p>
				<p class="western"><br></p>
				<p class="western"><br></p>
				<p class="western"><font face="Arial, sans-serif" size="2">М.П.</font></p>
			</td>
		</tr>
	</tbody>
</table>
<?php if($jur_person->stamp_url!=''):?>
	<img alt="" src="<?php echo $jur_person->stamp_url?>" style="width: 42mm; height: 42mm;margin-top: -15mm;"/>
<?php endif?>
<br/><br/><br/><br/><br/>
<?php
/*if($obProduct=$package->getProduct()) {
	if($this->getViewFile('contract_application/product'.$obProduct->id)) {
		echo $this->renderPartial('contract_application/product'.$obProduct->id,array('package'=>$package,'product'=>$obProduct,'client'=>$client),true);
	}*
}*/
