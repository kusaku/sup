<?php
/*	Вывод окна About
*/

?>
<div class="newClientWindow" style="margin-bottom: 5px;">
	<div class="clientHead">About "<?=Yii::app()->name['shortName']?>" ver. <?=Yii::app()->name['version']?></div>
	<div style="padding: 5px;">
		<div style="text-align: center;">
			<?=Yii::app()->name['name']?><br>
			<?=Yii::app()->name['vendor']?> 2007-<?=date('Y')?>г.
		</div><br>
		Основные возможности и функции:
		<ul>
			<li>создание и отслеживание заказов</li>
			<li>робота с клиентами</li>
			<li>взаимодействие с Redmine и BillManager</li>
			<li>информирование о приближающихся событиях</li>
			<li>работа с партнёрской программой</li>
		</ul>
		В основе СУП лежит лежит система PR, явившаяся прототипом и донором БД.<br>
		В данный момент это основной инструмент менеджеров в повседневной работе.<br>
		<hr>
<div style="overflow-y: auto; max-height: 215px;">
		<b>1.08.24 Взрывной Варан</b> - Август 2011г.<br>
		Новые возможности:
		<ul>
			<li>Отчёт по поступившим финансам</li>
			<li>Привязка сайта к оплаченной задаче</li>
			<li>Передача / взятие себе оплаченного заказа</li>
		</ul>

		Исправлено:
		<ul>
			<li>Потенциальная ошибка, способная привести к появлению 2-х задач по одной заказанной услуге</li>
		</ul>
		<!-- ******************** --><br>

		<!-- ******************** --><br>
		<b>1.08.16 Борзый Бобёр</b> - Август 2011г.<br>
		Новые возможности:
		<ul>
			<li>Создание задач с выбором мастра</li>
			<li>Закрытие задачи</li>
			<li>Логин через LDAP-сервер</li>
			<li>Новые отчёты (пока в режиме тестирования)</li>
		</ul>

		Переделано:
		<ul>
			<li>Блок информации о клиенте</li>
			<li>Верстка в ряде окон</li>
			<li>Описание задачи в Redmine</li>
			<li>Алгоритм создания новых задач</li>
		</ul>

		Исправлено:
		<ul>
			<li>Создание задач из карточки клиента</li>
			<li>Алгоритм логина с использованиес стороннего сервера</li>
		</ul>
		<!-- ******************** --><br>

		<!-- ******************** --><br>
		<b>1.08.02 Атлетичный Аист</b> - Август 2011г.<br>
		Новые возможности:
		<ul>
			<li>создание отчёта за месяц</li>
			<li>настройка оповещений, цикличные оповещения</li>
		</ul>
		
		Изменения:
		<ul>
			<li>ссылка на раскрытие/скрытие блока клиента</li>
			<li>новые кнопки управления</li>
			<li>оплата заказа (новый шаг, ведение учёта)</li>
		</ul>
		<!-- ******************** --><br>
</div>
		<hr>
		<a href="http://www.YiiFramework.com/" target="_blanc"><img src="/images/yii-powered.png" title="Yii PHP Framework"></a>
		<a href="http://www.jQuery.com/" target="_blanc"><img src="/images/jquery-powered.png" title="jQuery JS Framework"></a>
		<a href="https://github.com/All4DK" target="_blanc"><img src="/images/all4dk.png" title="Krivchikov Dmitriy"></a>
		<a href="https://github.com/aks1983" target="_blanc"><img src="/images/aks.png" title="Arhipenko Kirill"></a>
	</div>
	<div class="buttons" style="text-align: center;">
		<a class="buttonCancel" onClick="hidePopUp()"></a>
	</div>
</div>