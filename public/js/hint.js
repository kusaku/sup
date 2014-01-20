function hint(obItem,text,width) {
	function mouseOut() {
		obHint.hide();
	}
	if(!width) width=140;
	var obHint=$('#hint');
	if(obHint.length==0) {
		$('body').append('<div id="hint"><div class="hint_content"></div><div class="hint_arrow"></div>');
		obHint=$('#hint');
		obHint.mouseout(mouseOut);
	}
	obHint.css('width',width).children('.hint_content').html(text);
	var obPos=obItem.offset();
	obHint.show();
	obPos.top=obPos.top-obHint.outerHeight();
	obHint.offset(obPos).show();
	obItem.unbind('mouseout',mouseOut).mouseout(mouseOut);
}
