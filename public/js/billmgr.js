/**
 * @author aks
 *
 * Функции для работы с контроллером BILLManager'а
 *
 * Требует библиотеки jQuery и jQuery.tipBox
 *
 */
/* 
 * Регистрация в биллинге
 */
function bmRegister(client_id){
    var client_id = client_id;
    var form = $('#sup_popup form');
    $.ajax({
        type: 'POST',
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
                    form.find('a').replaceWith('<a style="padding:5px 20px;display:block;" onclick="bmOpen(' + client_id + ')" href="#">Открыть в BM (id ' + data.cdata['user.id'] + ')</a>');
                }
                else {
                    switch (data.code) {
                        // ошибка 4 - неправильной/отсутвуещее поле формы
                        case 4:
                            var field = $('#' + data.val);
                            var msg = (field.val()) ? 'Это поле заполнено неправильно' : 'Это поле требуется для регистрации'
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
                            break;
                        // поля при проверке передаются...
                        case 8:
                            if (data.msg == 'userexists') {
                                var field = $('#username');
                                var msg = 'Пользователь с таким именем уже зарегистрирван'
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
                            break;
                        // ошибка 100 не описана, но обычно это ошибка доступа
                        case 100:
                            var field = $('#username');
                            var msg = 'В доступе отказано'
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
                            break;
                        // другие ошибки
                        default:
							var msg = 'ошибка #' + data.code + ' - ' + data.msg + ' - ' + data.val;
                    		$('#linkid-' + client_id).tipBox(msg).tipBox('show');
                    }
                }
            } 
            catch (e) {
                // что-то пошло не так, json не вернулся
            }
        }
    });
    
    return false;
}

/*
 * Переход в биллинг
 */
function bmOpen(client_id){
    $.ajax({
        type: 'POST',
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
                } else {
					var msg = 'ошибка #' + data.code + ' - ' + data.msg + ' - ' + data.val;
                    $('#linkid-' + client_id).tipBox(msg).tipBox('show');
				}
                return false;
            } 
            catch (e) {
                // что-то пошло не так, json не вернулся
            }
        }
    });
    return false;
}

/*
 * Заказ виртуального хостинга
 */
function bmVHost(site_id, package_id, service_id){
    $.ajax({
        type: 'POST',
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
                    var msg = 'ошибка #' + data.code + ' - ' + data.msg + ' - ' + data.val;
                    $('#linkid-' + package_id + '-' + service_id).tipBox(msg).tipBox('show');
                }
                return false;
            } 
            catch (e) {
                // что-то пошло не так, json не вернулся
            }
        }
    });
    return false;
}

/*
 * Заказ доменного имени
 */
function bmDomainName(site_id, package_id, service_id){
    $.ajax({
        type: 'POST',
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
                    var msg = 'ошибка #' + data.code + ' - ' + data.msg + ' - ' + data.val;
                    $('#linkid-' + package_id + '-' + service_id).tipBox(msg).tipBox('show');
                }
                return false;
            } 
            catch (e) {
                // что-то пошло не так, json не вернулся
            }
        }
    });
    return false;
}
