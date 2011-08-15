/**
 * @author aks
 */
/**
 * Список доступных шаблонов для отправки письма клиенту
 * @param {Object} client_id
 */
function selectMailTemplate(client_id){
	showPopUpLoader();
	$.ajax({
		url: '/mail/list',
		dataType: 'html',
		data: {
			'client_id': client_id
		},
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').text(textStatus);
			showPopUp();
		}
	});
}

/**
 *
 */
function EditMailTemplates(){
	hidePopUp();
	showPopUpLoader();
	$.ajax({
		url: '/mail/index',
		dataType: 'html',
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp();
			$('#sup_popup textarea').wysiwyg();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').text(textStatus);
			showPopUp();
		}
	});
};

/**
 *
 */
function SendMail(){
	$.ajax({
		url: '/mail/send',
		dataType: 'html',
		data: {
			'client_id': $('#client_id').val(),
			'template_id': $('#template_id').val()
		},
		success: function(data){
			hidePopUp();
			alert(data);
		},
		error: function(jqXHR, textStatus, errorThrown){
			hidePopUp();
			alert(textStatus);
		}
	});
}

/**
 *
 */
function massMail(){
	hidePopUp();
	showPopUpLoader();
	$.ajax({
		url: '/mail/massmail',
		dataType: 'html',
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').text(textStatus);
			showPopUp();
		}
	});
}

/**
 *
 */
function resetQueue(){
	if ($('#sup_popup').is(':hidden')) 
		return false;
	
	$.ajax({
		type: 'GET',
		url: '/mail/resetqueue',
		//dataType: 'json',
		success: function(data){
			try {
				data = jQuery.parseJSON(data)
				if (data.success) {
					$('#stagebutton').attr('onclick', 'makeQueue()');
					makeQueue();
				}
			} 
			catch (e) {
				// что-то пошло не так, json не вернулся
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
		}
	});
	return false;
}

/**
 *
 */
function makeQueue(){
	if ($('#sup_popup').is(':hidden')) 
		return false;
	
	var filter = $('#filter').val();
	var template_id = $('#template_id').val();
	
	$.ajax({
		type: 'GET',
		url: '/mail/makequeue',
		data: {
			'filter': filter,
			'template_id': template_id
		},
		//dataType: 'json',
		success: function(data){
			try {
				data = jQuery.parseJSON(data)
				if (data.success) {
					$('#progress').css({
						'width': Math.round(100 - 100 * data.left / data.total) + '%'
					}).tipBox('Создано ' + data.done + ' из ' + data.total).tipBox('show');
					if (data.left > 0) {
						makeQueue();
					}
					else {
						$('#stagebutton').attr('onclick', 'processQueue()').text('Начать рассылку');
					}
				}
			} 
			catch (e) {
				// что-то пошло не так, json не вернулся
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
		}
	});
	return false;
}

/**
 *
 */
function processQueue(){
	if ($('#sup_popup').is(':hidden')) 
		return false;
	
	$.ajax({
		type: 'GET',
		url: '/mail/processqueue',
		//dataType: 'json',
		success: function(data){
			try {
				data = jQuery.parseJSON(data)
				if (data.success) {
					$('#progress').css({
						'width': Math.round(100 - 100 * data.left / data.total) + '%'
					}).tipBox('Осталось ' + data.left + ' из ' + data.total + ', ' + data.failed + ' c ошибкой').tipBox('show');
					if (data.left > 0) {
						processQueue()
					}
					else {
						$('#stagebutton').attr('onclick', 'hidePopUp()').text('Рассылка закончена, закрыть окно');
					}
				}
			} 
			catch (e) {
				// что-то пошло не так, json не вернулся
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
		}
	});
	return false;
}
