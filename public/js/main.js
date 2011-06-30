/*	Автокомплит - поиск на главной странице.
 */
$(function(){
    $("#searchClient").autocomplete({
        source: "/people/GlobalSearchJSON",
        minLength: 3,
        select: function(event, ui){
            $("#buttonClear").removeClass('hidden');
            clientCard(ui.item.id);
            //loadData(ui.item.id);
        }
    });
	// считаем сумму при изменении опций в пакете
	$('input.cbox').live('change', function(){
		sumka();
	});
    prepareHtml();
});

/* 
 * Автоматическая загрузка - выполнится как загрузится страница.
 */
$(document).ready(function(){
    $('#sup_popup').hide(0); // Прячем всплывающее окно
    $('#sup_preloader').hide(0); // Прячем preloader
    $('#modal').hide(0); // И фон всплывающего окна
    $("#buttonClear").addClass('hidden'); // Прячем кнопку очистки поиска
    loadData(); // Загружаем заказы на главную страницу
	loadCalendar();
	$.datepicker.setDefaults( $.datepicker.regional[ "ru" ] ); // Устанавливаем локаль для календаря
});

/*
 * Подготовка динимаческого html при его загрузке и изменении
 */
function prepareHtml(){
    $('select').selectBox();
    $('input[type="checkbox"], input[type="radio"]').radiocheckBox();
    $('.accordion').accordion({
        active: false,
        autoHeight: false,
        collapsible: true
    });
    $('#sup_popup').draggable({
        handle: '.clientHead'
    });
}


/*	Очистка результатов поиска. Убираем введённое значение и прячем кнопку очистки.
 */
function searchClear(){
    $("#buttonClear").addClass('hidden');
    $("#searchClient").val('');
    loadData();
}


/*	Показываем форму заказа.
 Может быть новый заказ для клиента, а может и существующий на редактирование
 */
function Package(package_id, client_id){
    $('.ui-widget-content').hide();
    hidePopUp();
    showPopUpLoader();
    $("#searchClient").val('');
    $.ajax({
        url: '/package/' + package_id,
        dataType: 'html',
        data: {
            'package_id': package_id,
            'client_id': client_id
        },
        success: function(data){
            $('#clients').val("");
            $("#buttonClear").addClass('hidden');
            $('#sup_popup').html(data);
            showPopUp();
        }
    });
};


/* Загружаем данные для главной страницы.
 */
function loadData(client_id){
    $('#sup_content').html('<div id="preloader"></div>');
    $.ajax({
        url: '/package',
        data: {
            'client_id': client_id
        },
        dataType: 'html',
        success: function(data){
            $('#sup_content').html(data);
            
            // скрываем заказы клиента - все кроме первого
            $('.forhide').hide(0);
            flagsUpdate();
        }
    });
};


/* Показываем всплвающее окно.
 */
function showPopUp(){
    $('#modal').fadeIn(0); // 200
    $('#sup_preloader').hide(0); // Прячем preloader
    $('#sup_popup').show(0);
    $('#sup_popup').html($('#sup_popup').html() + '<a id="popup_close" onClick="hidePopUp()"></a>');
    
    var left = Math.round($(document).width() / 2) - Math.round($('#sup_popup').width() / 2);
    if (left < 1) 
        left = 0;
    var top = Math.round($(window).height() / 2) - Math.round($('#sup_popup').height() / 2);
    if (top < 1) 
        top = 0;
    
    $('#sup_popup').css('left', left + 'px');
    $('#sup_popup').css('top', top + 'px');
    
    prepareHtml();
};


/* Прячем всплвающее окно.
 */
function hidePopUp(){
    $('#sup_popup').fadeOut(0);
    $('#sup_preloader').hide(0); // Прячем preloader
    $('#modal').fadeOut(0); // 200
};

/* Показываем всплвающее окно.
 */
function showPopUpLoader(){
    $('#modal').fadeIn(0);
    $('#sup_preloader').show(0);
    
    var left = Math.round($(document).width() / 2) - Math.round($('#sup_preloader').width() / 2);
    if (left < 1) 
        left = 0;
    var top = Math.round($(window).height() / 2) - Math.round($('#sup_preloader').height() / 2);
    if (top < 1) 
        top = 0;
    
    $('#sup_preloader').css('left', left + 'px');
    $('#sup_preloader').css('top', top + 'px');
};

