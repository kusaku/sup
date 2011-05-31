/*	Автокомплит - поиск на главной странице.
*/
$(function() {
	$( "#searchClient" ).autocomplete({
		source: "/people/GlobalSearchJSON",
		minLength: 3,
		select: function( event, ui ) {
			$("#buttonClear").removeClass('hidden');
			clientCard(ui.item.id);
			//loadData(ui.item.id);
		}
	});
});


/*	Очистка результатов поиска. Убираем введённое значение и прячем кнопку очистки.
*/
function searchClear()
{
	$("#buttonClear").addClass('hidden');
	$("#searchClient").val('');
	loadData();
}


/*	Показываем форму заказа.
	Может быть новый заказ для клиента, а может и существующий на редактирование
*/
function Package(package_id, client_id)
{
	$('.ui-widget-content').hide();
	hidePopUp();
	showPopUpLoader();
	$("#searchClient").val('');
	$.ajax({
		url:'/package/' + package_id,
		dataType: 'html',
		data:{'package_id': package_id, 'client_id': client_id},
		success: function(data){
			$('#clients').val("");
			$("#buttonClear").addClass('hidden');
			$('#sup_popup').html(data);
			showPopUp();
			checkboxinit();
		}
		});
};


/* Загружаем данные для главной страницы.
*/
function loadData(client_id)
{
	$('#sup_content').html('<div id="preloader"></div>');
	$.ajax({
		url:'/package',
		data:{'client_id': client_id},
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
function showPopUp()
{
	$('#modal').fadeIn(0); // 200
	$('#sup_preloader').hide(0); // Прячем preloader
	$('#sup_popup').show(0);
	$('#sup_popup').html($('#sup_popup').html()+'<a id="popup_close" onClick="hidePopUp()"></a>');

	var left = Math.round($(document).width()/2)-Math.round($('#sup_popup').width()/2);
	if (left < 1) left = 0;
	var top = Math.round($(window).height()/2)-Math.round($('#sup_popup').height()/2);
	if (top < 1) top = 0;

	$('#sup_popup').css('left', left+'px');
	$('#sup_popup').css('top', top+'px');
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
function showPopUpLoader()
{
	$('#modal').fadeIn(0);
	$('#sup_preloader').show(0);

	var left = Math.round($(document).width()/2)-Math.round($('#sup_preloader').width()/2);
	if (left < 1) left = 0;
	var top = Math.round($(window).height()/2)-Math.round($('#sup_preloader').height()/2);
	if (top < 1) top = 0;

	$('#sup_preloader').css('left', left+'px');
	$('#sup_preloader').css('top', top+'px');
};

/*	Сворачиваем/разворачиваем заказы клиетна на главной странице.
	Фактически прячем/показываем все заказы кроме первого.
*/
function ShowHide(id)
{
	$('#'+id).toggleClass('less');
	$('#'+id).children('.forhide').toggle(0); // 150
}

/*	Сворачиваем/разворачиваем блок сайта в карточке клиента.
*/
function CardShowHide(id)
{
	$('#orderBlock'+id).toggleClass('open');
	$('#orderBlock'+id).children('.orderPart').toggleClass('hidden'); // 150
}

/*	Считаем сумму заказа.
*/
function sumka()
{
	var sum = 0;
	$(".cbox").each(function()
		{
			if ($(this).attr('checked') == true)
			{
				var price = $('#price'+$(this).val()).val();
				var count = $('#count'+$(this).val()).val();
				var res = price*count;
				sum = sum + res;
			}
		});
	$("#pack_summa").val(sum);
	$("#summa").html(sum+' руб.');
};


/*	Подгружаем список сайтов этого клиента.
*/
function loadSites(client_id, selected)
{
	$.ajax({
		url:'/site/getlist',
		dataType: 'html',
		data:{'client_id': client_id, 'selected': selected,},
		success: function(data){
			$('#site_selector').html(data);
		}
		});
};


/*	Создаём новый хост.
*/
function loadNewSite()
{
	$.ajax({
		url:'/site/0',
		dataType: 'html',
		data:{'no_button': true},
		success: function(data){
			$('#site_selector').html(data);
		}
		});
};


/*	Отмечаем заказ как оплаченный.
*/
function addPay(package_id, liid)
{
	if (package_id != null)
	{
		var msg = 'Подробности платежа';
		var message = prompt("Провести оплату заказа #"+package_id+"?", msg);
		if ( message != null ) // Нажали ОК
		{
			if (message == msg) message = ""; // Ели ничего не ввели, то сообщение очищаем

			$.ajax({
				url:'/package/addpay/'+package_id,
				dataType: 'html',
				data:{'message': message,},
				success: function(data){
					$('#li'+liid).replaceWith(data);
					flagsUpdate();
				}
				});
		}
	}
}


/*	Берём новый заказ себе.
*/
function takePack(package_id, liid)
{
	if (package_id != null)
	{
		$.ajax({
			url:'/package/takepack/'+package_id,
			dataType: 'html',
			success: function(data){
				$('#li'+liid).replaceWith(data);
				flagsUpdate();
			}
			});
	}
}


/*	Добавляем нового клиента.
*/
function addEditClient(id, parent)
{
	showPopUpLoader();
	$.ajax({
		url:'/people/'+id,
		dataType: 'html',
		data:{'parent': parent},
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp(); // Окно сформировано - показываем его
		}
	});
}


/*	Редактируем сайт (домен)
*/
function editDomain(id, client_id)
{
	showPopUpLoader();
	$.ajax({
		url:'/site/'+id,
		dataType: 'html',
		data:{'client_id': client_id},
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp(); // Окно сформировано - показываем его
		}
	});
}

function checkDomain()
{
	if ( $("#site_url").val() != '' )
	$.ajax({
		url:'/site/checker',
		dataType: 'html',
		data:{'url': $("#site_url").val()},
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
function decline(package_id, liid)
{
	if (package_id != null)
	{
		$.ajax({
			url:'/package/decline/'+package_id,
			dataType: 'html',
			success: function(data){
				$('#li'+liid).replaceWith(data);
				flagsUpdate();
			}
			});
	}
}


/*	Обновляем значения флажков (Новые заказы, Новые события, Выполненные проекты)
*/
function flagsUpdate()
{
	$('#newOrdersCount').html($('.red').size());
	$('#newEventsCount').html($('.orange').size());
	$('#doneProjectsCount').html($('.green').size());
}

/*	Карточка клиента.
*/
function clientCard(id)
{
	$.ajax({
		url:'/people/card/'+id,
		dataType: 'html',
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp(); // Окно сформировано - показываем его
		}
	});
}

/*	About
*/
function about()
{
	$.ajax({
		url:'/about/',
		dataType: 'html',
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp(); // Окно сформировано - показываем его
		}
	});
}

/*	Работа с табами в окне оплаченного (выполняемого) заказа.
*/
function selectTab(id)
{
	$('.tab').removeClass('selected');
	$('#tab'+id).addClass('selected');
	$('.tabContent').addClass('hidden');
	$('#tabContent'+id).removeClass('hidden');
}