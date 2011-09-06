/* 
 * Работа с редмайном, что в общем и не удивительно.
 */
function redmineSendMessage(issue_id, pack_id, serv_id){
	$('body').css('cursor', 'wait');
	var message = $('#redmineMessageInput' + issue_id).val();
	$.ajax({
		type: 'POST',
		url: '/package/addRedmineMessage',
		data: {
			'issue_id': issue_id,
			'pack_id': pack_id,
			'serv_id': serv_id,
			'message': message
		},
		dataType: 'html',
		success: function(data){
			$('body').css('cursor', 'default');
			$('#tabContent' + serv_id + ' #redmineIssue').replaceWith(data);
		},
		error: function(){
			$('body').css('cursor', 'default');
			alert('Возникла ошибка!');
		}
	});
}

/*
 * Привязываем задачу из редмайна
 */
function bindRedmineIssue(pack_id, serv_id){
	$('body').css('cursor', 'wait');
	var issue_id = $('#input' + serv_id).val();
	$.ajax({
		url: '/package/bindRedmineIssue',
		data: {
			'issue_id': issue_id,
			'pack_id': pack_id,
			'serv_id': serv_id
		},
		dataType: 'html',
		success: function(data){
			$('body').css('cursor', 'default');
			$('#tabContent' + serv_id + ' #redmineIssue').replaceWith(data);
		},
		error: function(){
			$('body').css('cursor', 'default');
			alert('Возникла ошибка!');
		}
	});
}

/*
 * Создаём новую задачу в редмайне.
 */
function newRedmineIssue(pack_id, serv_id){
	$('body').css('cursor', 'wait');
	// id мастера на текущей вкладке, иначе id менеджера на главной
	var master_id = $('#tabContent' + serv_id + ' select').val() || $('#tabContent0 select').val();
	$.ajax({
		url: '/package/newRedmineIssue',
		data: {
			'pack_id': pack_id,
			'serv_id': serv_id,
			'master_id': master_id
		},
		dataType: 'html',
		success: function(data){
			$('body').css('cursor', 'default');
			$('#tabContent' + serv_id + ' #redmineIssue').replaceWith(data);
		},
		error: function(){
			$('body').css('cursor', 'default');
			alert('Возникла ошибка!');
		}
	});
}

/*
 * Создаём все задачи в редмайне.
 */
function createAllRedmineIssues(package_id, ulid){
	$('#modal').fadeIn(0);
	$.ajax({
		url: '/package/createAllRedmineIssues/' + package_id,
		dataType: 'html',
		success: function(data){
			$('#ul' + ulid).replaceWith(data);
			$('#modal').fadeOut(0);
			flagsUpdate();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#modal').fadeOut(0);
			alert('Возникла ошибка!');
		}
	});
}

function redmineCloseIssue(issue_id, pack_id, serv_id){
	$('body').css('cursor', 'wait');
	$.ajax({
		url: '/package/closeRedmineIssue',
		data: {
			'issue_id': issue_id,
			'pack_id': pack_id,
			'serv_id': serv_id
		},
		dataType: 'html',
		success: function(data){
			$('body').css('cursor', 'default');
			$('#tabContent' + serv_id + ' #redmineIssue').replaceWith(data);
		},
		error: function(){
			$('body').css('cursor', 'default');
			alert('Возникла ошибка!');
		}
	});
}