/*	Сворачиваем/разворачиваем заказы клиетна на главной странице.
 Фактически прячем/показываем все заказы кроме первого.
 */
function ShowHide(id){
    $('#' + id).toggleClass('less');
    $('#' + id).children('.forhide').toggle(0); // 150
}

/*	Сворачиваем/разворачиваем блок сайта в карточке клиента.
 */
function CardShowHide(id){
    $('#orderBlock' + id).toggleClass('open');
    $('#orderBlock' + id).children('.orderPart').toggleClass('hidden'); // 150
}

/*	Считаем сумму заказа.
 */
function sumka(){
    var sum = 0;
    $(".cbox").each(function(){
        if ($(this).attr('checked') == true) {
            var price = $('#price' + $(this).val()).val();
            var count = $('#count' + $(this).val()).val();
            var res = price * count;
            sum = sum + res;
        }
    });
    $("#pack_summa").val(sum);
    $("#summa").html(sum + ' руб.');
};


/*	Подгружаем список сайтов этого клиента.
 */
function loadSites(client_id, selected){
    $.ajax({
        url: '/site/getlist',
        dataType: 'html',
        data: {
            'client_id': client_id,
            'selected': selected,
        },
        success: function(data){
            $('#site_selector').html(data);
			prepareHtml();
        }
    });
};


/*	Создаём новый хост.
 */
function loadNewSite(){
    $.ajax({
        url: '/site/0',
        dataType: 'html',
        data: {
            'no_button': true
        },
        success: function(data){
            $('#site_selector').html(data);
			prepareHtml();
        }
    });
};


/*	Отмечаем заказ как оплаченный.
 */
function addPay(package_id, liid){
    if (package_id != null) {
        var msg = 'Подробности платежа';
        var message = prompt("Провести оплату заказа #" + package_id + "?", msg);
        if (message != null) // Нажали ОК
        {
            if (message == msg) 
                message = ""; // Ели ничего не ввели, то сообщение очищаем
            $.ajax({
                url: '/package/addpay/' + package_id,
                dataType: 'html',
                data: {
                    'message': message,
                },
                success: function(data){
                    $('#li' + liid).replaceWith(data);
                    flagsUpdate();
                }
            });
        }
    }
}


/*	Берём новый заказ себе.
 */
function takePack(package_id, liid){
    if (package_id != null) {
        $.ajax({
            url: '/package/takepack/' + package_id,
            dataType: 'html',
            success: function(data){
                $('#li' + liid).replaceWith(data);
                flagsUpdate();
            }
        });
    }
}


/*	Добавляем нового клиента.
 */
function addEditClient(id, parent){
    showPopUpLoader();
    $.ajax({
        url: '/people/' + id,
        dataType: 'html',
        data: {
            'parent': parent
        },
        success: function(data){
            $('#sup_popup').html(data);
            showPopUp(); // Окно сформировано - показываем его
        }
    });
}


/*	Редактируем сайт (домен)
 */
function editDomain(id, client_id){
    showPopUpLoader();
    $.ajax({
        url: '/site/' + id,
        dataType: 'html',
        data: {
            'client_id': client_id
        },
        success: function(data){
            $('#sup_popup').html(data);
            showPopUp(); // Окно сформировано - показываем его
        }
    });
}

function checkDomain(){
    if ($("#site_url").val() != '') 
        $.ajax({
            url: '/site/checker',
            dataType: 'html',
            data: {
                'url': $("#site_url").val()
            },
            success: function(data){
                if (data > 0) 
                    //$("#site_url").css('background-color', '#ffc6c6');
                    $("#site_url").css('background-color', '#fa0500').css('color', '#FFF');
                else 
                    //$("#site_url").css('background-color', '#c6ffca');
                    $("#site_url").css('background-color', '#009646').css('color', '#FFF');
                
                
            //$('#sup_popup').html(data);
            //showPopUp(); // Окно сформировано - показываем его
            }
        });
}


/*	Отказывамся от заказа
 */
function decline(package_id, liid){
    if (package_id != null) {
        $.ajax({
            url: '/package/decline/' + package_id,
            dataType: 'html',
            success: function(data){
                $('#li' + liid).replaceWith(data);
                flagsUpdate();
            }
        });
    }
}


