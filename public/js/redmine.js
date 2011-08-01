/* 
 * Работа с редмайном, что в общем и не удивительно.
 */


function redmineSendMessage(issueId){
	$('body').css('cursor','wait');
	message = $('#redmineMessageInput'+issueId).val();
	pack = $('#redmineMessageInput'+issueId).attr('pack');
	$.ajax({
		url: '/package/addRedmineMessage',
		data: {
			'id': issueId,
			'message': message,
			'pack': pack
		},
		dataType: 'html',
		success: function(data){
			if (data != 0){
				$('#redmineMessageInput'+issueId).parent().html(data);
			}
			else{
				alert('При добавлении комментария возникла ошибка!');
			}
			$('body').css('cursor','default');
		}
	});
}

/*
 * Привязываем задачу из редмайна
 */
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

/*
 * Создаём новую задачу в редмайне.
 */
function newRedmineIssue(pack_id, serv_id){
	$('body').css('cursor','wait');
	$.ajax({
		url: '/package/newRedmineIssue',
		data: {
			'pack_id':	pack_id,
			'serv_id':	serv_id
		},
		dataType: 'html',
		success: function(data){
			$('body').css('cursor','default');
			if (data != 0){
				//$('#tabContent'+serv_id).html('Создание новой задачи прошло <b>успено</b>! При следующем открытии вы увидите все сообщения из Redmine по задаче.');
				$('#tabContent'+serv_id).html(data);
			} else {
				$('#tabContent'+serv_id).html('Создание новой задачи возникла <b>ошибка</b>!');
			}
		}
	});
}

/*
 * Создаём новую задачу в редмайне.
 *
function createAllRedmineIssues(pack_id, serv_id){
	$('body').css('cursor','wait');
	$.ajax({
		url: '/package/newRedmineIssue',
		data: {
			'pack_id':	pack_id,
			'serv_id':	serv_id
		},
		dataType: 'html',
		success: function(data){
			$('body').css('cursor','default');
			if (data == 1){
				$('#tabContent'+serv_id).html('Создание новой задачи прошло <b>успено</b>! При следующем открытии вы увидите все сообщения из Redmine по задаче.');
			} else {
				$('#tabContent'+serv_id).html('Создание новой задачи возникла <b>ошибка</b>!');
			}
		}
	});
}*/