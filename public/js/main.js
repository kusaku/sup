/* 
 * Автоматическая загрузка - выполнится как загрузится страница.
 */
$(function(){
	loadData();
	loadCalendar();
	//	Показываем кнопку очистки поля автокомплита при незавершенном поиске
	$("#searchClient").keyup(function(){
		if ($(this).val().length > 2) {
			$("#buttonClear").removeClass('hidden');
		}
		else {
			$("#buttonClear").addClass('hidden');
		}
	});
	//Автокомплит - поиск на главной странице.
	$("#searchClient").autocomplete({
		source: "/people/GlobalSearchJSON",
		minLength: 3,
		select: function(event, ui){
			$("#buttonClear").removeClass('hidden');
			loadData(ui.item.id);
		}
	});
	// считаем сумму при изменении опций в пакете
	$('input.cbox').live('change', function(){
		sumka();
	});
	$.datepicker.setDefaults($.datepicker.regional["ru"]); // Устанавливаем локаль для календаря
	// реализация аккордеона
	$('.supAccordion h3').live('click', function(){
		$(this).next().slideDown();
		$('.supAccordion > div').not($(this).next()).slideUp();
	});
	prepareHtml();
});

/*
 * Прячем попап при нажатии Esc.
 */
$(document).keyup(function(e){
	if (e.keyCode == 27) {
		hidePopUp();
	}
});

/*
 * Подготовка динимаческого html при его загрузке и изменении
 */
function prepareHtml(){
	// замена стандартных элементов
	//$('select').selectBox(); // Отключено, т.к. возникают сложности
	$('input[type="checkbox"], input[type="radio"]').radiocheckBox();
	$('#sup_popup').draggable({
		handle: '.clientHead',
		containment: 'parent'
	});
}

/*	
 * Очистка результатов поиска. Убираем введённое значение и прячем кнопку очистки.
 */
function searchClear(){
	$("#buttonClear").addClass('hidden');
	$("#searchClient").val('');
	loadData();
}

/* 
 * Загружаем данные для главной страницы.
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
			$("#searchClient").focus();
			// скрываем заказы клиента - все кроме первого			
			flagsUpdate();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_content').text(textStatus);
		}
	});
}

/* 
 * Показываем всплвающее окно.
 */
function showPopUp(){
	$('#sup_popup').attr('scrollPos', $(window).scrollTop());
	$(window).bind('scroll', function(){
		$('body').animate({
			scrollTop: $('#sup_popup').attr('scrollPos')
		}, 300, function(){
			$('body').stop(true);
		});
	});
	
	$('#modal').fadeIn(0); // 200
	$('#sup_preloader').hide(0); // Прячем preloader
	$('#sup_popup').show(0);
	$('#sup_popup').html($('#sup_popup').html() + '<a id="popup_close" onClick="hidePopUp()"></a>');
	
	var left = Math.round($(document).width() / 2) - Math.round($('#sup_popup').width() / 2);
	var top = Math.round($(window).height() / 2) - Math.round($('#sup_popup').height() / 2);
	
	if (top > 10) 
		top = 10; // Так проще работать с маленьким экраном.
	$('#sup_popup').css({
		'left': left,
		'top': top
	});
	
	prepareHtml();
}

/* 
 * Прячем всплвающее окно.
 */
function hidePopUp(){
	// разрушение селектбоксов 
	//$('#sup_popup select').selectBox('destroy');
	$(window).unbind('scroll');
	$('#sup_popup').fadeOut(0);
	$('#sup_preloader').hide(0); // Прячем preloader
	$('#modal').fadeOut(0); // 200
	$('body').css('cursor', 'default');
}

/* 
 * Показываем всплвающее окно.
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
}

/*	
 * Сворачиваем/разворачиваем заказы клиетна на главной странице.
 * Фактически прячем/показываем все заказы кроме первого.
 */
function ShowHide(id){
	$('#' + id).find('li').toggleClass('less').children('.forhide').slideToggle(300);
}

/*	
 * Сворачиваем/разворачиваем блок сайта в карточке клиента.
 */
function CardShowHide(id){
	$('#orderBlock' + id).toggleClass('open');
	$('#orderBlock' + id).children('.orderPart').toggleClass('hidden'); // 150
}

/*	
 * Подгружаем список сайтов этого клиента.
 */
function loadSites(client_id, selected){
	$.ajax({
		url: '/site/getlist',
		dataType: 'html',
		data: {
			'client_id': client_id,
			'selected': selected
		},
		success: function(data){
			// разрушение селектбоксов 
			//$('#site_selector select').selectBox('destroy');
			$('#site_selector').html(data);
			prepareHtml();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#site_selector').text(textStatus);
		}
		
	});
}

/*
 * Отмечаем заказ как оплаченный.
 */
function addPayment(package_id, ulid, summa, message, noReporting){
	$('body').css('cursor', 'wait');
	if (message == undefined) 
		message = "";
	if (noReporting != 'checked') 
		noReporting = 1;
	else 
		noReporting = 0;
	
	$.ajax({
		url: '/package/addpay',
		dataType: 'html',
		method: 'POST',
		data: {
			'package_id': package_id,
			'summa': summa,
			'message': message,
			'noReporting': noReporting
		},
		success: function(data){
			$('#ul' + ulid).replaceWith(data);
			flagsUpdate();
			$('#modal').fadeOut(0);
			hidePopUp();
			$('body').css('cursor', 'default');
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#modal').fadeOut(0);
			$('#ul' + ulid).replaceWith($('<span/>').text(textStatus));
			hidePopUp();
			$('body').css('cursor', 'default');
		}
	});
}


