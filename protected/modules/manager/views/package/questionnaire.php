<?php
/**
 * @var PackageController $this
 * @var Package $pack
 */
echo CHtml::openTag('div',array('style'=>'width:600px;'));
echo CHtml::openTag('div',array('class'=>'formWindow','style'=>'margin-bottom:-12px;'));
echo CHtml::tag('div',array('class'=>'formHead'),'Результаты заполнения анкеты к заказу #'.$pack->id);
echo CHtml::openTag('div',array('class'=>'formBody','style'=>'padding:10px;'));
$arTDR=array('align'=>'right','valign'=>'top');
$arTDL=array('align'=>'left','valign'=>'top');
foreach($pack->questionnaire as $obItem) {
	echo CHtml::tag('table',array('class'=>"questionnaire"),
			CHtml::tag('col',array('width'=>200)).
			CHtml::tag('col',array('width'=>600)).
			CHtml::tag('tr',array(),
				CHtml::tag('td',$arTDR,'Дата заполнения').
				CHtml::tag('td',$arTDL,$obItem->date_filled)
			).
			CHtml::tag('tr',array(),
				CHtml::tag('td',$arTDR,'Тематика:').
				CHtml::tag('td',$arTDL,htmlspecialchars($obItem->description))
			).
			CHtml::tag('tr',array(),
				CHtml::tag('td',$arTDR,'Цвета:').
				CHtml::tag('td',$arTDL, htmlspecialchars($obItem->colors))
			).
			CHtml::tag('tr',array(),
				CHtml::tag('td',$arTDR,'Сайты которые нравятся:').
				CHtml::tag('td',$arTDL, htmlspecialchars($obItem->favorite_sites))
			)
	);
	echo CHtml::tag('br');
}
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');
