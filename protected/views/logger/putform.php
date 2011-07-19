<div style="margin-bottom: 5px;" class="newClientWindow">
	<form id="megaform" action="/logger/put" method="POST">
		<div class="clientHead">Добавление отчета в журнал</div>
		<div style="padding:10px 5px;">
			<textarea style="width:550px;height:200px;" name="info"></textarea>
			<input type="hidden" id="client_id" name="client_id" value="<?=$client_id?>" />
		</div>
		<div class="buttons">
			<a class="grayButton" onclick="hidePopUp();">Отмена</a>
			<a style="float:right;" class="orangeButton" onclick="saveAndProceed('#megaform', function(success){ success && hidePopUp(); });">Добавить в журнал</a>
		</div>
	</form>
</div>
