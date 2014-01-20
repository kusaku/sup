/*global loadCalendar: true,
		prepareHtml:true,
		positionPopUp:true,
		Package:true,
		DashboardFullInfo:true,
		CabinetWindow:true,
		DomainRequestListWindow:true,
		DomainRequestWindow:true,
		PopupWindow:true,
		PackageQuestionnaire: true,
		HashEvents:true*/
var tabs,hashHandler;
/**
 * Автоматическая загрузка - выполнится как загрузится страница.
 */
$(function(){
	loadData();
	loadCalendar();

	var obSearchClient = $("#searchClient");
	var obSearchClientInput = obSearchClient.find(".inputField");
	var obSearchClientButton = obSearchClient.find('.buttonClear');

	//	Показываем кнопку очистки поля автокомплита при незавершенном поиске
	obSearchClientInput.keyup(function() {
		if($(this).val().length > 2) {
			obSearchClientButton.removeClass('hidden');
		} else {
			obSearchClientButton.addClass('hidden');
		}
	}).autocomplete({
		// Автокомплит - поиск на главной странице.
		source : "/manager/people/search",
		minLength : 3,
		select : function(event, ui) {
			obSearchClientButton.removeClass('hidden');
			loadData(ui.item.id);
		}
	});
	// Обработчик сабмита формы
	obSearchClient.bind('submit', function(event){
		event.stopPropagation();
		loadData(obSearchClientInput.val());
		return false;
	});

	// Обработчик кнопки очистки формы
	obSearchClientButton.click(function(event){
		loadData();
		obSearchClientInput.val('');
		obSearchClientButton.addClass('hidden');
	});

	// устанавливаем локаль для календаря
	//noinspection JSUnresolvedVariable
	$.datepicker.setDefaults($.datepicker.regional.ru);

	// реализация аккордеона
	$('.supAccordion h3').live('click', function(){
		$(this).next().slideDown();
		$('.supAccordion > div').not($(this).next()).slideUp();
	});

	prepareHtml();

	tabs = $('.tabscontainer.projects').tabs();

	$(window).resize(function(){
		if ($('#sup_popup:visible').length > 0) {
			positionPopUp();
		}
	});

	hashHandler=new HashEvents();
	hashHandler.addHandler('main',processHashAction);
});

function processHashAction(arHash) {
	if (arHash.length > 0) {
		if (arHash[0] === '#cabinet' && arHash.length >= 2) {
			if (arHash.length >= 3 && /^step\d+$/i.test(arHash[2])) {
				var id = arHash[2];
				id = id.substring(4, id.length);
				var obWin = cabinet(arHash[1], function(){
					obWin.loadStepData(id);
				});
			}
			else {
				cabinet(arHash[1]);
			}
		} else if (arHash[0] === '#package' && arHash.length === 3) {
			Package(arHash[1], arHash[2]);
		} else if (arHash[0] === '#packageQuestionnaire' && arHash.length === 2) {
			PackageQuestionnaire(arHash[1]);
		} else if (arHash[0] === '#domainRequests' && arHash[1]==='package' && arHash.length==3) {
			DomainRequests(arHash[2]);
		} else if (arHash[0]=== '#domainRequest') {
			if(arHash.length===2) {
				DomainRequest(arHash[1],'',null);
			} else if(arHash.length===3) {
				DomainRequest(arHash[2],arHash[1],null);
			}
		} else if(arHash[0]==='#dashboard' && arHash.length===6) {
			DashboardFullInfo(arHash[1],arHash[2],arHash[3],arHash[4],arHash[5]);
		} else if(arHash[0]==='#partnerCard' && arHash.length===2) {
			PartnerCard(arHash[1]);
		} else if(arHash[0]==='#payments' && arHash.length===2) {
			PaymentsList(arHash[1]);
		} else if(arHash[0]==='#payment' && arHash.length===3) {
			PaymentEdit(arHash[1],arHash[2]);
		}
	}
}

/*
 * Очистка результатов поиска. Убираем введённое значение и прячем кнопку очистки.
 */
function searchClear(){
	$("#buttonClear").addClass('hidden');
	$("#searchClient").val('');
	loadData();
}

/**
 * Функция загружает скрипт управления окном ЛКК и вызывает функцию его отображения
 *
 * @param packageId int номер пакета
 * @param callback function функция, которую необходимо выопнлить при открытии окна
 *
 * @return CabinetWindow
 */
