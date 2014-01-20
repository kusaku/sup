/**
 * Ted contlol for irina.l <3
 */

$(function(){
	var ted = $('#ted');
	
	ted.bind('dblclick', function(event){
		ted.css({
			'left': 'inherit'
		});
		ted.animate({
			right: '-383px'
		}, 1000, function(){
			ted.tipBox().tipBox('show');
			var msgs = ['окей, пока...', 'я тебе не нужен, да? (((', 'ты даже не хочешь попрощаться?'];
			var msg = msgs[Math.round(Math.random() * Math.random() * msgs.length)];
			ted.tipBox(msg).tipBox('show')
		});
	}).bind('mouseup', function(event){
		var msgs = ['гы-гы, щекотно!', 'о да!...', 'слушай, а ты массажистом не пробовала работать?', 'у тебя такие нежные руки!', 'зря ты меня трогаешь за это место!', 'продолжай, мне нравится! ммм...'];
		var msg = msgs[Math.round(Math.random() * msgs.length)];
		ted.tipBox(msg).tipBox('show')
	});
	
	ted.draggable({
		handle: '*',
		//containment: 'parent',
		stop: function(event, ui){
			event.stopPropagation();
			var msgs = ['возможно, мне тут лучше...', 'да, мне отсюда лучше видно твои глаза...', 'не так быстро!', 'эй! полегче, детка!'];
			var msg = msgs[Math.round(Math.random() * Math.random() * msgs.length)];
			ted.tipBox(msg).tipBox('show')
		}
	});
	
	ted.animate({
		top: '0px'
	}, 1000, function(){
		var msgs = ['детка, превед! я твой Тед!', 'как дела?', 'привет, красотуля!', 'ну вот и я', 'я по тебе скучал, а ты?'];
		var msg = msgs[Math.round(Math.random() * Math.random() * msgs.length)];
		ted.tipBox(msg).tipBox('show');
	});
});
