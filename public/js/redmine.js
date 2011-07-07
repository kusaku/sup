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

function bindRedmineIssue(pack_id, serv_id){
	issue_id = $('#input'+serv_id).val();
	$('body').css('cursor','wait');
	$.ajax({
		url: '/package/bindRedmineIssue',
		data: {
			'issue_id':	issue_id,
			'pack_id':	pack_id,
			'serv_id':	serv_id
		},
		dataType: 'html',
		success: function(data){
			$('body').css('cursor','default');
			if (data == 1){
				$('#tabContent'+serv_id).html('Связывание прошло успено! При следующем открытии вы увидите все сообщения из Redmine по задаче.');
			}
		}
	});
}