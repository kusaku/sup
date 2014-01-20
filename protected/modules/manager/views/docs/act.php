<?php 
/**
 * спасибо товарищу
 * @author runcore
 * http://habrahabr.ru/blogs/php/53210/
 */
 
 /**
 * cклоняем словоформу
 * @param object $n
 * @param object $f1
 * @param object $f2
 * @param object $f5
 * @return
 */

function morph($n, $f1, $f2, $f5) {
	$n = abs($n) % 100;
	$n1 = $n % 10;
	if ($n > 10 && $n < 20)
		return $f5;
	if ($n1 > 1 && $n1 < 5)
		return $f2;
	if ($n1 == 1)
		return $f1;
	return $f5;
}

/**
 * Сумма прописью
 * @param object $inn
 * @param object $stripkop [optional]
 * @return
 */

function num2str($inn, $stripkop = false) {
	$nol = 'ноль';
	$str[100] = array(
		'','сто','двести','триста','четыреста','пятьсот','шестьсот','семьсот','восемьсот',
			'девятьсот'
	);
	$str[11] = array(
		'','десять','одиннадцать','двенадцать','тринадцать','четырнадцать','пятнадцать',
			'шестнадцать','семнадцать','восемнадцать','девятнадцать','двадцать'
	);
	$str[10] = array(
		'','десять','двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят',
			'восемьдесят','девяносто'
	);
	$sex = array(
		// m
		array(
			'','один','два','три','четыре','пять','шесть','семь','восемь','девять'
		),
		
		// f
		array(
			'','одна','две','три','четыре','пять','шесть','семь','восемь','девять'
		)
	);
	$forms = array(
		// 10^-2
		array(
			'копейка','копейки','копеек',1
		),
		
		// 10^ 0
		array(
			'рубль','рубля','рублей',0
		),
		
		// 10^ 3
		array(
			'тысяча','тысячи','тысяч',1
		),
		
		// 10^ 6
		array(
			'миллион','миллиона','миллионов',0
		),
		
		// 10^ 9
		array(
			'миллиард','миллиарда','миллиардов',0
		),
		
		// 10^12
		array(
			'триллион','триллиона','триллионов',0
		)
	);
	$out = $tmp = array(
	);
	// Поехали!
	$tmp = explode('.', str_replace(',', '.', $inn));
	$rub = number_format($tmp[0], 0, '', '-');
	if ($rub == 0)
		$out[] = $nol;
	// нормализация копеек
	$kop = isset($tmp[1]) ? substr(str_pad($tmp[1], 2, '0', STR_PAD_RIGHT), 0, 2) : '00';
	$segments = explode('-', $rub);
	$offset = sizeof($segments);
	if ((int) $rub == 0) { // если 0 рублей
		$o[] = $nol;
		$o[] = morph(0, $forms[1][0], $forms[1][1], $forms[1][2]);
	} else {
		foreach ($segments as $k=>$lev) {
			$sexi = (int) $forms[$offset][3]; // определяем род
			$ri = (int) $lev; // текущий сегмент
			if ($ri == 0 && $offset > 1) {// если сегмент==0 & не последний уровень(там Units)
				$offset--;
				continue;
			}
			// нормализация
			$ri = str_pad($ri, 3, '0', STR_PAD_LEFT);
			// получаем циферки для анализа
			$r1 = (int) substr($ri, 0, 1); //первая цифра
			$r2 = (int) substr($ri, 1, 1); //вторая
			$r3 = (int) substr($ri, 2, 1); //третья
			$r22 = (int) $r2.$r3; //вторая и третья
			// разгребаем порядки
			if ($ri > 99)
				$o[] = $str[100][$r1]; // Сотни
			if ($r22 > 20) {// >20
				$o[] = $str[10][$r2];
				$o[] = $sex[$sexi][$r3];
			} else { // <=20
				if ($r22 > 9)
					$o[] = $str[11][$r22 - 9]; // 10-20
				elseif ($r22 > 0)
					$o[] = $sex[$sexi][$r3]; // 1-9
			}
			// Рубли
			$o[] = morph($ri, $forms[$offset][0], $forms[$offset][1], $forms[$offset][2]);
			$offset--;
		}
	}
	// Копейки
	if (!$stripkop) {
		$o[] = $kop;
		$o[] = morph($kop, $forms[0][0], $forms[0][1], $forms[0][2]);
	}
	return preg_replace("/\s{2,}/", ' ', implode(' ', $o));
}

// Месяцы на русском для подставления в дату
$months = array(
	'января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября',
		'ноября','декабря'
);
$month = $months[date('n') - 1];

