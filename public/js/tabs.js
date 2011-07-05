/*	Работа с табами в окне оплаченного (выполняемого) заказа.
 */
function selectTab(id){
    $('.tab').removeClass('selected');
    $('.tabContent').addClass('hidden');
	$('#tab' + id).addClass('selected');
    $('#tabContent' + id).removeClass('hidden');
}
