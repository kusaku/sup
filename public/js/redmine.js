/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


function redmineSendMessage(issueId){
	$('body').css('cursor','wait');
	message = $('#redmineMessageInput'+issueId).val();
	$.ajax({
		url: '/package/addRedmineMessage',
		data: {
			'id': issueId,
			'message': message
		},
		dataType: 'html',
		success: function(data){
			if (data != 0){
				$('#redmineMessageInput'+issueId).val('').before(data);
			}
			else{
				alert('При добавлении комментария возникла ошибка!');
			}
			$('body').css('cursor','default');
		}
	});
}

