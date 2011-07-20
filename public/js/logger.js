/**
 * @author aks
 */
function loggerForm(client_id){
	showPopUpLoader();
	$.ajax({
		url: '/logger/index',
		data: {
			'client_id': client_id
		},
		dataType: 'html',
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp();
			$('#sup_popup textarea').wysiwyg();
		}
	});
};
