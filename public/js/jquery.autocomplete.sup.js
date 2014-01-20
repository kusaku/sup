/**
 * немного видоизменяем автокомплит
 * 
 * @param {Object} d.ui.autocomplete.prototype
 */
(function(d){
	d.extend(d.ui.autocomplete.prototype, {
		_renderItem: function(a, b){
			return d("<li><img src='/images/plus.png' onClick='Package(0, " + b.id + ")' title='Создать новый заказ'><img src='/images/list.png' onClick='clientCard(" + b.id + ")' title='Карточка клиента'></li>").data("item.autocomplete", b).append(d("<a></a>").text(b.label)).appendTo(a)
		}
	});
})(jQuery);
