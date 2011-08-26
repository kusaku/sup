<div class="newClientWindow" style="margin-bottom: 5px;">
	<form action="/" onSubmit="addPayment(<?=$package_id?>, <?=$liid?>, $('#pay_summ').val(), $('#pay_description').val(), $('#pay_noReporting').attr('checked')); return false;">
		<div class="clientHead">
			<input type="submit" style="height: 0; width: 0;">Информация о платеже
		</div>
		<span name="payment" id="payment">
			<label for="descr">
				Плательщик:
			</label>
			<input type="text" value="" name="descr" id="pay_description" />&nbsp;<br>

			<label for="summa">
				Сумма<span class="orange">*</span>:
			</label>
			<input type="text" value="<?=$summ?>" name="summa" id="pay_summ">&nbsp;<br>
		</span>
		<div class="buttons">
			<p>

				<input type="checkbox" id="pay_noReporting"> <span style="color: #818181;">скрыть в отчёте</span><br>
				<br>
				<span class="orange">* - обязательные поля</span>
			</p>
			<a onClick="$('#payment').submit();" class="buttonSave">Сохранить</a>
			<a class="buttonCancel" onClick="hidePopUp();">Отмена</a>
		</div>
	</form>
</div>