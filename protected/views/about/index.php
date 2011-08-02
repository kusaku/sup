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
		В основе СУП лежит лежит система PR, явившаяся прототипом и донором БД. В данный момент это основной инструмент менеджеров в повседневной работе.<br>
		


		<hr>
		<a href="http://www.YiiFramework.com/" target="_blanc"><img src="/images/yii-powered.png" title="Yii PHP Framework"></a>
		<a href="http://www.jQuery.com/" target="_blanc"><img src="/images/jquery-powered.png" title="jQuery JS Framework"></a>
		<a href="http://twitter.com/All4DK/" target="_blanc"><img src="/images/all4dk.png" title="Krivchikov Dmitriy"></a>
		<a href="http://twitter.com/isanybodyhere/" target="_blanc"><img src="/images/aks.png" title="Arhipenko Kirill"></a>
	</div>
	<div class="buttons" style="text-align: center;">
		<a class="buttonCancel" onClick="hidePopUp()"></a>
	</div>
</div>