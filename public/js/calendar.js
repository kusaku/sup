/*global hashHandler:true,
	SupWindow:true,
	TimerObject:true*/
/* Загружаем данные для главной страницы.
 */
function loadCalendar(){
	$.ajax({
		url: '/manager/calendar',
		dataType: 'html',
		success: function(data){
			$('body').append('<div class="calendar" id="calendarDock">' + data + '</div>');
			$('.eventCloseButton').bind('click', function(e){
                var id=$(this).parent().parent().attr("event_id");
				hideEvent(id);
			});
			$('.eventReadyButton').bind('click', function(e){
			    var id=$(this).parent().parent().attr("event_id");
				calendarEventReady(id);
			});
			//$("#calendarDock").slideUp(0); // По умолчанию скрывать события.
			
			if ($("#calendarDock").children().size() > 0) 
				$("#eventsCount").html($("#calendarDock").children().size());
		}
	});
};

/**
 * Отмечаем напоминание как выполненное.
 */
function calendarEventReady(id){
	$.ajax({
		url: '/manager/calendar/ready/' + id,
		dataType: 'html',
		success: function(data){
			if (parseInt(data,10)===1) {
				hideEvent(id);
			}
		}
	});
}

function hideEvent(id){
    $('#event'+id).hide(500);
}

function calendarToggle(){
	$("#calendarDock").slideToggle();
}

var CalendarNotices,NoticesWindow,NoticeEditWindow;

/**
 *
 * @param index
 * @param callback
 * @param parent
 * @constructor
 */
NoticesWindow=function(index,callback,parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.isInit=false;
	this.bRequest=false;
	this.bPositioned=false;
	this.bInitHandlers=false;
	this.index=index;
	this.lastTab=0;
	this.obTabs=null;
};

NoticesWindow.prototype=new SupWindow();
NoticesWindow.prototype.index=0;
NoticesWindow.prototype.reload=function() {
	if(this.bRequest) {
		return this;
	}
	var self=this;
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/calendar/window',
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
NoticesWindow.prototype.loadList=function(obDom) {
	if(this.bRequest || obDom.hasClass('loaded')) {
		return this;
	}
	var index=parseInt(obDom.attr('id').substr(5,1),10),
		self=this;
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/calendar/list',
		data: {'mode':index},
		dataType: 'html',
		success: function(data){
			self.bRequest=false;
			self.obPopup.hideLoading();
			obDom.find('.scrollPanel').append(data);
			obDom.addClass('loaded');
			self.initPanelHandlers(obDom);
		},
		error: function(jqXHR, textStatus, errorThrown){
			alert(textStatus);
			self.close();
		}
	});
	return this;
};
NoticesWindow.prototype.initHandlers=function() {
	if(!this.isInit || this.bInitHandlers) {
		return;
	}
	var self=this;
	var obWin=this.obPopup.body();
	this.obTabs=obWin.find('.tabscontainer').tabs();
	if(!this.bPositioned) {
		obWin.find('.scrollPanel').css('height',Math.max(200,$(window).height()-100));
		this.obPopup._position();
		this.bPositioned=true;
	}
	self.obTabs.tabs('option','activate',function(e,ui){
		self.lastTab=self.obTabs.tabs('option','active');
		if(!ui.newPanel.hasClass('loaded')) {
			self.loadList(ui.newPanel);
		}
	});
	self.initPanelHandlers(obWin.find('#tabs-0'));
	this.bInitHandlers=true;
};
NoticesWindow.prototype.initPanelHandlers=function(obDom) {
	obDom.find('.scrollPanel').jScrollPane({showArrows:true,hideFocus:true,verticalDragMinHeight:20});
	obDom.find('.noticesDelay').each(function(){
		CalendarNotices.prototype.initDropDownButton.call(this,$(this));
	});
};
NoticesWindow.prototype.close=function() {
	SupWindow.prototype.close.call(this);
};

/**
 *
 * @param noticeId
 * @param callback
 * @param parent
 * @constructor
 */
NoticeEditWindow=function(noticeId,callback,parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.isInit=false;
	this.bRequest=false;
	this.bPositioned=false;
	this.bInitHandlers=false;
	this.id=noticeId;
};

