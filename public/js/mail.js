/**
 * @author aks
 */
/**
 * Список доступных шаблонов для отправки письма клиенту
 * @param {Object} client_id
 */
function selectMailTemplate(package_id){
	showPopUpLoader();
	$.ajax({
		url: '/manager/mail/list',
		dataType: 'html',
		data: {
			'package_id': package_id
		},
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').html($('<div style="width:300px;"/>').append($('<h1/>').text(jqXHR.statusText + ':' + jqXHR.status)).append(jqXHR.responseXML ? $('<div/>').html(jqXHR.responseXML) : $('<div/>').text(jqXHR.responseText)).html());
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
		url: '/manager/mail/index',
		dataType: 'html',
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp();
			$('#sup_popup textarea').wysiwyg();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').html($('<div style="width:300px;"/>').append($('<h1/>').text(jqXHR.statusText + ':' + jqXHR.status)).append(jqXHR.responseXML ? $('<div/>').html(jqXHR.responseXML) : $('<div/>').text(jqXHR.responseText)).html());
			showPopUp();
		}
	});
};

/**
 *
 */
function SendMail(){
	$.ajax({
		url: '/manager/mail/send',
		dataType: 'html',
		data: {
			'package_id': $('#package_id').val(),
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
		url: '/manager/mail/massmail',
		dataType: 'html',
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').html($('<div style="width:300px;"/>').append($('<h1/>').text(jqXHR.statusText + ':' + jqXHR.status)).append(jqXHR.responseXML ? $('<div/>').html(jqXHR.responseXML) : $('<div/>').text(jqXHR.responseText)).html());
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
		url: '/manager/mail/resetqueue',
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
		url: '/manager/mail/makequeue',
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
		url: '/manager/mail/processqueue',
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
