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
		Тут информация о системе СУП. Не очень много, но и не три слова. Описание основных функций, возможностей, интересных решений. И вообще...<br>
		


		<hr>
		<a href="http://www.YiiFramework.com/" target="_blanc"><img src="/images/yii-powered.png"></a> - Yii PHP Framework<br>
		<a href="http://www.jQuery.com/" target="_blanc"><img src="/images/jquery-powered.png"></a> - jQuery JS Framework<br>
		<a href="http://twitter.com/All4DK/" target="_blanc"><img src="/images/all4dk-powered.png"></a> - Krivchikov Dmitriy
	</div>
	<div class="buttons" style="text-align: center;">
		<a class="buttonCancel" onClick="hidePopUp()"></a>
	</div>
</div>