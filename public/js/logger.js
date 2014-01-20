/*global PopupWindow:true,
 hidePopUpLoader:true,
 DomainRequest:true,
 SupWindow:true,
 hint:true,hashHandler:true*/
var LoggerFormWindow;

LoggerFormWindow = function (client_id, callback,parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.userId=client_id;
	this.isInit=false;
	this.bRequest=false;
	this.bPositioned=false;
};

LoggerFormWindow.prototype=new SupWindow();
LoggerFormWindow.prototype.userId=0;
LoggerFormWindow.prototype.reload=function() {
	if(this.bRequest) {
		return;
	}
	var obWin=this.obPopup.body();
	var self=this;
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/logger/index',
		data: {'client_id':self.userId},
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
LoggerFormWindow.prototype.initHandlers=function() {
	if(!this.isInit || this.bInitHandlers) {
		return;
	}
	if(!this.bPositioned) {
		this.obPopup._position();
		this.bPositioned=true;
	}
	var self=this;
	var obWin=self.obPopup.body();
	obWin.find('form#megaform').submit(function(e){
		e.preventDefault();
		self.save();
	});
	obWin.find('.buttonCancel').click(function(e){
		e.preventDefault();
		self.close();
	});
	obWin.find('.buttonSave').click(function(e) {
		e.preventDefault();
		obWin.find('form#megaform').submit();
	});
	obWin.find('textarea#info').keydown(function(e){
		if (e.keyCode === 13 && e.ctrlKey) {
			e.preventDefault();
			self.save();
		}
	});
	obWin.find('li.dateItem').click(function(e){
		e.preventDefault();
		var obHref=$(this).children('a').first();
		var sHref=obHref.attr('href');
		sHref=sHref.substr(1,sHref.length-1);
		var obDateA=obWin.find('li.date>a#'+sHref);
		if(obDateA.length>0) {
			var pos=obDateA.parent().position();
			var curPos=obWin.find('.rightColumn').scrollTop();
			var y=parseFloat(pos.top);
			y+=curPos;
			obWin.find('.rightColumn').scrollTop(y-obDateA.parent().outerHeight());
		}
	});
	this.bInitHandlers=true;
};
LoggerFormWindow.prototype.save=function() {
	if(this.bRequest) {
		return;
	}
	var self,obWin,obData;
	self=this;
	obWin=self.obPopup.body();
	obData=obWin.find('form#megaform').serialize();
	self.obPopup.showLoading();
	obWin.find('.formRow.error').removeClass('error');
	this.bRequest=true;
	$.ajax({
		url: '/manager/logger/put',
		data: obData,
		type: 'post',
		dataType: 'json',
		success: function(data) {
			self.bRequest=false;
			self.obPopup.hideLoading();
			if('success' in data && data.success) {
				if(self.obParent) {
					self.obParent.reload();
				}
				self.reload();
			} else {
				alert('При сохранении сообщения в журнал произошла ошибка');
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			self.obPopup.hideLoading();
			alert('При сохранении сообщения в журнал произошла ошибка');
			self.bRequest=false;
		}
	});
};

/**
 * Функция отображает окно журнала клиента
 *
 * @param client_id номер клиента, чей журнал необходимо отобразить
 * @param callback функция, которую необходимо выполнить после отображения окна
 * @param parent родительский объект
 *
 * @return {LoggerFormWindow}
 */
function LoggerWindow(client_id,callback,parent) {
	var obLoggerWindow = new LoggerFormWindow(client_id, callback, parent);
	obLoggerWindow.show();
	return obLoggerWindow;
}

$(document).ready(function(){
	hashHandler.addHandler('logger',function(arHash){
		if(arHash[0]==='#loggerForm' && arHash.length===2) {
			LoggerWindow(arHash[1]);
		}
	});
});