/**
 * Функция обеспечивает вывод окна с подробной информацией о заказах
 * @param {Object} mode
 * @param {Object} type
 * @param {Object} period
 * @param {Object} till
 */
function DashboardFullInfo(mode,type,period,till,people_id) {
	showPopUpLoader();
	$.ajax({
		url: '/manager/dashboard/details',
		dataType: 'html',
		data: {
			'mode':mode,
			'type':type,
			'period':period,
			'till':till,
			'people_id':people_id
		},
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').html($('<div style="width:300px;"/>').append($('<h1/>').text(jqXHR.statusText + ':' + jqXHR.status)).append(jqXHR.responseXML ? $('<div/>').html(jqXHR.responseXML) : $('<div/>').text(jqXHR.responseText)).html());
			showPopUp();
		}
	});
}

function dashboardReady() {
	$('#sup_preloader, #modal').hide();
	$('.tabButtons li').click(function() {
		//Сначала все скрываем, потом выводим нужное
		$(this).parents('.tabButtons').parent().find('.tabsContent > li').hide();
		$('.tabsContent #' + $(this).attr('rel')).fadeIn();
		//Сначала все деактивируем, потом выводим нужное
		$(this).parents('.tabButtons').find('li').removeClass('active');
		$(this).addClass('active');
		return false;
	});
	$('.tabButtons li:first').click();
	$('input.datapicker').datepicker({
		showOtherMonths : true,
		selectOtherMonths : true,
		dateFormat : "yy-mm-dd",
		changeMonth : true,
		changeYear : true
	});

	$('.dashTools form').bind('submit', function(event) {
		event.stopPropagation();
		var url = $(this).attr('action') + '?' + $(this).serialize();
		$('.tabscontainer.projects').tabs('url', 1, url).tabs('load', 1);
		showPopUpLoader();
		return false;
	});

	$('.dashTools a').bind('click', function(event) {
		event.stopPropagation();
		if ($(this).attr('rel') == 'choose') {
			$(this).siblings('input[name=period]').removeClass('hidden').val('');
		} else {
			$(this).siblings('input[name=period]').addClass('hidden').val($(this).attr('rel'));
		}
		$(this).addClass('selected').siblings('a.selected').removeClass('selected');
		return false;
	});
	$('.tablesorter').tablesorter({ 
		headers: { 
			2: {sorter:'myprice'},
			4: {sorter:'myprice'},
			6: {sorter:'myprice'},
			8: {sorter:'myprice'},
		} 
	});
}

$(document).ready(function() {
	
	$.tablesorter.addParser({ 
		// set a unique id 
		id: 'myprice', 
		is: function(s) { 
			// return false so this parser is not auto detected 
			return false; 
		}, 
		format: function(s) { 
			// format your data for normalization 
			var val=parseFloat(s.toLowerCase().replace(/ /g,'').replace(/,/g,'.'));
			console.log(val)
			return val 
		}, 
		// set type, either numeric or text 
		type: 'numeric' 
	}); 
}); 