NoticeEditWindow.prototype=new SupWindow();
NoticeEditWindow.prototype.id=0;
NoticeEditWindow.prototype.reload=function() {
	if(this.bRequest) {
		return this;
	}
	var self=this;
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/calendar/view/' + self.id,
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
NoticeEditWindow.prototype.save=function(callback) {
	if(this.bRequest) {
		return this;
	}
	var self=this;
	var obForm=self.obPopup.body().find('form#event-edit-form').serialize();
	self.obPopup.showLoading();
	this.bRequest=true;
	$.ajax({
		url: '/manager/calendar/save/',
		data: obForm,
		type: 'post',
		dataType: 'html',
		success: function(data){
			self.bRequest=false;
			self.setContent(data);
			self.isInit=true;
			self.initHandlers();
			if(callback!==undefined) {
				callback();
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			alert(textStatus);
			self.close();
		}
	});
	return this;
};
NoticeEditWindow.prototype.initHandlers=function() {
	if(!this.isInit || this.bInitHandlers) {
		return;
	}
	if(!this.bPositioned) {
		this.obPopup._position();
		this.bPositioned=true;
	}
	var self=this;
	var obWin=this.obPopup.body();
	obWin.find('form#contact-form').submit(function(e){
		e.preventDefault();
		self.save();
	});
	obWin.find('a.buttonSave').click(function(e){
		e.preventDefault();
		self.save();
	});
	obWin.find('a.buttonSaveClose').click(function(e){
		e.preventDefault();
		self.save(function(){
			if(obWin.find('.save-result').length>0) {
				self.close();
			}
		});
	});
	obWin.find('a.buttonCancel').click(function(e){
		e.preventDefault();
		self.close();
	});
	obWin.find('#Calendar_date').datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: "dd.mm.yy",
		changeMonth: true,
		changeYear: true
	});
	this.bInitHandlers=true;
};

/**
 * Объект обеспечивающий работу краткого списка уведомлений
 * @param obDom
 * @constructor
 */
CalendarNotices=function(obDom) {
	if(obDom.length===2) {
		return;
	}
	this.obDom=obDom;
	this.obTimer=false;
	this.bRequest=false;
	this.hash=obDom.find('.list').attr('id');
	this.initHandlers();
};

CalendarNotices.prototype.obDom=null;
CalendarNotices.prototype.obTimer=false;
CalendarNotices.prototype.initHandlers=function() {
	var self=this;
	this.obDom.find('a.noticesAction,a.noticesDelay').each(function(){
		self.initDropDownButton($(this));
	});
	this.obDom.find('a.noticeReady').each(function(){
		self.initReadyButton($(this));
	});
	this.obTimer=new TimerObject(60000);
	this.obTimer.run();
	this.obTimer.append(function(){self.reload();});
};
CalendarNotices.prototype.initReadyButton=function(obButton) {
	var self=this;
	obButton.click(function(e){
		$(this).parent().html('');
		self.hash='';
		self.reload();
	});
};
CalendarNotices.prototype.initDropDownButton=function(obButton) {
	obButton.bind('contextmenu', function(e){
		e.preventDefault();
		return false;
	}).contextMenu({
		menu: obButton.next()
	},function(a,el,pos) {
		return true;
	}).click(function(e){
		e.preventDefault();
		var pos = $(this).offset();
		pos.top+=$(this).outerHeight();
		$(this).showContextMenu(pos);
	});
};
CalendarNotices.prototype.reload=function() {
	if(this.bRequest) {
		return;
	}
	this.bRequest=true;
	var self=this;
	$.ajax({
		url: '/manager/calendar/last',
		data: {'hash':self.hash},
		dataType: 'json',
		success: function(data){
			self.bRequest=false;
			if(data.hasOwnProperty('html')) {
				var obList=self.obDom.find('.list');
				obList.prepend(data.html).attr('id',data.hash);
				self.hash=data.hash;
				obList.scrollTop(35);
				obList.animate({'scrollTop':0},500,function(){
					obList.children().last().remove();
					obList.children().last().remove();
					obList.find('a.noticesAction,a.noticesDelay').each(function(){
						self.initDropDownButton($(this));
					});
					obList.find('a.noticeReady').each(function(){
						self.initReadyButton($(this));
					});
					self.obDom.find('.notifier').attr('class','notifier new');
				});
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			self.obTimer.stop();
		}
	});
};

/**
 * Функция выполняет запрос на откладывание уведомления на указанный срок
 * @param id
 * @param days
 */
function delayNotice(id,minutes,callback) {
	$.ajax({
		url: '/manager/calendar/delay/' + id,
		data: {'minutes':minutes},
		dataType: 'html',
		success: function(data){
			if (parseInt(data,10)===1) {
				if(callback) {
					callback();
				}
				hideEvent(id);
			}
		}
	});
}

var NoticesRow=null;
/**
 * Инициализация существующих элементов при запуске страницы
 */
$(document).ready(function(){
	NoticesRow=new CalendarNotices($('.noticesRow'));
	hashHandler.addHandler('calendar',function(arHash){
		if(arHash[0]==='#notices' && arHash.length===1) {
			new NoticesWindow().show();
		} else if(arHash[0]==='#noticesReady' && arHash.length===2) {
			calendarEventReady(arHash[1]);
		} else if(arHash[0]==='#notice' && arHash.length===2) {
			new NoticeEditWindow(arHash[1]).show();
		} else if(arHash[0]==='#noticeDelay' && arHash.length===3) {
			delayNotice(arHash[1],arHash[2],function(){NoticesRow.reload();});
			document.location.hash='!';
		}
	});
});