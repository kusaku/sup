/*global PopupWindow:true,
 hidePopUpLoader:true,
 DomainRequest:true,
 SupWindow:true,
 hint:true*/
var PackageQuestionnaireWindow;

PackageQuestionnaireWindow = function (packageId,callback,parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.packageId=packageId;
	this.isInit=true;
	this.bRequest=false;
};

PackageQuestionnaireWindow.prototype=new SupWindow();
PackageQuestionnaireWindow.prototype.packageId=0;
PackageQuestionnaireWindow.prototype.reload=function() {
	if(this.bRequest) {
		return;
	}
	var obWin=this.obPopup.body();
	var self=this;
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/package/questionnaire',
		data: {'id':self.packageId},
		dataType: 'html',
		success: function(data){
			self.bRequest=false;
			self.setContent(data);
			self.initHandlers();
		},
		error: function(jqXHR, textStatus, errorThrown){
			self.bRequest=false;
			self.close();
		}
	});
};
PackageQuestionnaireWindow.prototype.initHandlers=function() {
	if(this.bInitHandlers) {
		return;
	}
	this.obPopup._position();
	this.bInitHandlers=true;
};

/**
 * Функция отображает окно со списком анкет к заказу
 *
 * @param packageId int номер пакета анкеты связанные с которым необходимо отобразить
 * @param callback функция, которую необходимо выполнить после отображения окна
 *
 * @return {PackageQuestionnaireWindow}
 *
 * @constructor
 */
function PackageQuestionnaire(packageId,callback) {
	var obPackageQuestionnaireWindow = new PackageQuestionnaireWindow(packageId, callback);
	obPackageQuestionnaireWindow.show();
	return obPackageQuestionnaireWindow;
}

/*
 * Показываем форму заказа.
 * Может быть новый заказ для клиента, а может и существующий на редактирование
 */
function Package(package_id, client_id){
	$("#searchClient .inputField").val('');
	showPopUpLoader();
	if($('#sup_popup .content').length===0) {
		$('#sup_popup').html('<div class="content"><div style="width:300px;height:200px;background: white;"></div></div>');
	}
	$.ajax({
		url: '/manager/package/' + package_id,
		dataType: 'html',
		data: {
			'client_id': client_id
		},
		success: function(data){
			$('#clients').val('');

			$('#sup_popup .content').html(data);
			showPopUp();
			$('#sup_popup .tabscontainer.modal').tabs();
			$("#sup_popup #pack_dt_beg").datetimepicker({
				showOtherMonths: true,
				selectOtherMonths: true,
				dateFormat: "yy-mm-dd",
				timeFormat: "hh:mm:ss",
				changeMonth: true,
				changeYear: true
			});
			
			// принудительное исправление запятой на точку
			$('#sup_popup .subPart').find('input[name^=count], input[name^=price], input[name^=duration]').bind('change', function(){
				$(this).val($(this).val().replace(',', '.'));
			});
			
			// считаем сумму при изменении опций в пакете
			$('#sup_popup .subPart input').bind('change', function(){
			try {
				var summ = 0;
				var period = 0;
				$(".subPart input.cbox:checked").each(function(){
						summ += $('#price' + $(this).val()).val() * $('#count' + $(this).val()).val();
						period += $('#duration' + $(this).val()).val() * $('#count' + $(this).val()).val();
						var id = $(this).parents('.projectBlock').attr('id');
						$('.tabscontainer.modal [href="#' + id + '"] .marked').text('*');
					});
					$("#package_summ").text(summ + ' руб., ' + period + ' дн.');
				} catch (e) {
					$("#package_summ").text('ошибка!');
				}
			});
			
			$('select[name=status_id]',$('#sup_popup')).mouseenter(function(){
				hint($(this),'Статус заказа возможно менять только вперёд. Невозможно &quot;снизить&quot; статус заказа.');
			})
			$('select[name=payment_id]',$('#sup_popup')).mouseenter(function(){
				hint($(this),'Статус оплаты возможно менять только когда заказ находится в статусе <b>оплачивается</b>. Изменить статус <b>оплачивается</b> на <b>распределяется</b> возможно только когда статус оплаты станет <b>Есть платёжка</b>.',200);
			})
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').html($('<div style="width:300px;"/>').append($('<h1/>').text(jqXHR.statusText + ':' + jqXHR.status)).append(jqXHR.responseXML ? $('<div/>').html(jqXHR.responseXML) : $('<div/>').text(jqXHR.responseText)).html());
			showPopUp();
		}
	});
}

/**
 * Сохранение заказа. Это новый или не оплаченный заказ.
 * @param client_id integer
 */
function packSave(client_id){
	defaultWindow.showLoading();
	saveAndProceed('#sup_popup form', function(data){
		defaultWindow.hideLoading();
		hidePopUp();
		loadData();
	})
}

/*
 *	Обновление заказа. Это оплаченный заказ - привязываем сайт или меняем менеджера.
 */
function packUpdate(client_id){
	var error = false;
	$('.redmineMessage').each(function(){
		if ($(this).val() != '') {
			error = true;
		}
	});
	
	if (error) 
		if (confirm("Есть неотправленные сообщения в Redmine\nOK - Не отправлять сообщения\nОтмена - Продолжить просмотр")) {
			error = false;
		}
	if(!error) {
		defaultWindow.showLoading();
		saveAndProceed('#sup_popup form', function(data){
			defaultWindow.hideLoading();
			hidePopUp();
			loadData();
		});
	}
}

/*
 *	По заказу выполнены все работы. Закрываем все задачи.
 */
function packageIsReady(package_id, ulid){
	$('#modal').fadeIn(0);
	if (package_id != null) {
		$.ajax({
			url: '/manager/package/packageisready/' + package_id,
			dataType: 'html',
			success: function(data){
				$('#ul' + ulid).replaceWith(data);
				flagsUpdate();
				$('#modal').fadeOut(0);
			},
			error: function(jqXHR, textStatus, errorThrown){
				$('#modal').fadeOut(0);
				$('#ul' + ulid).replaceWith($('<span/>').text(textStatus));
			}
		});
	}
	$('#modal').fadeOut(0);
}

/*	
 * Берём новый заказ себе.
 */
function takePack(package_id, ulid){
	if (package_id != null) {
		$.ajax({
			url: '/manager/package/takepack/' + package_id,
			dataType: 'html',
			success: function(data){
				$('#ul' + ulid).replaceWith(data);
				flagsUpdate();
				contextMenuInit();
			},
			error: function(jqXHR, textStatus, errorThrown){
				$('#ul' + ulid).replaceWith($('<span/>').text(textStatus));
			}
		});
	}
}

/*	
 * Отказывамся от заказа
 */
function decline(package_id, ulid){
	if (package_id != null) {
		$.ajax({
			url: '/manager/package/decline/' + package_id,
			dataType: 'html',
			success: function(data){
				$('#ul' + ulid).replaceWith(data);
				flagsUpdate();
				contextMenuInit();
			},
			error: function(jqXHR, textStatus, errorThrown){
				$('#ul' + ulid).replaceWith($('<span/>').text(textStatus));
			}
		});
	}
}
