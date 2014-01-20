/*global PopupWindow:true,
 hidePopUpLoader:true,
 DomainRequest:true,
 SupWindow:true,
 hint:true*/
var PaymentsListWindow;
var PaymentEditWindow;

PaymentsListWindow = function (packageId,callback,parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.packageId=packageId;
	this.isInit=false;
	this.bRequest=false;
	this.bPositioned=false;
	this.bInitHandlers=false;
};

PaymentsListWindow.prototype=new SupWindow();
PaymentsListWindow.prototype.packageId=0;
PaymentsListWindow.prototype.reload=function() {
	if(this.bRequest) {
		return;
	}
	var obWin=this.obPopup.body();
	var self=this;
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/payment/index',
		data: {'package_id':self.packageId},
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
PaymentsListWindow.prototype.initHandlers=function() {
	if(!this.isInit || this.bInitHandlers) {
		return;
	}
	if(!this.bPositioned) {
		this.obPopup._position();
		this.bPositioned=true;
	}
	var self=this;
	var obWin=self.obPopup.body();
	obWin.find('a.payform').click(function(e){
		e.preventDefault();
		var hash = $(this).attr('href');
		var arHash = hash.split('_');
		if(arHash[0]==='#payment' && arHash.length===3) {
			PaymentEdit(arHash[1],arHash[2],null,self);
		}
	});
	obWin.find('a.approve').click(function(e){
		e.preventDefault();
		self.approvePay($(this).attr('rel'));
	});
	this.bInitHandlers=true;
};
PaymentsListWindow.prototype.approvePay=function(payment_id){
	if(this.bRequest) {
		return;
	}
	var obWin=this.obPopup.body();
	var self=this;
	self.obPopup.showLoading();
	$.ajax({
		url: '/manager/payment/approve',
		dataType: 'html',
		data: {'payment_id': payment_id},
		success: function(data){
			self.bRequest=false;
			self.reload();
		},
		error: function(jqXHR, textStatus, errorThrown){
			self.bRequest=false;
			self.close();
		}
	});
};

/**
 * Функция генерирует окно редактирования оплаты
 * @param callback function функция которую необходимо вызвать при открытии окна
 * @param parent DomainRequestListWindow родительское окно открывшеее указанное
 *
 * @constructor
 * @param packageId
 * @param paymentId
 */
PaymentEditWindow = function (paymentId, packageId, callback, parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.paymentId=paymentId;
	this.packageId=packageId;
	this.isInit=false;
	this.bRequest=false;
	this.bPositioned=false;
	this.bInitHandlers=false;
};

PaymentEditWindow.prototype=new SupWindow();
PaymentEditWindow.prototype.paymentId=0;
PaymentEditWindow.prototype.packageId=0;
PaymentEditWindow.prototype.reload=function () {
	if(this.bRequest) {
		return;
	}
	var self=this;
	this.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/payment/view',
		data: {'package_id': this.packageId,'payment_id': this.paymentId},
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
PaymentEditWindow.prototype.save=function(callback) {
	if(this.bRequest) {
		return;
	}
	var self=this;
	var obWin=self.obPopup.body();
	var obData=obWin.find('#payment_form').serialize();
	self.obPopup.showLoading();
	obWin.find('.formRow.error').removeClass('error');
	this.bRequest=true;
	obWin.find('.save-result').css('display','none');
	$.ajax({
		url: '/manager/payment/save',
		data: obData,
		type: 'post',
		dataType: 'html',
		success: function(data) {
			self.bRequest=false;
			self.setContent(data);
			self.initHandlers();
			self.obPopup.hideLoading();
			if(obWin.find('.save-result')) {
				if(self.obParent) {
					self.obParent.reload();
				}
				if(callback && typeof(callback)==='function') {
					callback.call(self);
				}
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			self.obPopup.hideLoading();
			alert('save error');
			self.bRequest=false;
		}
	});
};
PaymentEditWindow.prototype.deleteRecord=function() {
	if(this.bRequest) {
		return;
	}
	var self=this;
	var obWin=self.obPopup.body();
	self.obPopup.showLoading();
	obWin.find('.formRow.error').removeClass('error');
	this.bRequest=true;
	obWin.find('.save-result').css('display','none');
	$.ajax({
		url: '/manager/payment/delete',
		data: {'payment_id':self.paymentId},
		type: 'get',
		dataType: 'json',
		success: function(data) {
			self.bRequest=false;
			self.obPopup.hideLoading();
			if(data.hasOwnProperty('done') && data.done==1) {
				if(self.obParent) {
					self.obParent.reload();
				}
				self.close();
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			self.obPopup.hideLoading();
			alert('delete error');
			self.bRequest=false;
		}
	});
};
PaymentEditWindow.prototype.initHandlers=function() {
	if(!this.isInit || this.bInitHandlers) {
		return;
	}
	if(!this.bPositioned) {
		this.obPopup._position();
		this.bPositioned=true;
	}
	var self=this;
	var obWin=self.obPopup.body();
	obWin.find("#Payment_dt,#Payment_dt_pay").datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: "dd.mm.yy",
		changeMonth: true,
		changeYear: true
	});
	obWin.find('.buttonCancel').click(function(e){
		e.preventDefault();
		self.close();
	});
	obWin.find('.buttonSaveClose').click(function(e) {
		e.preventDefault();
		self.save(function(){this.close();});
	});
	obWin.find('.buttonSave').click(function(e){
		e.preventDefault();
		self.save();
	});
	obWin.find('form#payment_form').submit(function(e){
		e.preventDefault();
		self.save();
	});
	obWin.find('.buttonDelete').click(function(e){
		e.preventDefault();
		self.deleteRecord();
	});
	self.bInitHandlers=true;
};

/**
 * Функция отображает окно со списком заявок на домен
 *
 * @param packageId int номер пакета заявки связанные с которым необходимо отобразить
 * @param callback функция, которую необходимо выполнить после отображения окна
 * @param parent
 *
 * @return {PaymentsListWindow}
 *
 * @constructor
 */
function PaymentsList(packageId,callback,parent) {
	var obPaymentsList = new PaymentsListWindow(packageId,callback,parent);
	obPaymentsList.show();
	return obPaymentsList;
}

function editPays(package_id){
	document.location.hash='payments_'+package_id;
}

/**
 * Функция открывает окно с подробной информацией о заявке и позволяет
 * @param packageId
 * @param parent
 * @param paymentId
 * @param callback function функция вызываемая при открытии окна
 *
 * @return {PaymentEditWindow}
 *
 * @constructor
 */
function PaymentEdit(paymentId,packageId,callback,parent) {
	var obPaymentWindow = new PaymentEditWindow(paymentId,packageId, callback, parent);
	obPaymentWindow.show();
	return obPaymentWindow;
}