?>
<table rules="NONE" cols="6" cellspacing="0" border="0" width="100%" style="font-family: arial; font-size:16px;">
	<colgroup>
		<col width="67"><col width="153"><col width="188"><col width="55"><col width="62"><col width="71"></colgroup>
	<tbody>
		<tr>
			<td height="47" align="CENTER" colspan="6">
				<b>
					<font size="4">
						Акт № <?= $package->getNumber(); ?>
						от <?= date('d '.$months[date('n', strtotime($package->dt_beg)) - 1].' Y', strtotime($package->dt_beg)); ?>г.
					</font>
				</b>
			</td>
		</tr>
		<tr>
			<td align="left" colspan="6">
				<b>Исполнитель:</b>&nbsp;
				<?php if($package->jur_person):?>
					<?php echo $package->jur_person->title?>
				<?php else:?>
					ООО «Фабрика сайтов»
				<?php endif?>
			</td>
		</tr>
		<tr>
			<td align="left" colspan="6">
				<b>Заказчик:</b>&nbsp;
				<?= $package->client->fio?>
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
		<?php $count = 0; ?>
		<?php $summ = 0; ?>
		<?php foreach ($package->servPack as $servPack): ?>
		<?php $count++?>
		<?php $summ += $servPack->price * $servPack->quant; ?>
		<tr>
			<td valign="TOP" height="34" align="center" style="border: 1px solid #222222;">
				<?= $count; ?>
			</td>
			<td valign="TOP" align="left" style="border-bottom: 1px solid #222222; border-left: 1px solid #222222;">
				<?= $servPack->service->name; ?>
			</td>
			<td valign="TOP" align="right" style="border-bottom: 1px solid #222222; border-left: 1px solid #222222;">
				<?= number_format($servPack->price, 2); ?>
			</td>
			<td valign="TOP" align="right" style="border-bottom: 1px solid #222222; border-left: 1px solid #222222;">
				<?= number_format($servPack->quant, 2); ?>
			</td>
			<td valign="TOP" align="CENTER" style="border-bottom: 1px solid #222222; border-left: 1px solid #222222;">шт.</td>
			<td valign="TOP" align="right" style="border-bottom: 1px solid #222222; border-left: 1px solid #222222; border-right: 1px solid #222222;">
				<?= number_format($servPack->price * $servPack->quant, 2); ?>
			</td>
		</tr>
		<?php endforeach; ?>
		<tr>
			<td colspan="5" align="right">ИТОГО:</td>
			<td align="right">
				<?= number_format($summ, 2); ?>
			</td>
		</tr>
		<tr>
			<td height="17" align="right" colspan="5">В том числе НДС:</td>
			<td valign="middle" align="right">
				<?= number_format($summ * .18, 2); ?>
			</td>
		</tr>
		<tr>
			<td height="17" align="left" colspan="6"></td>
		</tr>
		<tr>
			<td hight="17" align="left" colspan="6">
				<u>
					<font size="1">
						Всего наименований <?= $count; ?>, на сумму <?= number_format($summ, 2); ?> руб.
					</font>
				</u>
			</td>
		</tr>
		<tr>
			<td valign="TOP" height="17" align="left" colspan="6">
				<b>Сумма: <?= num2str($summ)?> </b>
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
		<tr>
			<td align="left" colspan="6" valign=bottom style="height: 68px; background:url(/images/signature-small.png) 290px 10px no-repeat;">
				<?php if($package->jur_person):?>
					<b><i><?php echo $package->jur_person->director_position?> ____________________/<?php echo $package->jur_person->director_fio?>/</i></b>
				<?php else:?>
					<b><i>и.о. Генерального директора ____________________/Захарьев Д.Л./</i></b>
				<?php endif?>
			</td>
		</tr>
		<tr>
			<td height="25px" align="left" colspan="6"> </td>
		</tr>
		<!--tr>
		<td align="left" colspan="6">
		<b><i>Главный бухгалтер __________________ /Абакумов С.Ю./</i></b>
		<img style="height: 60px; width: 100px; position: relative; top: 8px; left: -256px;" src="/images/signature.png" alt=""></td>
		</tr-->
	</tbody>
</table>
<?php if($package->jur_person && $package->jur_person->stamp_url!=''):?>
	<img alt="" src="<?php echo $package->jur_person->stamp_url?>" style="width: 42mm; height: 42mm;margin:-100px 0 0 400px;"/>
<?php else:?>
	<img alt="" src="/images/stamp.png" style="width: 42mm; height: 42mm;margin:-100px 0 0 400px;"/>
<?php endif?>