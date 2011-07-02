<div class="newClientWindow" style="margin-bottom: 5px;">
    <div class="clientHead">
        Событие с напоминанием.
    </div>
    <form action="/calendar/save" method="POST" name="calendar">
        <input type="hidden" name="event_id" value="<?= $event->id; ?>">
		<input type="hidden" name="people_id" value="<?= $event->people_id; ?>">
        <label>
            Date<span class="orange">*</span>:
        </label>
        <input type="text" value="<?= $event->date;?>" size="26" name="date" id="datepicker" />
        <label>
            Message:
        </label>
        <textarea name="message" rows="3" cols="12"><?= $event->message; ?></textarea>
    </form>
    <div class="buttons">
        <p>
            <span class="orange">* - обязательные поля</span>
        </p>
        <a onClick="document.forms['calendar'].submit();" class="buttonSave">Сохранить</a>
        <a class="buttonCancel" onClick="hidePopUp();">Отмена</a>
    </div>
</div>