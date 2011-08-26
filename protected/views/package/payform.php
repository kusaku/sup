<div class="newClientWindow" style="margin-bottom: 5px;">
	<div class="clientHead">Информация о платеже</div>
	<span name="payment">
		<label for="descr">Плательщик:</label>
		<input type="text" value="" name="descr" id="pay_description" />
		<br/>
		<label for="summa">
			Сумма<span class="orange">*</span>:
		</label>
		<input type="text" value="<?=$summ?>" name="summa" id="pay_summ">
		<br/>
	</span>
	<div class="buttons">
		<p>
			<input type="checkbox" id="pay_noReporting">
			<span style="color: #818181;">скрыть в отчёте</span>
			<br/>
			<br/>
			<span class="orange">* - обязательные поля</span>
		</p>
		<a onClick="addPayment(<?=$package_id?>, <?=$ulid?>, $('#pay_summ').val(), $('#pay_description').val(), $('#pay_noReporting').attr('checked'));" class="buttonSave">Сохранить</a>
		<a class="buttonCancel" onClick="hidePopUp();">Отмена</a>
	</div>
</div>
