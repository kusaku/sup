/*global SupWindow:true,hashHandler:true,BillManagerWindow:true*/

var PeoplePasswordWindow,ClientCardWindow;

/**
 * Функция генерирует окно с формой заявки на доменное имя
 *
 * @param id int номер записи
 * @param callback function функция которую необходимо вызвать при открытии окна
 * @param parent DomainRequestListWindow родительское окно открывшеее указанное
 *
 * @constructor
 * @param parent_id
 */
function PeopleEditWindow(id, parent_id, callback, parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.id=id;
	this.parent_id=parent_id;
	this.BMWindow=null;
	this.bRequest=false;
	this.bInitHandlers=false;
}

PeopleEditWindow.prototype=new SupWindow();
PeopleEditWindow.prototype.id=0;
PeopleEditWindow.prototype.parent_id=0;
PeopleEditWindow.prototype.reload=function () {
	if(this.bRequest) {
		return this;
	}
	var self=this;
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/people/' + self.id,
		dataType: 'html',
		data: {'parent': self.parent_id},
		success: function(data){
			self.bRequest=false;
			self.setContent(data).show();
			self.initHandlers();
		},
		error: function(jqXHR, textStatus, errorThrown){
			alert(textStatus);
			self.bRequest=false;
			self.close();
		}
	});
	return this;
};
PeopleEditWindow.prototype.findBmUser=function() {
	if(this.BMWindow===null) {
		this.BMWindow=new BillManagerWindow(undefined,this);
		this.BMWindow.setFilter({
			'BMUserFilterForm[email]':this.obPopup.body().find('input#PeopleEditForm_mail').val()
		});
		this.BMWindow.show();
	}
};
PeopleEditWindow.prototype.initHandlers=function() {
	if(this.bInitHandlers) {
		return;
	}
	var self=this;
	var obWin=this.obPopup.body();
	obWin.find('.tabscontainer.modal').tabs();
	this.obPopup._position();

	obWin.find('.buttonCancel').click(function(e){
		e.preventDefault();
		self.close();
	});
	obWin.find('.buttonSave').click(function(e) {
		e.preventDefault();
		obWin.find('#people-edit-form').submit();
	});
	obWin.find('.linkToBM').click(function(e) {
		e.preventDefault();
		self.findBmUser();
	});
	obWin.find('.buttonSaveClose').click(function(e) {
		e.preventDefault();
		self.save(function(){
			var obWin=self.obPopup.body();
			if(obWin.find('.save-result').length>0) {
				this.close();
			}
		});
	});
	obWin.find('.plus').click(function(e) {
		e.preventDefault();
		self.save(function(){
			var obWin=self.obPopup.body();
			if(obWin.find('.save-result').length>0) {
				var id=obWin.find('#PeopleEditForm_id').val();
				this.close();
				window.location.hash='package_0_'+id;
			}
		});
	});
	obWin.find('#people-edit-form').submit(function(e){
		e.preventDefault();
		self.save();
	});
	this.bInitHandlers=true;
};
PeopleEditWindow.prototype.save=function(callback) {
	if(this.bRequest) {
		return;
	}
	var self=this;
	var obWin=self.obPopup.body();
	var obData=obWin.find('#people-edit-form').serialize();
	self.obPopup.showLoading();
	obWin.find('.formRow.error').removeClass('error');
	obWin.find('.save-result').remove();
	this.bRequest=true;
	$.ajax({
		url: '/manager/people/save/',
		data: obData,
		type: 'post',
		dataType: 'html',
		success: function(data) {
			self.bRequest=false;
			self.setContent(data).show();
			self.initHandlers();
			if(callback && typeof(callback)==='function') {
				callback.call(self);
			}
		},
		error: function(jqXHR, textStatus, errorThrown) {
			self.bRequest=false;
			self.obPopup.hideLoading();
			alert('save error');
		}
	});
};

function PeopleEdit(id,parent) {
	var obWindow=new PeopleEditWindow(id,parent);
	obWindow.show();
}
/*
 * Добавляем нового клиента.
 */
function addEditClient(id, parent){
	PeopleEdit(id,parent);
}

/**
 * Функция генерирует окно с формой заявки на доменное имя
 *
 * @param id int номер записи
 * @param callback function функция которую необходимо вызвать при открытии окна
 * @param parent DomainRequestListWindow родительское окно открывшеее указанное
 *
 * @constructor
 */
function JurPersonCardWindow(id, callback, parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.id=id;
	this.bRequest=false;
	this.bInitHandlers=false;
}