/*	Обновляем значения флажков (Новые заказы, Новые события, Выполненные проекты)
 */
function flagsUpdate(){
    $('#newOrdersCount').html($('.red').size());
    $('#newEventsCount').html($('.orange').size());
    $('#doneProjectsCount').html($('.green').size());
}

/*	Карточка клиента.
 */
function clientCard(id){
    $.ajax({
        url: '/people/card/' + id,
        dataType: 'html',
        success: function(data){
            $('#sup_popup').html(data);
            showPopUp(); // Окно сформировано - показываем его
        }
    });
}

/*	About
 */
function about(){
    $.ajax({
        url: '/about/',
        dataType: 'html',
        success: function(data){
            $('#sup_popup').html(data);
            showPopUp(); // Окно сформировано - показываем его
        }
    });
}

/*	Работа с табами в окне оплаченного (выполняемого) заказа.
 */
function selectTab(id){
    $('.tab').removeClass('selected');
    $('#tab' + id).addClass('selected');
    $('.tabContent').addClass('hidden');
    $('#tabContent' + id).removeClass('hidden');
}

/* 
 * Регистрация в биллинге
 */
function bmRegister(){
    var form = $('form[name="site"]');
    var site_id = form.find('input[name="site_id"]').val();
    var login = form.find('input[name="site_bmlogin"]').val();
    var password = form.find('input[name="site_bmpassword"]').val();
    if (login && password) {
        $.ajax({
            type: 'POST',
            url: '/bm/register',
            data: {
                'site_id': site_id,
                'username': login,
                'password': password
            },
            //dataType: 'json',
            success: function(data){
                try {
                    // получаем json
                    data = jQuery.parseJSON(data)
                    // при удаче меняем ссылку
                    if (data.success) {
                        form.find('a').replaceWith('<a style="padding:5px 20px;display:block;" onclick="bmOpen()" href="#">Открыть в BM (id ' + data.userid + ')</a>');
                    }
                    else {
                        switch (data.code) {
                            // ошибка 4 - неправильной/отсутвуещее поле формы
                            case 4:
                            // поля при проверке передаются...
                            case 8:
                                if (data.msg == 'userexists') 
                                    alert('Пользователь с таким именем уже зарегистрирован')
                                break;
                            // ошибка 100 не описана, но обычно это ошибка доступа
                            case 100:
                                alert('В доступе отказано')
                                break;
                            // другие ошибки
                            default:
                            //alert(data.code + ': ' + data.msg);
                        }
                    }
                } 
                catch (e) {
                    // что-то пошло не так, json не вернулся
                }
            }
        });
    }
    else {
        !login && form.find('input[name="site_bmlogin"]').css('background-color', '#fa0500').css('color', '#FFF');
        !password && form.find('input[name="site_bmpassword"]').css('background-color', '#fa0500').css('color', '#FFF');
    }
    return false;
}

/*
 * Переход в биллинг
 */
function bmOpen(){
    var form = $('form[name="site"]');
    var site_id = form.find('input[name="site_id"]').val();
    var login = form.find('input[name="site_bmlogin"]').val();
    var password = form.find('input[name="site_bmpassword"]').val();
    if (login && password) {
        $.ajax({
            type: 'POST',
            url: '/bm/open',
            data: {
                'username': login,
                'password': password
            },
            //dataType: 'json',
            success: function(data){
                try {
                    data = jQuery.parseJSON(data)
                    if (data.success) {
                        var url = 'https://host.fabricasaitov.ru/manager/billmgr';
                        url += '?func=auth&username=' + data.username + '&key=' + data.key;
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
                    return false;
                } 
                catch (e) {
                    // что-то пошло не так, json не вернулся
                }
            }
        });
    }
    else {
        !login && form.find('input[name="site_bmlogin"]').css('background-color', '#fa0500').css('color', '#FFF');
        !password && form.find('input[name="site_bmpassword"]').css('background-color', '#fa0500').css('color', '#FFF');
    }
    return false;
}

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
                return false;
            } 
            catch (e) {
                // что-то пошло не так, json не вернулся
            }
        }
    });
    return false;
}

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
					// успешно...
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
					// успешно...
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