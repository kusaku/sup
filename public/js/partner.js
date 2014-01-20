/*global PopupWindow:true,SupWindow:true */
var PartnerCardWindow;
/**
 * Функция генерирует окно с формой заявки на доменное имя
 *
 * @param id int номер записи
 * @param callback function функция которую необходимо вызвать при открытии окна
 * @param parent DomainRequestListWindow родительское окно открывшеее указанное
 *
 * @constructor
 */
PartnerCardWindow=function(id, callback, parent) {
	SupWindow.prototype.init.call(this,parent,callback);
    this.id=id;
};

PartnerCardWindow.prototype=new SupWindow();

PartnerCardWindow.prototype.id=0;
PartnerCardWindow.prototype.reload=function () {
	var self=this;
	self.obPopup.showLoading();
	$.ajax({
		url: '/manager/people/partnercard/' + self.id,
		dataType: 'html',
		success: function(data){
			self.obPopup.setContent(data).show();
			self.initHandlers();
		},
		error: function(jqXHR, textStatus, errorThrown){
			alert(textStatus);
			self.obPopup.close();
			window.location.hash = '!';
		}
	});
	return this;
};
PartnerCardWindow.prototype.initHandlers=function() {
	var self=this;
	var obWin=this.obPopup.body();
	obWin.find("#PartnerEditForm_date_sign").datetimepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: "yy-mm-dd",
		timeFormat: "hh:mm:ss",
		changeMonth: true,
		changeYear: true
	});
	obWin.find('.buttonCancel').click(function(e){
		e.preventDefault();
		self.obPopup.close();
		window.location.hash = '!';
	});
	obWin.find('.buttonSave').click(function(e) {
		e.preventDefault();
		obWin.find('#partner-form').submit();
	});
	obWin.find('#partner-form').submit(function(e){
		e.preventDefault();
		self.save();
	});
};
PartnerCardWindow.prototype.save=function() {
	var self=this;
	var obWin=self.obPopup.body();
	var obData=obWin.find('#partner-form').serialize();
	self.obPopup.showLoading();
	obWin.find('.formRow.error').removeClass('error');
	$.ajax({
		url: '/manager/people/partnercard/' + self.id,
		data: obData,
		type: 'post',
		dataType: 'html',
		success: function(data) {
			self.obPopup.setContent(data).show();
			self.initHandlers();
		},
		error: function(jqXHR, textStatus, errorThrown) {
			self.obPopup.hideLoading();
			alert('save error');
		}
	});
};

/*
 * Карточка партнёра.
 */
function PartnerCard(id){
	var obPartnerWindow=new PartnerCardWindow(id);
	obPartnerWindow.show();
}