JurPersonCardWindow.prototype=new SupWindow();
JurPersonCardWindow.prototype.id=0;
JurPersonCardWindow.prototype.bRequest=false;
JurPersonCardWindow.prototype.bInitHandlers=false;
JurPersonCardWindow.prototype.reload=function () {
	if(this.bRequest) {
		return;
	}
	var self=this;
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/people/jur_reference/' + self.id,
		dataType: 'html',
		success: function(data){
			self.setContent(data).show();
			self.initHandlers();
			self.bRequest=false;
		},
		error: function(jqXHR, textStatus, errorThrown){
			alert(textStatus);
			self.close();
			self.bRequest=false;
		}
	});
	return this;
};
JurPersonCardWindow.prototype.initHandlers=function() {
	if(this.bInitHandlers) {
		return;
	}
	var self=this;
	var obWin=this.obPopup.body();
	obWin.find("#JurPersonReferenceForm_type").change(function(e){
		var type=$(this).val();
		obWin.find('.panel').addClass('hidden');
		obWin.find('.panel.'+type+'Data').removeClass('hidden');
	});
	obWin.find('.buttonCancel').click(function(e){
		e.preventDefault();
		self.close();
	});
	obWin.find('.buttonSave').click(function(e) {
		e.preventDefault();
		obWin.find('#jur-reference-form').submit();
	});
	obWin.find('#jur-reference-form').submit(function(e){
		e.preventDefault();
		self.save();
	});
	this.bInitHandlers=true;
};
JurPersonCardWindow.prototype.save=function() {
	if(this.bRequest) {
		return;
	}
	var self=this;
	var obWin=self.obPopup.body();
	var obData=obWin.find('#jur-reference-form').serialize();
	self.obPopup.showLoading();
	obWin.find('.formRow.error').removeClass('error');
	this.bRequest=true;
	$.ajax({
		url: '/manager/people/jur_reference/' + self.id,
		data: obData,
		type: 'post',
		dataType: 'html',
		success: function(data) {
			self.setContent(data).show();
			self.initHandlers();
			self.bRequest=false;
		},
		error: function(jqXHR, textStatus, errorThrown) {
			self.obPopup.hideLoading();
			alert('save error');
			self.bRequest=false;
		}
	});
};

/*
 * Карточка юридического лица.
 */
function JurPersonCard(id){
	var obWindow=new JurPersonCardWindow(id);
	obWindow.show();
}

/**
 * Функция генерирует окно с формой заявки на доменное имя
 *
 * @param id int номер записи
 * @param callback function функция которую необходимо вызвать при открытии окна
 * @param parent DomainRequestListWindow родительское окно открывшеее указанное
 *
 * @constructor
 */
ClientCardWindow=function(id, callback, parent) {
	SupWindow.prototype.init.call(this,parent,callback);
    this.id=id;
};

ClientCardWindow.prototype=new SupWindow();
ClientCardWindow.prototype.id=0;
ClientCardWindow.prototype.reload=function () {
	var self=this;
	self.obPopup.showLoading();
	$.ajax({
		url: '/manager/people/card/' + self.id,
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
ClientCardWindow.prototype.initHandlers=function() {
	var self=this;
	var obWin=this.obPopup.body();
	/*obWin.find("#PartnerEditForm_date_sign").datetimepicker({
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
	});*/
};

/**
 * Карточка клиента.
 */
function clientCard(id){
	document.location.hash='clientCard_'+id;
}

//Контактные лица клиента
var PeopleContactsWindow;

PeopleContactsWindow=function(user_id,contact_id,callback,parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.id=user_id;
	this.contact_id=contact_id;
	this.isInit=false;
	this.bRequest=false;
	this.bPositioned=false;
	this.bInitHandlers=false;
	this.obListScroll=null;
};

PeopleContactsWindow.prototype=new SupWindow();
PeopleContactsWindow.prototype.id=0;
PeopleContactsWindow.prototype.contact_id=0;
PeopleContactsWindow.prototype.reload=function() {
	if(this.bRequest) {
		return this;
	}
	var self=this;
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/people/contacts/' + self.id,
		data: {contact_id:self.contact_id},
		dataType: 'html',
		success: function(data){
			self.bRequest=false;
			self.setContent(data);
			self.isInit=true;
			self.initHandlers();
		},
		error: function(jqXHR, textStatus, errorThrown){
			alert(textStatus);
			self.close();
		}
	});
	return this;
};
PeopleContactsWindow.prototype.save=function() {
	if(this.bRequest) {
		return this;
	}
	var self=this;
	var obForm=self.obPopup.body().find('form#contact-form').serialize();
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/people/contactsEdit/' + self.id,
		data: obForm,
		type: 'post',
		dataType: 'html',
		success: function(data){
			self.bRequest=false;
			self.setContent(data);
			self.isInit=true;
			self.initHandlers();
		},
		error: function(jqXHR, textStatus, errorThrown){
			alert(textStatus);
			self.close();
		}
	});
	return this;
};
PeopleContactsWindow.prototype.deleteItem=function() {
	if(this.bRequest) {
		return this;
	}
	var self=this;
	var obForm=self.obPopup.body().find('form#contact-form').serialize();
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/people/contactsDelete/' + self.id,
		data: {contact_id:self.contact_id},
		type: 'post',
		dataType: 'html',
		success: function(data){
			self.bRequest=false;
			self.setContent(data);
			self.isInit=true;
			self.initHandlers();
		},
		error: function(jqXHR, textStatus, errorThrown){
			alert(textStatus);
			self.close();
		}
	});
	return this;
};
PeopleContactsWindow.prototype.initHandlers=function() {
	if(!this.isInit || this.bInitHandlers) {
		return;
	}
	if(!this.bPositioned) {
		this.obPopup._position();
		this.bPositioned=true;
	}
	var self=this;
	var obWin=this.obPopup.body();
	obWin.find('.leftColumn>.scrollPanel').jScrollPane({showArrows:true,hideFocus:true,verticalDragMinHeight:20});
	self.obListScroll=obWin.find('.leftColumn>.scrollPanel').data('jsp');
	self.contact_id=parseInt(obWin.find('form#contact-form').submit(function(e){
		e.preventDefault();
		self.save();
	}).find('input#PeopleContacts_id').val(),10);
	obWin.find('a.buttonNew').click(function(e){
		e.preventDefault();
		self.contact_id=-1;
		self.reload();
	});
	obWin.find('a.buttonSave').click(function(e){
		e.preventDefault();
		self.save();
	});
	obWin.find('a.buttonCancel').click(function(e){
		e.preventDefault();
		self.close();
	});
	obWin.find('a.buttonDelete').click(function(e){
		e.preventDefault();
		self.deleteItem();
	});
	obWin.find('.contactItem').click(function(e){
		e.preventDefault();
		var ah=$(this).children('a');
		var hash = ah.attr('href');
		var arHash = hash.split('_');
		if (arHash.length > 0) {
			if(arHash[0]==='#peopleContact' && arHash.length===3) {
				self.contact_id=arHash[2];
				self.reload();
			}
		}
	});
	var obActiveItem=obWin.find('.leftColumn .active');
	if(obActiveItem.length>0) {
		var pos=obActiveItem.position();
		self.obListScroll.scrollToY(pos.top);
	}
	this.bInitHandlers=true;
};

