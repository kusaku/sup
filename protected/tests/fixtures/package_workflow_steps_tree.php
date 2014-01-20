<?php
return array(
	array(
		'id'=>"1",
		'from_step_id'=>"1",
		'to_step_id'=>"2",
		'comment'=>"Выбор продукта на редактирование списка услуг",
	),
	array(
		'id'=>"2",
		'from_step_id'=>"2",
		'to_step_id'=>"3",
		'comment'=>"Выбор услуг и ввод контактных данных на оплату"
	),
	array(
		'id'=>"3",
		'from_step_id'=>"3",
		'to_step_id'=>"2",
		'comment'=>"Возврат с оплаты на выбор услуг"
	),
	array(
		'id'=>"4",
		'from_step_id'=>"3",
		'to_step_id'=>"4",
		'comment'=>"Выбор способа оплаты на оплату по квитанции банка"
	),
	array(
		'id'=>"5",
		'from_step_id'=>"3",
		'to_step_id'=>"5",
		'comment'=>"Выбор способа оплаты на заполнение данных юр-лица"
	),
	array(
		'id'=>"6",
		'from_step_id'=>"4",
		'to_step_id'=>"3",
		'comment'=>"Возврат с квитанции банка на выбор способа оплаты"
	),
	array(
		'id'=>"7",
		'from_step_id'=>"5",
		'to_step_id'=>"3",
		'comment'=>"Возврат с заполнения данных юр-лица на выбор способа оплаты"
	),
	array(
		'id'=>"8",
		'from_step_id'=>"5",
		'to_step_id'=>"6",
		'comment'=>"Переход с ввода данных юр-лица на проверку данных и печать договора"
	),
	array(
		'id'=>"9",
		'from_step_id'=>"6",
		'to_step_id'=>"5",
		'comment'=>"Переход с печати договора на исправление данных юр-лица"
	),
	array(
		'id'=>"10",
		'from_step_id'=>"7",
		'to_step_id'=>"8",
		'comment'=>"!Переход с выбора домена на заполнение анкеты"
	),
	array(
		'id'=>"11",
		'from_step_id'=>"8",
		'to_step_id'=>"9",
		'comment'=>"Переход с анкеты на ожидание готовности сайта"
	),
	array(
		'id'=>"12",
		'from_step_id'=>"9",
		'to_step_id'=>"10",
		'comment'=>"Переход с ожидания готовности, на готовый сайт"
	),
	array(
		'id'=>"13",
		'from_step_id'=>"3",
		'to_step_id'=>"11",
		'comment'=>"Переход с выбора способа оплаты на заполнение формы Qiwi"
	),
	array(
		'id'=>"14",
		'from_step_id'=>"11",
		'to_step_id'=>"12",
		'comment'=>"Переход с формы Qiwi на оплату в Qiwi"
	),
	array(
		'id'=>"15",
		'from_step_id'=>"11",
		'to_step_id'=>"3",
		'comment'=>"Возврат с формы ввода Qiwi на выбор способа оплаты"
	),
	array(
		'id'=>"16",
		'from_step_id'=>"6",
		'to_step_id'=>"7",
		'comment'=>"!Переход с печати договора на заполнение заявки на домен"
	),
	array(
		'id'=>"17",
		'from_step_id'=>"4",
		'to_step_id'=>"7",
		'comment'=>"!Переход с формы печати квитанции на заявку на домен"
	),
	array(
		'id'=>"18",
		'from_step_id'=>"7",
		'to_step_id'=>"6",
		'comment'=>"Переход с заявки на домен на печать договора"
	),
	array(
		'id'=>"19",
		'from_step_id'=>"7",
		'to_step_id'=>"4",
		'comment'=>"Переход с заявки на домен на печать квитанции"
	),
	array(
		'id'=>"20",
		'from_step_id'=>"7",
		'to_step_id'=>"12",
		'comment'=>"Переход с заявки на домен на оплату в Qiwi"
	),
	array(
		'id'=>"21",
		'from_step_id'=>"12",
		'to_step_id'=>"7",
		'comment'=>"!Переход с оплаты Qiwi на заявку на домен"
	),
	array(
		'id'=>"22",
		'from_step_id'=>"12",
		'to_step_id'=>"11",
		'comment'=>"Возврат с проверки данных Qiwi на форму ввода данных Qiwi"
	),
	array(
		'id'=>"23",
		'from_step_id'=>"3",
		'to_step_id'=>"13",
		'comment'=>"Переход с выбора способа оплаты на оплату через КупиВКредит"
	),
	array(
		'id'=>"24",
		'from_step_id'=>"13",
		'to_step_id'=>"3",
		'comment'=>"Переход с КупиВКредит на выбор способа оплаты"
	),
	array(
		'id'=>"25",
		'from_step_id'=>"13",
		'to_step_id'=>"7",
		'comment'=>"Переход с КупиВКредит на заявку на домен"
	),
	array(
		'id'=>"26",
		'from_step_id'=>"3",
		'to_step_id'=>"14",
		'comment'=>"Переход со способа оплаты на оплату в Robokassa"
	),
	array(
		'id'=>"27",
		'from_step_id'=>"14",
		'to_step_id'=>"3",
		'comment'=>"Возврат с оплаты Robokassa на способы оплаты"
	),
	array(
		'id'=>"28",
		'from_step_id'=>"14",
		'to_step_id'=>"7",
		'comment'=>"!Переход с оплаты Robokassa на заявку на доменное имя"
	),
	array(
		'id'=>"29",
		'from_step_id'=>"7",
		'to_step_id'=>"3",
		'comment'=>"!!Возврат с заявки на домен на выбор способа оплаты (похоже для оплаты в Кредит)"
	),
	array(
		'id'=>"30",
		'from_step_id'=>"7",
		'to_step_id'=>"14",
		'comment'=>"!Возврат с заявки на домен на оплату в Robokassa"
	),
	array(
		'id'=>"31",
		'from_step_id'=>"8",
		'to_step_id'=>"7",
		'comment'=>"!Возврат с анкеты на заявку на домен"
	),
	array(
		'id'=>"32",
		'from_step_id'=>"6",
		'to_step_id'=>"8",
		'comment'=>"Переход с договора на анкету"
	),
	array(
		'id'=>"33",
		'from_step_id'=>"8",
		'to_step_id'=>"12",
		'comment'=>"Переход с анкеты на оплату Qiwi"
	),
	array(
		'id'=>"34",
		'from_step_id'=>"12",
		'to_step_id'=>"8",
		'comment'=>"Переход с оплаты Qiwi на анкету"
	),
	array(
		'id'=>"35",
		'from_step_id'=>"4",
		'to_step_id'=>"8",
		'comment'=>"Переход с печати квитанции на заполнение анкеты"
	),
	array(
		'id'=>"36",
		'from_step_id'=>"8",
		'to_step_id'=>"4",
		'comment'=>"Возврат с анкеты на печать квитанции"
	),
	array(
		'id'=>"37",
		'from_step_id'=>"8",
		'to_step_id'=>"6",
		'comment'=>"Возврат с анкеты на печать договора"
	),
	array(
		'id'=>"38",
		'from_step_id'=>"8",
		'to_step_id'=>"3",
		'comment'=>"Переход с анкеты на выбор способа оплаты"
	),
	array(
		'id'=>"39",
		'from_step_id'=>"13",
		'to_step_id'=>"8",
		'comment'=>"Переход со служебного кредитования на анкету"
	),
	array(
		'id'=>"40",
		'from_step_id'=>"14",
		'to_step_id'=>"8",
		'comment'=>"Переход с оплаты в робокассе на анкету"
	),
	array(
		'id'=>"41",
		'from_step_id'=>"8",
		'to_step_id'=>"14",
		'comment'=>"Переход с анкеты на оплату в Робокассе"
	),
);

