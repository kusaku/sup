<div class="event" event_id="<?=$event->id?>" id="event<?=$event->id?>">
	<div class="date">
		<?=date("d-m-Y",strtotime($event->date))?>
	</div>
	<div class="message">
		<?=$event->message?>
	</div>
	<div class="buttons">
		<a class="orangeButton eventReadyButton">Готово!</a>
		<a class="grayButton eventCloseButton">Скрыть</a>
	</div>
</div>