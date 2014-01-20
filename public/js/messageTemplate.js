/*global PopupWindow:true,
         hidePopUpLoader:true,
         DomainRequest:true,
         SupWindow:true,
         hint:true*/
var MessageTemplateWindow;

MessageTemplateWindow = function (parent,callback) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.isInit=true;
	this.bRequest=false;
	this.lastTab=0;
	this.obTabs=null;
};

MessageTemplateWindow.prototype=new SupWindow();
MessageTemplateWindow.prototype.lastTab=0;
MessageTemplateWindow.prototype.obTabs=null;
MessageTemplateWindow.prototype.obCommonMessagesScroll=null;
MessageTemplateWindow.prototype.obMyMessagesScroll=null;
MessageTemplateWindow.prototype.reload=function() {
	if(this.bRequest) {
		return;
	}
	var obWin,self;
	obWin=this.obPopup.body();
	self=this;
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/cabinet/messageTemplates',
		dataType: 'html',
		success: function(data){
			self.bRequest=false;
			self.setContent(data);
			self.initHandlers();
			self.obTabs.tabs("option","active",self.lastTab);
		},
		error: function(jqXHR, textStatus, errorThrown){
			self.bRequest=false;
			self.close();
		}
	});
};
MessageTemplateWindow.prototype.save=function() {
	if(this.bRequest) {
		return;
	}
	var obWin,self,obData;
	obWin=this.obPopup.body();
	obData=obWin.find('form.messageTemplateForm').serialize();
	self=this;
	self.lastTab=self.obTabs.tabs('option','active');
	this.bRequest=true;
	$.ajax({
		url: '/manager/cabinet/messageTemplateAdd',
		dataType: 'json',
		data: obData,
		type:'post',
		success: function(data){
			self.bRequest=false;
			if(data.hasOwnProperty('ok') && data.ok==1) {
				self.reload();
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			self.bRequest=false;
			self.close();
		}
	});
};
MessageTemplateWindow.prototype.deleteMessage=function(iId) {
	if(this.bRequest) {
		return;
	}
	var obWin,self,obData;
	self=this;
	self.lastTab=self.obTabs.tabs('option','active');
	this.bRequest=true;
	$.ajax({
		url: '/manager/cabinet/messageTemplateDelete',
		dataType: 'json',
		data: {id:iId},
		type:'post',
		success: function(data){
			self.bRequest=false;
			if(data.hasOwnProperty('ok') && data.ok==1) {
				self.reload();
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			self.bRequest=false;
			self.close();
		}
	});
};
MessageTemplateWindow.prototype.initHandlers=function() {
	if(this.bInitHandlers) {
		return;
	}
	var self=this;
	var obWin=self.obPopup.body();
	//Элементы интерфейса
	self.obTabs=obWin.find('.tabscontainer').tabs();
	this.obPopup._position();
	obWin.find('#tabs-1>.scrollPanel').jScrollPane({showArrows:true,hideFocus:true,verticalDragMinHeight:20});
	self.obMyMessagesScroll=obWin.find('#tabs-1>.scrollPanel').data('jsp');
	obWin.find('#tabs-0>.scrollPanel').jScrollPane({showArrows:true,hideFocus:true,verticalDragMinHeight:20});
	self.obCommonMessagesScroll=obWin.find('#tabs-0>.scrollPanel').data('jsp');
	self.obTabs.tabs('option','activate',function(e,ui){
		self.obCommonMessagesScroll.reinitialise();
		self.obMyMessagesScroll.reinitialise();
	});
	//Обычные кнопки
	obWin.find('a.buttonSave').click(function(e){
		e.preventDefault();
		self.save();
	});
	obWin.find('a.buttonClose').click(function(e){
		e.preventDefault();
		self.close();
	});
	//Поля ввода и форма
	obWin.find('form.messageTemplateForm').submit(function(e){
		e.preventDefault();
		self.save();
	});
	obWin.find('#MessagesTemplateForm_content').keydown(function(e){
		if (e.keyCode === 13 && e.ctrlKey) {
			e.preventDefault();
			self.save();
		}
	});
	//Редактирование сообщений
	obWin.find('a.editMyMesssage').click(function(e){
		e.preventDefault();
		var sText=$(this).parent().find('input[name=content]').val();
		var iId=$(this).parent().find('input[name=id]').val();
		obWin.find('.buttons').hide();
		obWin.find('.buttonsUpdate').show();
		obWin.find('textarea#MessagesTemplateForm_content').val(sText);
		obWin.find('#MessagesTemplateForm_id').val(iId);
	});
	obWin.find('a.buttonUpdate').click(function(e){
		e.preventDefault();
		self.save();
	});
	obWin.find('a.buttonCancel').click(function(e){
		e.preventDefault();
		obWin.find('.buttons').show();
		obWin.find('.buttonsUpdate').hide();
		obWin.find('textarea#MessagesTemplateForm_content').val('');
		obWin.find('#MessagesTemplateForm_id').val(0);
	});
	//Удаление сообщения
	obWin.find('a.deleteMyMesssage').click(function(e){
		e.preventDefault();
		var iId=$(this).parent().find('input[name=id]').val();
		self.deleteMessage(iId);
	});
	//Ввод сообщения
	obWin.find('a.message').click(function(e){
		e.preventDefault();
		var sText=$(this).next().val();
		if(sText!==undefined && sText.length>0) {
			self.obParent.val(sText);
			self.close();
		}
	});
	self.bInitHandlers=true;
};