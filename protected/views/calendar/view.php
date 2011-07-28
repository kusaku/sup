<div class="newClientWindow" style="margin-bottom: 5px;">
    <div class="clientHead">
        Событие с напоминанием.
    </div>
    <form action="/calendar/save" method="POST" name="calendar" style="padding: 10px;">
        <input type="hidden" name="event_id" value="<?= $event->id; ?>">
		<input type="hidden" name="people_id" value="<?= $event->people_id; ?>">
        <label for="datepicker">
            Дата<span class="orange">*</span>:
        </label>
        <input type="text" value="<?= $event->date;?>" size="26" name="date" id="datepicker" /><br>
        <label for="eventMessage">
            Текст:
        </label>
        <textarea id="eventMessage" name="message" rows="3" cols="12"><?= $event->message; ?></textarea><br>

		<label for="eventEvery">
            Напоминать каждые:
        </label>
		<select name="interval" id="eventEvery">
			<option value="0" <?=$event->interval==0?'selected':''?> >Не повторять</option>
			<option value="1" <?=$event->interval==1?'selected':''?> >1 мес.</option>
			<option value="3" <?=$event->interval==3?'selected':''?> >3 мес.</option>
			<option value="6" <?=$event->interval==6?'selected':''?> >6 мес.</option>
			<option value="12" <?=$event->interval==12?'selected':''?> >12 мес.</option>
		</select>
    </form>
    <div class="buttons">
        <p>
            <span class="orange">* - обязательные поля</span>
        </p>
        <a onClick="document.forms['calendar'].submit();" class="buttonSave">Сохранить</a>
        <a class="buttonCancel" onClick="hidePopUp();">Отмена</a>
    </div>
</div>