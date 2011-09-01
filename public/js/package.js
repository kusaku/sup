/*	
 * Показываем форму заказа.
 * Может быть новый заказ для клиента, а может и существующий на редактирование
 */
function Package(package_id, client_id){
	$('body').css('cursor', 'wait');
	$('.ui-widget-content').hide();
	showPopUpLoader();
	$("#searchClient").val('');
	$.ajax({
		url: '/package/' + package_id,
		dataType: 'html',
		data: {
			'package_id': package_id,
			'client_id': client_id
		},
		success: function(data){
			$('#clients').val("");
			$("#buttonClear").addClass('hidden');
			$('#sup_popup').html(data);			
			showPopUp();
			//$('#sup_popup textarea').wysiwyg();
			$('body').css('cursor', 'default');
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').text(textStatus);
		}
	});
}

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
 * Создаём новый хост.
 */
function loadNewSite(){
	$.ajax({
		url: '/site/0',
		dataType: 'html',
		data: {
			'no_button': true
		},
		success: function(data){
			// разрушение селектбоксов 
			//$('#site_selector select').selectBox('destroy');
			$('#site_selector').html(data);
			prepareHtml();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#site_selector').text(textStatus);
		}
	});
}

/*
 *	Сохранение заказа. Это новый или не оплаченный заказ.
 */
function packSave(){
	saveAndProceed('#sup_popup form', function(data){
		hidePopUp();
		loadData(0);
	})
}

/*
 *	Обновление заказа. Это оплаченный заказ - привязываем сайт или меняем менеджера.
 */
function packUpdate(){
	var error = false;
	$('.redmineMessage').each(function(){
		if ($(this).val() != '') {
			error = true;
		}
	});
	
	if (error) 
		if (!confirm("Есть не сохранённые сообщения! \nOK - Вернуться к редактированию заказа? \nОтмена - сохранить изменения проекта, но потерять сообщения!")) {
			error = false;
		}
	
	saveAndProceed('#sup_popup form', function(data){
		hidePopUp();
		loadData(0);
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
