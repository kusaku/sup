<div class="event" event_id="<?=$event->id?>" id="event<?=$event->id?>">
	<div class="date">
		<?=date("d-m-Y",strtotime($event->date))?>
		<?php if ($event->interval):?>
		(каждые <b><?=$event->interval?></b> мес.)
		<?php endif;?>
		<img src="/images/settings.png" onClick="editCalendarEvent(<?=$event->id?>);" style="float: right; cursor: pointer;">
	</div>
	<div class="message">
		<?=$event->message?>
	</div>
	<div class="buttons">
		<a class="orangeButton eventReadyButton">Готово!</a>
		<a class="grayButton eventCloseButton">Скрыть</a>
	</div>
</div>