function cabinet(packageId, callback){
	//$.getScript('/js/cabinet.js',function(){
	var obCabinetWindow = new CabinetWindow(packageId, callback);
	obCabinetWindow.show();
	return obCabinetWindow;
	//cabinetWindow.init(packageId).show();
	//})
}

function contextMenuInit(){
	var hMenuItemClick;

	hMenuItemClick=function(a,el,pos) {
		return $(a).attr('target') === '_blank' || $(a).attr('rel') === 'auto';
	};

	$(".projectBox").each(function(){
		$(this).bind('contextmenu', function(e){
			e.preventDefault();
			return false;
		}).contextMenu({
			menu: $(this).find('.packageContextMenu').first()
		}, hMenuItemClick);
	});
	$(".clientInfo").each(function(){
		$(this).bind('contextmenu', function(e){
			e.preventDefault();
			return false;
		}).contextMenu({
			menu: $(this).find('.userContextMenu').first()
		}, hMenuItemClick);
	});
	$('.contextMenuButton').click(function(e){
		e.preventDefault();
		var obDiv = $(this).parentsUntil('.projectBox').parent();
		var pos = $(this).offset();
		pos.top+=$(this).outerHeight();
		obDiv.showContextMenu(pos);
	});
}

/*
 * Загружаем данные для главной страницы.
 */
function loadData(search, page){
	$('#sup_content').html('<div id="preloader"></div>');
	$.ajax({
		url: '/manager/package',
		data: {
			'search': search,
			'page': page
		},
		dataType: 'html',
		success: function(data){
			$('#sup_content').html(data);
			$("#searchClient .inputField").focus();
			$('li a.lessClick').click(function(e){
				e.preventDefault();
				$(this).parent().toggleClass('less').children('.forhide').slideToggle(300);
			});
			// скрываем заказы клиента - все кроме первого
			$('.forhide').hide(0);
			flagsUpdate();
			contextMenuInit();
			$("[data-tooltip]").mouseenter(function(e){
				hint($(this),$(this).attr("data-tooltip"),250);
			});
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_content').text(textStatus);
		}
	});
}

/*
 * Сворачиваем/разворачиваем блок сайта в карточке клиента.
 */
function CardShowHide(id){
	$('#orderBlock' + id).toggleClass('open');
	$('#orderBlock' + id).children('.orderPart').toggleClass('hidden');
	// 150
}

/*
 * Подгружаем список сайтов этого клиента.
 */
function loadSites(client_id, selected){
	$.ajax({
		url: '/manager/site/getlist',
		dataType: 'html',
		data: {
			'client_id': client_id,
			'selected': selected
		},
		success: function(data){
			// разрушение селектбоксов
			// $('#site_selector select').selectBox('destroy');
			$('#site_selector').html(data);
			prepareHtml();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#site_selector').text(textStatus);
		}
	});
}

/*
 * Создаём новый хост.
 */
function loadNewSite(){
	$.ajax({
		url: '/manager/site/0',
		dataType: 'html',
		data: {
			'no_button': true
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
 * Редактируем сайт (домен)
 */
function editDomain(id, client_id){
	showPopUpLoader();
	$.ajax({
		url: '/manager/site/' + id,
		dataType: 'html',
		data: {
			'client_id': client_id
		},
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp();
			// Окно сформировано - показываем его
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').html($('<div style="width:300px;"/>').append($('<h1/>').text(jqXHR.statusText + ':' + jqXHR.status)).append(jqXHR.responseXML ? $('<div/>').html(jqXHR.responseXML) : $('<div/>').text(jqXHR.responseText)).html());
			showPopUp();
			// Окно сформировано - показываем его
		}
	});
}

/*
 * Проверка домена на существование
 */
function checkDomain(){
	if ($("#site_url").val() != '') 
		$.ajax({
			url: '/manager/site/checker',
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
 * Обновляем значения флажков (Новые заказы, Новые события, Выполненные проекты)
 */
function flagsUpdate(){
	$('#newOrdersCount').html($('.red').size());
	$('#newEventsCount').html($('.orange').size());
	$('#doneProjectsCount').html($('.green').size());
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
			showPopUp();
			// Окно сформировано - показываем его
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').html($('<div style="width:300px;"/>').append($('<h1/>').text(jqXHR.statusText + ':' + jqXHR.status)).append(jqXHR.responseXML ? $('<div/>').html(jqXHR.responseXML) : $('<div/>').text(jqXHR.responseText)).html());
			showPopUp();
			// Окно сформировано - показываем его
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

