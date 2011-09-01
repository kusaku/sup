/* 
 * Работа с редмайном, что в общем и не удивительно.
 */


function redmineSendMessage(issueId){
	$('body').css('cursor','wait');
	message = $('#redmineMessageInput'+issueId).val();
	pack = $('#redmineMessageInput'+issueId).attr('pack');
	$.ajax({
		type: 'POST',
		url: '/package/addRedmineMessage',
		data: {
			'id': issueId,
			'message': message,
			'pack': pack
		},
		dataType: 'html',
		success: function(data){
			if (data){
				$('#redmineIssue' + issueId).replaceWith(data);
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
	master_id = $('#tabContent'+serv_id+' .RedmineUserSelect').val();
	$.ajax({
		url: '/package/newRedmineIssue',
		data: {
			'pack_id':	pack_id,
			'serv_id':	serv_id,
			'master_id': master_id
		},
		dataType: 'html',
		success: function(data){
			$('body').css('cursor','default');
			if (data != 0){
				//$('#tabContent'+serv_id).html('Создание новой задачи прошло <b>успено</b>! При следующем открытии вы увидите все сообщения из Redmine по задаче.');
				$('#tabContent'+serv_id).html(data);
			} else {
				$('#tabContent'+serv_id).html($('#tabContent'+serv_id).html()+'<br>При создании новой задачи возникла <b>ошибка</b>!');
			}
		}
	});
}

/*
 * Создаём все задачи в редмайне.
 */
function createAllRedmineIssues(package_id, ulid){
	if (package_id) {
		$('#modal').fadeIn(0);
		$.ajax({
			url: '/package/createAllRedmineIssues/' + package_id,
			dataType: 'html',
			success: function(data){
				$('#ul' + ulid).replaceWith(data);
				flagsUpdate();
				$('#modal').fadeOut(0);
			},
			error: function(jqXHR, textStatus, errorThrown){				
				$('#ul' + ulid).replaceWith($('<span/>').text(textStatus));
				$('#modal').fadeOut(0);
			}
		});
	}
}

function redmineCloseIssue(issueId){
	$('body').css('cursor','wait');
	pack = $('#redmineMessageInput'+issueId).attr('pack');
	serv = $('#redmineMessageInput'+issueId).attr('serv');
	$.ajax({
		url: '/package/closeRedmineIssue',
		data: {
			'issue_id': issueId,
			'pack_id': pack,
			'serv_id': serv
		},
		dataType: 'html',
		success: function(data){
			if (data != 0){
				$('#redmineIssue' + issueId).replaceWith(data);
			}
			else{
				alert('При закрытии задачи возникла ошибка!');
			}
			$('body').css('cursor','default');
		}
	});
}