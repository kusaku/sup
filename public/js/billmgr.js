/**
 * @author aks
 *
 * Функции для работы с контроллером BILLManager'а
 *
 * Требует библиотеки jQuery и jQuery.tipBox
 *
 */
/*
 * Сооющения об ошибках
 */
var errors = {
	4: 'Неправильное/отсутствующее поле',
	3: 'Отказано в доступе',
	8: 'Пользователь уже существует',
	9: 'Невозможно заказать',
	100: 'Отказано в доступе'
}
/* 
 * Регистрация в биллинге
 */
function bmRegister(client_id){
	var client_id = client_id;
	var form = $('#sup_popup form');
	$.ajax({
		type: 'GET',
		url: '/bm/register',
		data: {
			'client_id': client_id
		},
		//dataType: 'json',
		success: function(data){
			try {
				// получаем json
				data = jQuery.parseJSON(data)
				// при удаче меняем ссылку
				if (data.success) {
					$('#linkid-' + client_id).parent().html('<a href="#" onclick="saveAndProceed(\'#sup_popup form\', function(success){if (success) bmOpen(' + client_id + '); else $(\'#linkid-' + client_id + '\').tipBox(\'Ошибка сохранения!\').tipBox(\'show\');});" id="linkid-' + client_id + '" class="add_open"></a>Открыть BILLManager');
				}
				else {
					var msg = 'Ошибка';
					(data.code) && (msg += ' #' + data.code + ' - ' + errors[data.code]);
					(data.msg) && (msg += ' - ' + data.msg);
					(data.val) && (msg += ' (' + data.val + ')');
					
					var field = form.find('#' + (data.val ? data.val : 'notexist'));
					if (field.length) {
						field.tipBox(msg);
						var parent = field.parents(':hidden');
						if (parent.length) {
							parent.slideDown(function(){
								field.tipBox('show');
							});
							$('.supAccordion > div').not(parent).slideUp();
						}
						else {
							field.tipBox('show');
						}
					}
					else {
						$('#linkid-' + client_id).tipBox(msg).tipBox('show');
					}
				}
			} 
			catch (e) {
				// что-то пошло не так, json не вернулся
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#linkid-' + client_id).tipBox('Ошибка на стороне сервера!').tipBox('show');
		}
	});
	
	return false;
}

/*
 * Переход в биллинг
 */
function bmOpen(client_id){
	$.ajax({
		type: 'GET',
		url: '/bm/open',
		data: {
			'client_id': client_id
		},
		//dataType: 'json',
		success: function(data){
			try {
				data = jQuery.parseJSON(data)
				if (data.success) {
					var url = 'https://host.fabricasaitov.ru/manager/billmgr';
					url += '?func=auth&username=' + data.username + '&key=' + data.key;
					// "зачем эта хренотень?" - спросите Вы? дело в том, что если на сайте
					// биллинга не установлены куки, то автовход вернет ошибку. поэтому
					// мы подгружаем в iframe страничку, которая ставит куку, и затем
					// переходим по ссылке в биллинг.
					$('<iframe>').load(function(){
						/*
						 var w = window.open((urladdon) ? url + urladdon : url);
						 if (w)
						 w.location.href = '/';
						 else
						 alert('Ваш браузер блокирует всплывающие окна');
						 */
						window.location.href = url;
					}).attr('src', 'http://host.fabricasaitov.ru/setcookie.php').hide().appendTo('body');
				}
				else {
					var msg = 'Ошибка';
					(data.code) && (msg += ' #' + data.code + ' - ' + errors[data.code]);
					(data.msg) && (msg += ' - ' + data.msg);
					(data.val) && (msg += ' (' + data.val + ')');
					$('#linkid-' + client_id).tipBox(msg).tipBox('show');
				}
				return false;
			} 
			catch (e) {
				// что-то пошло не так, json не вернулся
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#linkid-' + client_id).tipBox('Ошибка на стороне сервера!').tipBox('show');
		}
	});
	return false;
}

/*
 * Заказ виртуального хостинга
 */
function bmVHost(site_id, package_id, service_id){
	$.ajax({
		type: 'GET',
		url: '/bm/ordervhost',
		data: {
			'site_id': site_id,
			'package_id': package_id,
			'service_id': service_id
		},
		//dataType: 'json',
		success: function(data){
			try {
				data = jQuery.parseJSON(data)
				if (data.success) {
					$('#linkid-' + package_id + '-' + service_id).remove();
				}
				else {
					var msg = 'Ошибка';
					(data.code) && (msg += ' #' + data.code + ' - ' + errors[data.code]);
					(data.msg) && (msg += ' - ' + data.msg);
					(data.val) && (msg += ' (' + data.val + ')');
					$('#linkid-' + package_id + '-' + service_id).tipBox(msg).tipBox('show');
				}
				return false;
			} 
			catch (e) {
				// что-то пошло не так, json не вернулся
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#linkid-' + package_id + '-' + service_id).tipBox('Ошибка на стороне сервера!').tipBox('show');
		}
	});
	return false;
}

/*
 * Заказ доменного имени
 */
function bmDomainName(site_id, package_id, service_id){
	$.ajax({
		type: 'GET',
		url: '/bm/orderdomain',
		data: {
			'site_id': site_id,
			'package_id': package_id,
			'service_id': service_id
		},
		//dataType: 'json',
		success: function(data){
			try {
				data = jQuery.parseJSON(data)
				if (data.success) {
					$('#linkid-' + package_id + '-' + service_id).remove();
				}
				else {
					var msg = 'Ошибка';
					(data.code) && (msg += ' #' + data.code + ' - ' + errors[data.code]);
					(data.msg) && (msg += ' - ' + data.msg);
					(data.val) && (msg += ' (' + data.val + ')');
					$('#linkid-' + package_id + '-' + service_id).tipBox(msg).tipBox('show');
				}
				return false;
			} 
			catch (e) {
				// что-то пошло не так, json не вернулся
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#linkid-' + package_id + '-' + service_id).tipBox('Ошибка на стороне сервера!').tipBox('show');
		}
	});
	return false;
}

/*
 * Заказ доменного имени
 */
function bmUpdateAttributes(client_id){
	$.ajax({
		type: 'GET',
		url: '/bm/updateattributes',
		data: {
			'client_id': client_id,
		},
		//dataType: 'json',
		success: function(data){
			try {
				data = jQuery.parseJSON(data)
				if (data.success) {
					$('#linkid-' + client_id).tipBox('Данные успешно подгружены.').tipBox('show');
				}
				else {
					var msg = 'Ошибка';
					(data.code) && (msg += ' #' + data.code + ' - ' + errors[data.code]);
					(data.msg) && (msg += ' - ' + data.msg);
					(data.val) && (msg += ' (' + data.val + ')');
					$('#linkid-' + client_id).tipBox(msg).tipBox('show');
				}
				return false;
			} 
			catch (e) {
				// что-то пошло не так, json не вернулся
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#linkid-' + client_id).tipBox('Ошибка на стороне сервера!').tipBox('show');
		}
	});
	return false;
}
