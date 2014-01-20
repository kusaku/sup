/**
 * добавлено включение визивига (он не одолжен быть скрыть при включении)
 * @param {Object} id
 */
function selectTab(id){
	$('.tab').removeClass('selected');
	$('.tabContent').addClass('hidden');
	$('#tab' + id).addClass('selected');
	$('#tabContent' + id).removeClass('hidden');
}
