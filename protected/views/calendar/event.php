<div class="event">
	<div class="date">
		<?=date("d-m-Y",strtotime($event->message))?>
	</div>
	<div class="message">
		<?=$event->message?>
	</div>
	<div class="buttons">
		<input type="button" value="Close" class="eventCloseButton">
	</div>
</div>