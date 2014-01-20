/*global PopupWindow:true,
         hidePopUpLoader:true,
         DomainRequest:true,
         SupWindow:true,
         hint:true*/
var DomainRequestListWindow;
var DomainRequestWindow;

DomainRequestListWindow = function (packageId, userId, callback,parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.packageId=packageId;
	this.userId=userId;
	this.isInit=true;
	this.bRequest=false;
};

DomainRequestListWindow.prototype=new SupWindow();
DomainRequestListWindow.prototype.packageId=0;
DomainRequestListWindow.prototype.userId=0;
DomainRequestListWindow.prototype.reload=function() {
	if(this.bRequest) {
		return;
	}
	var obWin=this.obPopup.body();
	var self=this;
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/domainRequest/index',
		data: {'packageId':self.packageId,'userId':self.userId},
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
DomainRequestListWindow.prototype.initHandlers=function() {
	if(this.bInitHandlers) {
		return;
	}
	this.obPopup._position();
	var self=this;
	var obWin=self.obPopup.body();
	obWin.find('a.domainRequestLink,a.domainAdd').click(function(e){
		e.preventDefault();
		var sHref=$(this).attr('href');
		if(sHref.length>0) {
			var arHash=sHref.split('_');
			if (arHash[0]=== '#domainRequest') {
				if(arHash.length===2) {
					DomainRequest(arHash[1],'',null,self);
				} else if(arHash.length===3) {
					DomainRequest(arHash[2],arHash[1],null,self);
				}
			}
		}
	});
	self.bInitHandlers=true;
};

/**
 * Функция генерирует окно с формой заявки на доменное имя
 *
 * @param type string тип идентификатора
 * @param id int номер записи
 * @param callback function функция которую необходимо вызвать при открытии окна
 * @param parent DomainRequestListWindow родительское окно открывшеее указанное
 *
 * @constructor
 */
DomainRequestWindow = function (type, id, callback, parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	if(type==='') {
		this.requestId=id;
	} else if(type==='package') {
		this.packageId=id;
	} else {
		throw new Error('Unsupported type');
	}
	this.type=type;
	this.bRequest=false;
	this.bInitHandlers=false;
};

DomainRequestWindow.prototype=new SupWindow();
DomainRequestWindow.prototype.requestId=0;
DomainRequestWindow.prototype.userId=0;
DomainRequestWindow.prototype.packageId=0;
DomainRequestWindow.prototype.type='';
DomainRequestWindow.prototype.prepareRequest=function() {
	var obResult={
		'type':this.type
	};
	if(this.type==='') {
		obResult.requestId=this.requestId;
	}  else if(this.type==='package') {
		obResult.packageId=this.packageId;
	} else {
		throw new Error('Unsupported type');
	}
	return obResult;
};
DomainRequestWindow.prototype.reload=function () {
	if(this.bRequest) {
		return;
	}
	var self=this;
	this.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/domainRequest/form',
		data: this.prepareRequest(),
		dataType: 'html',
		success: function(data){
			self.setContent(data).show();
			self.initHandlers();
			self.bRequest=false;
		},
		error: function(jqXHR, textStatus, errorThrown){
			alert(textStatus);
			self.close();
		}
	});
};
DomainRequestWindow.prototype.processFormErrors=function(data) {
	var self=this;
	var obWin=self.obPopup.body();
	if(parseInt(data.error,10)===1000 && ('errors' in data)) {
		var field='';
		//noinspection JSUnresolvedVariable
		for(field in data.errors) {
			if(data.errors.propertyIsEnumerable(field)) {
				var arErrors=data.errors[field];
				obWin.find('#DomainRequestForm_'+field).parent().attr('title',arErrors.join('\n')).addClass('error');
			}
		}
	}
};
DomainRequestWindow.prototype.save=function(callback) {
	if(this.bRequest) {
		return;
	}
	var self=this;
	var obWin=self.obPopup.body();
	var obData=obWin.find('#dr-form').serialize();
	self.obPopup.showLoading();
	obWin.find('.formRow.error').removeClass('error');
	this.bRequest=true;
	obWin.find('.save-result').css('display','none');
	$.ajax({
		url: '/manager/domainRequest/form',
		data: obData,
		type: 'post',
		dataType: 'json',
		success: function(data) {
			self.bRequest=false;
			self.obPopup.hideLoading();
			if('saveOk' in data && data.saveOk) {
				if(self.obParent) {
					self.obParent.reload();
				}
				obWin.find('.save-result').css('display','block');
				if(callback && typeof(callback)==='function') {
					callback.call(self);
				}
			} else if('error' in data) {
				self.processFormErrors(data);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			self.obPopup.hideLoading();
			alert('save error');
			self.bRequest=false;
		}
	});
};
DomainRequestWindow.prototype.requestDomain=function() {
	if(this.bRequest) {
		return;
	}
	var self=this;
	var obWin=self.obPopup.body();
	var obData={
		'id':self.requestId
	};
	self.obPopup.showLoading();
	obWin.find('.formRow.error').removeClass('error');
	self.bRequest=true;
	$.ajax({
		url: '/manager/domainRequest/bmrequest',
		data: obData,
		type: 'post',
		dataType: 'json',
		success: function(data) {
			self.obPopup.hideLoading();
			if('error' in data) {
				self.processFormErrors(data);
			}
			self.bRequest=false;
		},
		error: function(jqXHR, textStatus, errorThrown) {
			self.obPopup.hideLoading();
			alert('request error');
			self.bRequest=false;
		}
	});
};
DomainRequestWindow.prototype.initHandlers=function() {
	if(this.bInitHandlers) {
		return;
	}
	var self=this;
	var obWin=self.obPopup.body();
	obWin.find('.billManagerRequestDomain').click(function(e){
		e.preventDefault();
		//self.requestDomain();
	});
	obWin.find('.billManagerRequestCancel').click(function(e){
		e.preventDefault();
		self.close();
	});
	obWin.find('.billManagerRequestSaveClose').click(function(e) {
		e.preventDefault();
		self.save(function(){
			this.close();
		});
	});
	obWin.find('.billManagerRequestSave').click(function(e){
		e.preventDefault();
		self.save();
	});
	obWin.find('#DomainRequestForm_mode').change(function(e){
		obWin.find('.panel').addClass('hidden');
		obWin.find('.'+$(this).val()+'Data.panel').removeClass('hidden');
	});
	obWin.find('form').submit(function(e){
		e.preventDefault();
		self.save();
	});
	obWin.find('label').each(function(){
		var sTitle=$(this).attr('title');
		if(sTitle!==undefined && sTitle!=='') {
			$(this).after('<div style="display:none;">'+sTitle.replace(/\\n/g,'<br/>')+'</div>');
			$(this).attr('title','');
			$(this).mouseenter(function(e){
				hint($(this),$(this).next().html(),200);
			});
		}
	});
	self.bInitHandlers=true;
};

/**
 * Функция отображает окно со списком заявок на домен
 *
 * @param packageId int номер пакета заявки связанные с которым необходимо отобразить
 * @param userId int номер пользователя чьи заявки необходимо отобразить
 * @param callback функция, которую необходимо выполнить после отображения окна
 *
 * @return {DomainRequestListWindow}
 *
 * @constructor
 */
function DomainRequests(packageId,userId,callback) {
	//$.getScript('/js/domainrequest.js',function(){
	var obDomainsWindow = new DomainRequestListWindow(packageId, userId, callback);
	obDomainsWindow.show();
	return obDomainsWindow;
	//cabinetWindow.init(packageId).show();
	//})
}

/**
 * Функция открывает окно с подробной информацией о заявке и позволяет заполнить и посмотреть информацию
 *
 * @param requestId int номер заявки
 * @param type string тип переданного номера, пустая строка - номер заявки, package - номер заказа
 * @param callback function функция вызываемая при открытии окна
 *
 * @return {DomainRequestListWindow}
 *
 * @constructor
 */
function DomainRequest(requestId, type, callback, parent) {
	//$.getScript('/js/domainrequest.js',function(){
	var obDomainWindow = new DomainRequestWindow(type, requestId, callback, parent);
	obDomainWindow.show();
	return obDomainWindow;
	//cabinetWindow.init(packageId).show();
	//})
}