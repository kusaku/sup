
/*	
 * Считаем сумму заказа.
 */
function sumka(){
	var sum = 0;
	$(".cbox").each(function(){
		if ($(this).attr('checked') == 'checked' | $(this).attr('checked') == true) {
			var price = $('#price' + $(this).val()).val();
			var count = $('#count' + $(this).val()).val();
			var res = price * count;
			sum = sum + res;
		}
	});
	$("#pack_summa").val(sum);
	$("#summa").html(sum + ' руб.');
}

/*	
 * Показываем форму заказа.
 * Может быть новый заказ для клиента, а может и существующий на редактирование
 */
function Package(package_id, client_id){
	$("#searchClient").val('');
	$('.ui-widget-content').hide();
	showPopUpLoader();	
	$.ajax({
		url: '/package/' + package_id,
		dataType: 'html',
		data: {
			'client_id': client_id
		},
		success: function(data){
			$('#clients').val('');
			$("#buttonClear").addClass('hidden');
			$('#sup_popup').html(data);
			showPopUp();
			sumka();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').text(textStatus);
			showPopUp();			
		}
	});
}

/*
 *	Сохранение заказа. Это новый или не оплаченный заказ.
 */
function packSave(client_id){
	saveAndProceed('#sup_popup form', function(data){
		hidePopUp();
		loadData();
	})
}

/*
 *	Обновление заказа. Это оплаченный заказ - привязываем сайт или меняем менеджера.
 */
function packUpdate(client_id){
	var error = false;
	$('.redmineMessage').each(function(){
		if ($(this).val() != '') {
			error = true;
		}
	});
	
	if (error) 
		if (confirm("Есть неотправленные сообщения в Redmine\nOK - Не отправлять сообщения\nОтмена - Продолжить просмотр")) {
			error = false;
		}
	
	error || saveAndProceed('#sup_popup form', function(data){
		hidePopUp();
		loadData();
	})
}

/*
 *	По заказу выполнены все работы. Закрываем все задачи.
 */
function packageIsReady(package_id, ulid){
	$('#modal').fadeIn(0);
	if (package_id != null) {
		$.ajax({
			url: '/package/packageIsReady/' + package_id,
			dataType: 'html',
			success: function(data){
				$('#ul' + ulid).replaceWith(data);
				flagsUpdate();
				$('#modal').fadeOut(0);
			},
			error: function(jqXHR, textStatus, errorThrown){
				$('#modal').fadeOut(0);
				$('#ul' + ulid).replaceWith($('<span/>').text(textStatus));
			}
		});
	}
	$('#modal').fadeOut(0);
}
