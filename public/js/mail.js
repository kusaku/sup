/**
 * @author aks
 */

/**
 * Список доступных шаблонов для отправки письма клиенту
 * @param {Object} client_id
 */
function SelectMailTemplate(client_id){
	hidePopUp();
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
		}
	});
}

function EditMailTemplates() {
	hidePopUp();
	showPopUpLoader();
	$.ajax({
		url: '/mail/index',
		dataType: 'html',
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp();
			$('#sup_popup textarea').wysiwyg();
		}
	});
};

function SendMail() {
	$.ajax({
		url: '/mail/send',
		dataType: 'html',
		data: {
			'client_id': $('#client_id').val(),
			'template_id': $('#template_id').val()
		},
		success: function(data){
			hidePopUp();
			$('#sup_preloader').hide(0); // Прячем preloader
			alert(data);
		}
	});	
}
