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