/*	
 * Берём новый заказ себе.
 */
function takePack(package_id, ulid){
	if (package_id != null) {
		$.ajax({
			url: '/package/takepack/' + package_id,
			dataType: 'html',
			success: function(data){
				$('#ul' + ulid).replaceWith(data);
				flagsUpdate();
			},
			error: function(jqXHR, textStatus, errorThrown){
				$('#ul' + ulid).replaceWith($('<span/>').text(textStatus));
			}
		});
	}
}

/*	
 * Добавляем нового клиента.
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
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').text(textStatus);
			showPopUp(); // Окно сформировано - показываем его
		}
	});
}

/*	
 * Редактируем сайт (домен)
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
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').text(textStatus);
			showPopUp(); // Окно сформировано - показываем его
		}
	});
}

/*
 * Проверка домена на существование
 */
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

/*	
 * Отказывамся от заказа
 */
function decline(package_id, ulid){
	if (package_id != null) {
		$.ajax({
			url: '/package/decline/' + package_id,
			dataType: 'html',
			success: function(data){
				$('#ul' + ulid).replaceWith(data);
				flagsUpdate();
			},
			error: function(jqXHR, textStatus, errorThrown){
				$('#ul' + ulid).replaceWith($('<span/>').text(textStatus));
			}
		});
	}
}

/*	
 * Обновляем значения флажков (Новые заказы, Новые события, Выполненные проекты)
 */
function flagsUpdate(){
	$('#newOrdersCount').html($('.red').size());
	$('#newEventsCount').html($('.orange').size());
	$('#doneProjectsCount').html($('.green').size());
}

/*	
 * Карточка клиента.
 */
function clientCard(id){
	$.ajax({
		url: '/people/card/' + id,
		dataType: 'html',
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp(); // Окно сформировано - показываем его
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').text(textStatus);
			showPopUp(); // Окно сформировано - показываем его
		}
	});
}

/*	
 * About
 */
function about(){
	$.ajax({
		url: '/about/',
		dataType: 'html',
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp(); // Окно сформировано - показываем его
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').text(textStatus);
			showPopUp(); // Окно сформировано - показываем его
		}
	});
}

/*	
 * Работа с табами в окне оплаченного (выполняемого) заказа.
 */
function selectTab(id){
	$('.tab').removeClass('selected');
	$('.tabContent').addClass('hidden');
	$('#tab' + id).addClass('selected');
	$('#tabContent' + id).removeClass('hidden');
}

/*
 * Обработка кнопки "сохранить и продолжить"
 */
function saveAndProceed(what, where){
	var form = $(what);
	
	if (!form.length) {		
		return false;
	}
	
	var data = form.serialize();
	data += '&ajax=1';
	$.ajax({
		type: form.attr('method'),
		url: form.attr('action'),
		data: data,
		//dataType: 'json',
		success: function(data){
			try {
				where(jQuery.parseJSON(data));
			} 
			catch (e) {
				// что-то пошло не так, json не вернулся
				where({
					'success': false
				});
			}
		},
		error: function(data){
			where({
				'success': false
			});
		}
	});
	return false;
}

/*
 * Показываем форму заказа.
 * Может быть новый заказ для клиента, а может и существующий на редактирование
 */
function payForm(package_id, ulid, summ){
	$('body').css('cursor', 'wait');
	showPopUpLoader();
	$("#searchClient").val('');
	$.ajax({
		url: '/package/getpayform',
		dataType: 'html',
		data: {
			'package_id': package_id,
			'ulid': ulid,
			'summ': summ
		},
		success: function(data){
			$('#clients').val("");
			$("#buttonClear").addClass('hidden');
			$('#sup_popup').html(data);
			showPopUp();
			$('body').css('cursor', 'default');
			$('#pay_description').focus();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').text(textStatus);
		}
	});
}

/*

 * Отмечаем заказ как оплаченный. Устаревшая функция.

 */

//function addPay(package_id, ulid, summ){
//	$('#modal').fadeIn(0);
//	if (package_id != null) {
//		var msg = 'Подробности платежа';
//		var message = prompt("Провести оплату заказа #" + package_id + "?", msg);
//
//		if (message != null)
//			var summa = prompt("Оплаченная сумма", summ);
//
//		if (message != null && summa != null) {
//			$('#modal').fadeIn(0);
//			if (message == msg)
//				message = ""; // Если ничего не ввели, то сообщение очищаем
//			$.ajax({
//				url: '/package/addpay/' + package_id,
//				dataType: 'html',
//				data: {
//					'message': message,
//					'summa': summa
//				},
//				success: function(data){
//					$('#ul' + ulid).replaceWith(data);
//					flagsUpdate();
//					$('#modal').fadeOut(0);
//				},
//				error: function(jqXHR, textStatus, errorThrown){
//					$('#modal').fadeOut(0);
//					$('#ul' + ulid).replaceWith($('<span/>').text(textStatus));
//				}
//			});
//		}
//	}
//	$('#modal').fadeOut(0);
//}
