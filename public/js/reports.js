/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

function createReport(){
	$('body').css('cursor','wait');
	$.ajax({
		url: '/report',
		data: {
			'id': 0
		},
		dataType: 'html',
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp(); // Окно сформировано - показываем его
			$('body').css('cursor','default');
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').text(textStatus);
			showPopUp(); // Окно сформировано - показываем его
			$('body').css('cursor','default');
		}
	});
}
