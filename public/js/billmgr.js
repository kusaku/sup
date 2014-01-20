/*global hashHandler:true,SupWindow:true*/
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
		url: 'manager/bm/register',
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
					
					$('#linkid-' + client_id).tipBox(msg).tipBox('show');
				}
			} 
			catch (e) {
				// что-то пошло не так, json не вернулся
				$('#linkid-' + client_id).tipBox('Ошибка на стороне сервера!').tipBox('show');
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
		url: 'manager/bm/open',
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
				$('#linkid-' + client_id).tipBox('Ошибка на стороне сервера!').tipBox('show');
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
function bmVHost(package_id, service_id){
	$.ajax({
		type: 'GET',
		url: 'manager/bm/ordervhost',
		data: {
			'package_id': package_id,
			'service_id': service_id,
			'use_promo': confirm('Использовать промокод?') ? 1 : 0
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
				$('#linkid-' + package_id + '-' + service_id).tipBox('Ошибка на стороне сервера!').tipBox('show');
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
function bmDomainName(package_id, service_id){
	$.ajax({
		type: 'GET',
		url: 'manager/bm/orderdomain',
		data: {
			'package_id': package_id,
			'service_id': service_id,
			'use_promo': confirm('Использовать промокод?') ? 1 : 0
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
				$('#linkid-' + package_id + '-' + service_id).tipBox('Ошибка на стороне сервера!').tipBox('show');
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
		url: 'manager/bm/updateattributes',
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
				$('#linkid-' + client_id).tipBox('Ошибка на стороне сервера!').tipBox('show');
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#linkid-' + client_id).tipBox('Ошибка на стороне сервера!').tipBox('show');
		}
	});
	return false;
}

var BillManagerWindow;

BillManagerWindow = function (callback,parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.isInit=false;
	this.bRequest=false;
	this.bPositioned=false;
	this.obFilter={};
	this.sReloadUrl='/manager/bm/index';
};

BillManagerWindow.prototype=new SupWindow();
BillManagerWindow.prototype.sReloadUrl='/manager/bm/index';
BillManagerWindow.prototype.obFilter={};
BillManagerWindow.prototype.close=function() {
	this.obPopup.close();
	window.location.hash='!';
	if(this.obParent) {
		this.obParent.BMWindow=null;
	}
};
BillManagerWindow.prototype.reload=function() {
	if(this.bRequest) {
		return;
	}
	var obWin=this.obPopup.body();
	var self=this;
	self.obPopup.showLoading();
	this.bRequest=true;
	var obData={};
	obData=self.obFilter;
	$.ajax({
		url: this.sReloadUrl,
		data: obData,
		type: 'post',
		dataType: 'html',
		success: function(data){
			self.bRequest=false;
			self.setContent(data);
			self.isInit=true;
			self.initHandlers();
		},
		error: function(jqXHR, textStatus, errorThrown){
			self.bRequest=false;
			self.close();
		}
	});
};
BillManagerWindow.prototype.setFilter=function(obFilter) {
	this.obFilter=obFilter;
};
BillManagerWindow.prototype.emptyFilter=function() {
	this.obFilter={'BMUserFilterForm[clear]':1};
};
BillManagerWindow.prototype.filter=function() {
	this.obFilter=this.obPopup.body().find('form#filter').serialize();
	this.reload();
};
BillManagerWindow.prototype.initHandlers=function() {
	if(!this.isInit || this.bInitHandlers) {
		return;
	}
	if(!this.bPositioned) {
		this.obPopup._position();
		this.bPositioned=true;
	}
	var self=this;
	var obWin=self.obPopup.body();
	obWin.find('.yiiPager>li>a').click(function(e){
		e.preventDefault();
		self.sReloadUrl=$(this).attr('href');
		self.reload();
	});
	obWin.find('.buttonFilter').click(function(e){
		e.preventDefault();
		self.filter();
	});
	obWin.find('.buttonClearfilter').click(function(e){
		e.preventDefault();
		self.emptyFilter();
		self.reload();
	});
	obWin.find('form#filter').submit(function(e){
		e.preventDefault();
		self.filter();
	});
	this.bInitHandlers=true;
};

$(document).ready(function(){
	hashHandler.addHandler('billmanager',function(arHash){
		if(arHash[0]==='#billManager' && arHash.length===1) {
			new BillManagerWindow().show();
		}
	});
});