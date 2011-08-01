/**
 * @author aks
 */
/**
 * Список доступных отчетов
 * @param {Object} client_id
 */
function selectReportType(){
	showPopUpLoader();
	$.ajax({
		url: '/report/index',
		dataType: 'html',
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp();
			$( "#sup_popup .datepicker" ).datepicker({
				showOtherMonths: true,
				selectOtherMonths: true,
				dateFormat: "yy-mm-dd",
				changeMonth: true,
				changeYear: true
			});						
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').text(textStatus);
			showPopUp();
		}
	});
}

function generateReport(){
	showPopUpLoader();
	$.ajax({
		url: '/report/generate',
		data: {
			'reportType': $('#reportType').val(),
			'manager_id': $('#manager_id').val()
		},
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