/**
 * Функция генерирует окно с кнопкой генерации пароля и отправки уведомления пользователю
 *
 * @param id int номер клиента
 * @param callback function функция которую необходимо вызвать при открытии окна
 * @param parent родительское окно открывшеее указанное
 *
 * @constructor
 */
PeoplePasswordWindow=function(id, callback, parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.id=id;
	this.bRequest=false;
};

PeoplePasswordWindow.prototype=new SupWindow();
PeoplePasswordWindow.prototype.id=0;
PeoplePasswordWindow.prototype.reload=function () {
	if(this.bRequest) {
		return this;
	}
	var self=this;
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/people/newpassword/' + self.id,
		dataType: 'html',
		success: function(data){
			self.bRequest=false;
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
PeoplePasswordWindow.prototype.save=function() {
	if(this.bRequest) {
		return this;
	}
	var self=this;
	var obForm=self.obPopup.body().find('form#password-form').serialize();
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/people/newpassword/' + self.id,
		data: obForm,
		type: 'post',
		dataType: 'html',
		success: function(data){
			self.bRequest=false;
			self.setContent(data);
			self.initHandlers();
		},
		error: function(jqXHR, textStatus, errorThrown){
			alert(textStatus);
			self.close();
		}
	});
	return this;
};
PeoplePasswordWindow.prototype.initHandlers=function() {
	var self=this;
	var obWin=this.obPopup.body();
	self.obPopup._position();
	obWin.find('.buttonCancel').click(function(e){
		e.preventDefault();
		self.close();
	});
	obWin.find('.buttonSave').click(function(e) {
		e.preventDefault();
		obWin.find('#password-form').submit();
	});
	obWin.find('#password-form').submit(function(e){
		e.preventDefault();
		self.save();
	});
};

$(document).ready(function(){
	hashHandler.addHandler('people',function(arHash){
		if(arHash[0]==='#peoplePassword' && arHash.length===2) {
			new PeoplePasswordWindow(arHash[1]).show();
		} else if(arHash[0]==='#clientCard' && arHash.length===2) {
			new ClientCardWindow(arHash[1]).show();
		}else if(arHash[0]==='#jurPersonCard' && arHash.length===2) {
			JurPersonCard(arHash[1]);
		} else if(arHash[0]==='#people' && arHash.length===2) {
			PeopleEdit(arHash[1]);
		} else if(arHash[0]==='#peopleContact' && arHash.length>=2) {
			if(arHash.length===2) {
				new PeopleContactsWindow(arHash[1],0).show();
			} else if(arHash.length===3) {
				new PeopleContactsWindow(arHash[1],arHash[2]).show();
			}
		}
	});
});