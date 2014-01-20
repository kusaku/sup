/*global PopupWindow:true,
 hidePopUpLoader:true,
 DomainRequest:true,
 SupWindow:true,
 hint:true,
 timerObject:true,
 iCurrentUserId:true,
 MessageTemplateWindow: true*/

var CabinetWindow;
CabinetWindow = function (packageId, callback, parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.packageId=packageId;
	this.isInit=true;
	this.bRequest=false;
	this.obTimer=false;
	this.sCurrentStep=0;
	this.iUserStep=0;
	this.iLastUpdateTime=0;
	this.bLasyMode=false;
	this.iTicks=0;
	this.iTicksLimit=0;
	this.iLasyModeTicks=0;
};

CabinetWindow.prototype=new SupWindow();
CabinetWindow.prototype.packageId=0;
CabinetWindow.prototype.isInit=false;
CabinetWindow.prototype.obTimer=false;
CabinetWindow.prototype.sCurrentStep=0;
CabinetWindow.prototype.iUserStep=0;
CabinetWindow.prototype.iLastUpdateTime=0;
CabinetWindow.prototype.bLasyMode=false;
CabinetWindow.prototype.iTicks=0;
CabinetWindow.prototype.iTicksLimit=0;
CabinetWindow.prototype.iLasyModeTicks=0;
/**
 * Метод выполняет отрисовку окна статуса ЛКК с возможностью общения с посетителем через систему комментариев
 *
 * @return boolean|CabinetWindow
 */
CabinetWindow.prototype.show=function () {
	if(!this.isInit) {
		return false;
	}
	var self=this;
	this.obPopup=new PopupWindow({
		'onShow':function(win) {
			if(self.obShowCallback) {
				self.obShowCallback();
			}
		}, 'onHide':function(win) {
			if(self.obTimer) {
				self.obTimer.stop().empty();
			}
			win.close();
		}
	});
	this.obPopup.show().showLoading();
	this.reload();
	return this;
};
/**
 * Метод выполняет загрузку данных формы с сервера и её отрисовку
 */
CabinetWindow.prototype.reload=function() {
	var self=this;
	$.ajax({
		url: '/manager/cabinet/index',
		data: {'packageId':self.packageId},
		dataType: 'html',
		success: function(data){
			self.obPopup.setContent(data).show();
			self.initHandlers();
		},
		error: function(jqXHR, textStatus, errorThrown){
			alert(textStatus);
			self.obPopup.hide();
		}
	});
};
CabinetWindow.prototype.initHandlers=function() {
	var self=this;
	this.obTimer=new TimerObject(5000);
	this.obPopup.body().find('.wm_step').mouseenter(function(){
		$(this).addClass('hover');
	}).mouseleave(function(){
		$(this).removeClass('hover');
	}).click(function(e){
		e.preventDefault();
		var id=$(this).attr('id');
		id=id.replace('wm_step_','');
		self.loadStepData(id);
	});
	this.obTimer.append(function(){self.loadPackageData();}).run();
};
/**
 * Метод выполняет отображение локального процесса загрузки данных
 */
CabinetWindow.prototype.showLocalLoading=function() {
	this.obPopup.body().find('.wm_loader').show();
};
/**
 * Метод выполняет скрытие локального процесса загрузки данных
 */
CabinetWindow.prototype.hideLocalLoading=function() {
	this.obPopup.body().find('.wm_loader').hide();
};
/**
 * Метод выполняет загрузку данных заказа
 */
CabinetWindow.prototype.loadPackageData=function() {
	var self=this;
	if(this.bLasyMode) {
		this.iTicks++;
		if(this.iTicks>this.iTicksLimit) {
			this.iTicks=0;
		} else {
			return;
		}
	}
	this.showLocalLoading();
	$.ajax({
		url: '/manager/cabinet/wizzardStatus',
		data: {'packageId':self.packageId,'time':self.iLastUpdateTime},
		dataType: 'json',
		success: function(data, textStatus, jqXHR){
			self.hideLocalLoading();
			var now=new Date(jqXHR.getResponseHeader('Date'));
			self.iLastUpdateTime=Math.round(now.getTime()/1000);
			if(typeof(data)==='object' && 'step_id' in data) {
				var stepId=data.step_id;
				if(stepId!==self.iUserStep) {
					self.updatePackageStatus(stepId);
					if(self.iUserStep===0) {
						self.loadStepData(stepId);
					}
					self.bLasyMode=false;
					self.iTicksLimit=0;
					self.iLasyModeTicks=0;
					self.iUserStep=stepId;
				}
			} else {
				self.iLasyModeTicks++;
				if(self.iLasyModeTicks>3) {
					self.bLasyMode=true;
					self.iTicksLimit++;
					self.iLasyModeTicks=0;
				}
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			self.hideLocalLoading();
			self.obTimer.stop().empty();
			alert('Ошибка получения состояния заказа');
		}
	});
};
/**
 * Метод выполняет обновление интерфейса состояния мастера
 * @param stepId номер шага, который необходимо активировать
 */
CabinetWindow.prototype.updatePackageStatus=function(stepId) {
	var winBody=this.obPopup.body();
	var obStep=winBody.find('#wm_step_'+stepId);
	if(obStep.length>0) {
		var obPosition=obStep.position();
		winBody.find('.wm_user').animate({left:obPosition.left+2,top:obPosition.top-8},1000,function(){
			winBody.find('.wm_step').removeClass('current done disabled');
			switch(stepId) {
				case 'select_product':
					winBody.find('#wm_step_select_product').addClass('current');
				break;
				case 'select_services':
					winBody.find('#wm_step_select_product').addClass('done');
					winBody.find('#wm_step_select_services').addClass('current');
				break;
				case 'paytype':
					winBody.find('#wm_step_select_product,#wm_step_select_services').addClass('done');
					winBody.find('#wm_step_paytype').addClass('current');
				break;
				case 'man_pay_sb':
					winBody.find('#wm_step_select_product,#wm_step_select_services,#wm_step_paytype').addClass('done');
					winBody.find('#wm_step_fill_rekviz,#wm_step_check_rekviz,#wm_step_man_pay_qiwi,#wm_step_qiwi_pay_form,' +
						'#wm_step_vkredit_request,#wm_step_robokassa_payment').addClass('disabled');
					winBody.find('#wm_step_man_pay_sb').addClass('current');
				break;
				case 'fill_rekviz':
					winBody.find('#wm_step_select_product,#wm_step_select_services,#wm_step_paytype').addClass('done');
					winBody.find('#wm_step_man_pay_sb,#wm_step_man_pay_qiwi,#wm_step_qiwi_pay_form,#wm_step_vkredit_request,' +
						'#wm_step_robokassa_payment').addClass('disabled');
					winBody.find('#wm_step_fill_rekviz').addClass('current');
				break;
				case 'check_rekviz':
					winBody.find('#wm_step_select_product,#wm_step_select_services,#wm_step_paytype,#wm_step_fill_rekviz').addClass('done');
					winBody.find('#wm_step_man_pay_sb,#wm_step_man_pay_qiwi,#wm_step_qiwi_pay_form,#wm_step_vkredit_request,' +
						'#wm_step_robokassa_payment').addClass('disabled');
					winBody.find('#wm_step_check_rekviz').addClass('current');
				break;
				case 'form_domain':
					winBody.find('#wm_step_select_product,#wm_step_select_services,#wm_step_paytype,#wm_step_man_pay_sb,' +
						'#wm_step_fill_rekviz,#wm_step_check_rekviz,#wm_step_man_pay_qiwi,#wm_step_qiwi_pay_form,' +
						'#wm_step_vkredit_request,#wm_step_robokassa_payment').addClass('done');
					winBody.find('#wm_step_form_domain').addClass('current');
				break;
				case 'form_info':
					winBody.find('#wm_step_select_product,#wm_step_select_services,#wm_step_paytype,#wm_step_man_pay_sb,' +
						'#wm_step_fill_rekviz,#wm_step_check_rekviz,#wm_step_man_pay_qiwi,#wm_step_qiwi_pay_form,' +
						'#wm_step_vkredit_request,#wm_step_robokassa_payment,#wm_step_form_domain').addClass('done');
					winBody.find('#wm_step_form_info').addClass('current');
					break;
				case 'payment_waiting':
					winBody.find('#wm_step_select_product,#wm_step_select_services,#wm_step_paytype,#wm_step_man_pay_sb,' +
						'#wm_step_fill_rekviz,#wm_step_check_rekviz,#wm_step_man_pay_qiwi,#wm_step_qiwi_pay_form,' +
						'#wm_step_vkredit_request,#wm_step_robokassa_payment,#wm_step_form_domain,#wm_step_form_info').addClass('done');
					winBody.find('#wm_step_payment_waiting').addClass('current');
				break;
				case 'design_form':
					winBody.find('#wm_step_select_product,#wm_step_select_services,#wm_step_paytype,#wm_step_man_pay_sb,' +
						'#wm_step_fill_rekviz,#wm_step_check_rekviz,#wm_step_man_pay_qiwi,#wm_step_qiwi_pay_form,' +
						'#wm_step_vkredit_request,#wm_step_robokassa_payment,#wm_step_form_domain,#wm_step_form_info,#wm_step_payment_waiting').addClass('done');
					winBody.find('#wm_step_design_form').addClass('current');
				break;
				case 'brief_form':
					winBody.find('#wm_step_select_product,#wm_step_select_services,#wm_step_paytype,#wm_step_man_pay_sb,' +
						'#wm_step_fill_rekviz,#wm_step_check_rekviz,#wm_step_man_pay_qiwi,#wm_step_qiwi_pay_form,' +
						'#wm_step_vkredit_request,#wm_step_robokassa_payment,#wm_step_form_domain,#wm_step_form_info,' +
						'#wm_step_payment_waiting,#wm_step_design_form').addClass('done');
					winBody.find('#wm_step_brief_form').addClass('current');
				break;
				case 'waiting':
					winBody.find('#wm_step_select_product,#wm_step_select_services,#wm_step_paytype,#wm_step_man_pay_sb,' +
						'#wm_step_fill_rekviz,#wm_step_check_rekviz,#wm_step_man_pay_qiwi,#wm_step_qiwi_pay_form,' +
						'#wm_step_vkredit_request,#wm_step_robokassa_payment,#wm_step_form_domain,#wm_step_form_info,' +
						'#wm_step_payment_waiting,#wm_step_design_form,#wm_step_brief_form').addClass('done');
					winBody.find('#wm_step_waiting').addClass('current');
				break;
				case 'man_pay_qiwi':
					winBody.find('#wm_step_select_product,#wm_step_select_services,#wm_step_paytype').addClass('done');
					winBody.find('#wm_step_fill_rekviz,#wm_step_check_rekviz,' +
						'#wm_step_vkredit_request,#wm_step_robokassa_payment,#wm_step_man_pay_sb').addClass('disabled');
					winBody.find('#wm_step_man_pay_qiwi').addClass('current');
				break;
				case 'qiwi_pay_form':
					winBody.find('#wm_step_select_product,#wm_step_select_services,#wm_step_paytype,#wm_step_man_pay_qiwi').addClass('done');
					winBody.find('#wm_step_fill_rekviz,#wm_step_check_rekviz,' +
						'#wm_step_vkredit_request,#wm_step_robokassa_payment,#wm_step_man_pay_sb').addClass('disabled');
					winBody.find('#wm_step_qiwi_pay_form').addClass('current');
				break;
				case 'vkredit_request':
					winBody.find('#wm_step_select_product,#wm_step_select_services,#wm_step_paytype,#wm_step_man_pay_qiwi').addClass('done');
					winBody.find('#wm_step_fill_rekviz,#wm_step_check_rekviz,#wm_step_man_pay_qiwi,#wm_step_qiwi_pay_form,' +
						'#wm_step_robokassa_payment,#wm_step_man_pay_sb').addClass('disabled');
					winBody.find('#wm_step_vkredit_request').addClass('current');
				break;
				case 'robokassa_payment':
					winBody.find('#wm_step_select_product,#wm_step_select_services,#wm_step_paytype,#wm_step_man_pay_qiwi').addClass('done');
					winBody.find('#wm_step_fill_rekviz,#wm_step_check_rekviz,#wm_step_man_pay_qiwi,#wm_step_qiwi_pay_form,' +
						'#wm_step_vkredit_request,#wm_step_man_pay_sb').addClass('disabled');
					winBody.find('#wm_step_robokassa_payment').addClass('current');
				break;
			}
		});
	}
};
/**
 * Метод выполняет загрузку данных о шаге с сервера
 * @param stepId - номер шага, данные которого необходимо загрузить
 */
CabinetWindow.prototype.loadStepData=function(stepId) {
	var self=this;
	var winBody=self.obPopup.body();
	winBody.find('.wm_step.active').removeClass('active');
	winBody.find('#wm_step_'+stepId).addClass('active');
	this.showLocalLoading();
	$.ajax({
		url: '/manager/cabinet/wizzardStepData',
		data: {'packageId':this.packageId,'stepId':stepId},
		dataType: 'json',
		success: function(data){
			self.hideLocalLoading();
			if('content' in data) {
				winBody.find('.step_data').html(data.content);
				winBody.find('.tabscontainer.modal').tabs();
				winBody.find('.scrollPanel').jScrollPane({showArrows:true});
			}
			var sLeaveFuncName='_leave_'+self.sCurrentStep;
			if(sLeaveFuncName in self) {
				self[sLeaveFuncName]();
			}
			self.sCurrentStep=stepId;
			if('init' in data && data.init in self) {
				self[data.init]();
			}
		},
		error: function(jqXHR, textStatus, errorThrown){
			self.hideLocalLoading();
			alert('Ошибка получения данных шага мастера');
		}
	});
};
/**
 * Метод вызывается при переключении с шага ожидания
 * @private
 */
CabinetWindow.prototype._leave_waiting=function() {
	var self=this;
	this.obPopup.body().find('#wm_step_waiting .im_icon').removeClass('online');
	this.obTimer.empty().append(function(){self.loadPackageData();}).run();
};
CabinetWindow.prototype._leave_payment_waiting=function() {
	var self=this;
	this.obPopup.body().find('#wm_step_payment_waiting .im_icon').removeClass('online');
	this.obTimer.empty().append(function(){self.loadPackageData();}).run();
};
CabinetWindow.prototype._leave_design_form=function() {
	var self=this;
	this.obPopup.body().find('#wm_step_design_form .im_icon').removeClass('online');
	this.obTimer.empty().append(function(){self.loadPackageData();}).run();
};
CabinetWindow.prototype._leave_brief_form=function() {
	var self=this;
	this.obPopup.body().find('#wm_step_brief_form .im_icon').removeClass('online');
	this.obTimer.empty().append(function(){self.loadPackageData();}).run();
};

CabinetWindow.prototype._initFormDomainStep=function() {
	//alert('Init');
};
/**
 * Метод вызывается при отображении шага ожидания завершения работ над заказом
 * @private
 */
CabinetWindow.prototype._initWaitingStep=function() {
	var winBody,wave;
	winBody=this.obPopup.body();
	wave=new WaveBlock('waiting',winBody,this.packageId);
	this.obTimer.append(wave.onTimer);
};

/**
 * Метод вызывается при отображении шага ожидания оплаты заказа
 * @private
 */
CabinetWindow.prototype._initPaymentWaitingStep=function() {
	var winBody,wave;
	winBody=this.obPopup.body();
	wave=new WaveBlock('payment_waiting',winBody,this.packageId);
	this.obTimer.append(wave.onTimer);
};

/**
 * Метод вызывается при отображении шага ожидания завершения работ над заказом
 * @private
 */
CabinetWindow.prototype._initDesignFormStep=function() {
	var winBody,wave,self;
	self=this;
	winBody=this.obPopup.body();
	wave=new WaveBlock('design_form',winBody,this.packageId);
	winBody.find('#offNextStep').click(function(e){
		e.preventDefault();
		if($(this).hasClass('loading')) {
			return;
		}
		$(this).addClass('loading');
		var button=$(this);
		self.showLocalLoading();
		$.ajax({
			url: '/manager/cabinet/designFormRequest',
			data: {'packageId':self.packageId,'act':'lock'},
			dataType: 'json',
			success: function(data){
				button.removeClass('loading');
				self.hideLocalLoading();
				if(data.hasOwnProperty('done') && data.done===1) {
					self.loadStepData(self.sCurrentStep);
				} else {
					alert('Ошибка блокировки шага дизайна');
				}
			},
			error: function(jqXHR, textStatus, errorThrown){
				self.hideLocalLoading();
				alert('Ошибка блокировки шага дизайна');
			}
		});
	});
	winBody.find('#onNextStep').click(function(e){
		e.preventDefault();
		if($(this).hasClass('loading')) {
			return;
		}
		$(this).addClass('loading');
		var button=$(this);
		self.showLocalLoading();
		$.ajax({
			url: '/manager/cabinet/designFormRequest',
			data: {'packageId':self.packageId,'act':'unlock'},
			dataType: 'json',
			success: function(data){
				button.removeClass('loading');
				self.hideLocalLoading();
				if(data.hasOwnProperty('done') && data.done===1) {
					self.loadStepData(self.sCurrentStep);
				} else {
					alert('Ошибка разблокировки шага дизайна');
				}
			},
			error: function(jqXHR, textStatus, errorThrown){
				self.hideLocalLoading();
				alert('Ошибка разблокировки шага дизайна');
			}
		});
	});
	this.obTimer.append(wave.onTimer);
};

/**
 * Метод вызывается при отображении шага ожидания завершения работ над заказом
 * @private
 */
CabinetWindow.prototype._initBriefFormStep=function() {
	var winBody,wave,self;
	self=this;
	winBody=this.obPopup.body();
	wave=new WaveBlock('brief_form',winBody,this.packageId);
	winBody.find('#offNextStep').click(function(e){
		e.preventDefault();
		if($(this).hasClass('loading')) {
			return;
		}
		$(this).addClass('loading');
		var button=$(this);
		self.showLocalLoading();
		$.ajax({
			url: '/manager/cabinet/briefFormRequest',
			data: {'packageId':self.packageId,'act':'lock'},
			dataType: 'json',
			success: function(data){
				button.removeClass('loading');
				self.hideLocalLoading();
				if(data.hasOwnProperty('done') && data.done===1) {
					self.loadStepData(self.sCurrentStep);
				} else {
					alert('Ошибка блокировки шага дизайна');
				}
			},
			error: function(jqXHR, textStatus, errorThrown){
				self.hideLocalLoading();
				alert('Ошибка блокировки шага дизайна');
			}
		});
	});
	winBody.find('#onNextStep').click(function(e){
		e.preventDefault();
		if($(this).hasClass('loading')) {
			return;
		}
		$(this).addClass('loading');
		var button=$(this);
		self.showLocalLoading();
		$.ajax({
			url: '/manager/cabinet/briefFormRequest',
			data: {'packageId':self.packageId,'act':'unlock'},
			dataType: 'json',
			success: function(data){
				button.removeClass('loading');
				self.hideLocalLoading();
				if(data.hasOwnProperty('done') && data.done===1) {
					self.loadStepData(self.sCurrentStep);
				} else {
					alert('Ошибка разблокировки шага дизайна');
				}
			},
			error: function(jqXHR, textStatus, errorThrown){
				self.hideLocalLoading();
				alert('Ошибка разблокировки шага дизайна');
			}
		});
	});
	this.obTimer.append(wave.onTimer);
};

function WaveBlock(stepId,obDom,packageId) {
	var bCanUpload,itemsScroll,filesScroll,inputField;
	bCanUpload=false;
	obDom.find('.comments .leftColumn .items').jScrollPane({'stickToBottom':true,showArrows:true,hideFocus:true,verticalDragMinHeight:20});
	itemsScroll=obDom.find('.comments .leftColumn .items').data('jsp');
	itemsScroll.scrollToPercentY(100);
	obDom.find('.comments .rightColumn .fileList').jScrollPane({'stickToBottom':true,showArrows:true,hideFocus:true,verticalDragMinHeight:20});
	filesScroll=obDom.find('.comments .rightColumn .fileList').data('jsp');
	filesScroll.scrollToPercentY(100);
	inputField=obDom.find('.comments .input form textarea');

	/**
	* Функция выполняет отмену режима редактирования
	*/
	function cancelEdit() {
		var obForm=$(this).parentsUntil('form').parent();
		obForm.fadeOut(300,function(){
			$(this).prev().fadeTo(300,1,function(){
				$(this).next().show();
			});
			$(this).remove();
		});
	}
	/**
	* Функция выполняет сохранение редактирования
	*/
	function saveEdit() {
		var obForm=$(this).parentsUntil('form').parent();
		obForm.prev().addClass('loading');
		cancelEdit.call(this);
		$.ajax({
			url: obForm.attr('action'),
			data: obForm.serialize(),
			dataType: 'json',
			type:'POST',
			success: function(data){
				obForm.prev().removeClass('loading');
				if('post' in data) {
					obForm.prev().html(data.post.content);
				}
				itemsScroll.reinitialise();
			},
			error: function(jqXHR, textStatus, errorThrown){
				obForm.prev().removeClass('loading');
				alert('Ошибка отправки сообщения');
			}
		});
	}
	/**
	* Функция выполняет редактирование поста
	* @param postId - номер сообщения, которое необходимо отредактировать
	*/
	function editPost(postId) {
		var obItem=itemsScroll.getContentPane().find('#comment'+postId);
		var obTemplate=obDom.find('#waveItemEditTemplate').children().clone();
		if(obItem.length>0) {
			var obText=obItem.find('.text');
			var height=Math.max(obText.height(),34);
			obTemplate.attr('action',decodeURIComponent(obTemplate.attr('action')).replace('#ID#',postId));
			obTemplate.height(height);
			obTemplate.find('textarea').val(obText.text());
			obTemplate.find('a.cancel').click(function(e){
				e.preventDefault();
				cancelEdit.call(this);
			});
			obTemplate.find('a.save').click(function(e){
				e.preventDefault();
				saveEdit.call(this);
			});
			obText.after(obTemplate);
			obText.next().fadeOut(0);
			obText.fadeOut(300,function(){
				$(this).next().fadeIn(300);
			});
			obItem.children('.bar').hide();
		}
	}
	/**
	* Функции выполняыемые в рамках шага ожидания
	* @param postData данные содержащие информацию о сообщении
	*/
	function addPost(postData) {
		var obTemplate=obDom.find('#waveItemTemplate').children().clone();
		if(obTemplate.length>0) {
			obTemplate.attr('id','comment'+postData.id);
			obTemplate.find('.info>.date').html(postData.date_add);
			obTemplate.find('.info>.author').html(postData.author.fio || postData.author.mail);
			if(postData.author.is_manager) {
				obTemplate.addClass('manager');
				if(postData.author.id===iCurrentUserId) {
					obTemplate.find('.bar').html('<a href="#edit" class="edit" title="Отредактировать сообщение"><img src="/images/comments/pencil.png" alt="Отредактировать" /></a>');
					obTemplate.find('.bar a.edit').click(function(e){
						e.preventDefault();
						editPost(postData.id);
					});
				}
			} else {
				obTemplate.addClass('user');
			}
			if('avatar' in postData.author && postData.author.avatar!=='') {
				obTemplate.find('.avatarImg').html('<img src="'+postData.author.avatar+'" alt="'+(postData.author.fio || postData.author.mail)+'"/>');
			}
			obTemplate.find('.text').html(postData.content);
			itemsScroll.getContentPane().append(obTemplate);
			itemsScroll.reinitialise();
		}
	}

	function showMessageTemplateWindow() {
		if(!window.hasOwnProperty('MessageTemplateWindow')) {
			$.getScript('/js/messageTemplate.js',function(){
				var obCabinetWindow = new MessageTemplateWindow(inputField);
				obCabinetWindow.show();
				return obCabinetWindow;
			});
		} else {
			var obCabinetWindow = new MessageTemplateWindow(inputField);
			obCabinetWindow.show();
			return obCabinetWindow;
		}
	}

	//Установка обработчиков кнопки редактирования поста
	itemsScroll.getContentPane().find('.bar a.edit').click(function(e){
		e.preventDefault();
		var id=$(this).parentsUntil('.item').parent().attr('id');
		if(id) {
			id=id.substring(7,id.length);
			editPost(id);
		}
	});

	//Развешиваем обработчики на кнопки
	obDom.find('.comments .input form').submit(function(e){
		e.preventDefault();
		obDom.find('.wm_loader').show();
		$.ajax({
			url: $(this).attr('action'),
			data: $(this).serialize(),
			dataType: 'json',
			type:'POST',
			success: function(data){
				obDom.find('.wm_loader').hide();
				if('post' in data) {
					addPost(data.post);
				}
				obDom.find('.comments .input textarea').val('');
			},
			error: function(jqXHR, textStatus, errorThrown){
				obDom.find('.wm_loader').hide();
				alert('Ошибка отправки сообщения');
			}
		});
	});
	inputField.keydown(function(e){
		if (e.keyCode === 13 && e.ctrlKey) {
			obDom.find('.comments .input form').submit();
			e.preventDefault();
		}
		if (e.keyCode === 13 && e.shiftKey) {
			e.preventDefault();
			showMessageTemplateWindow();
		}
	});
	obDom.find('.comments .input a.waveMessageTemplates').click(function(e){
		e.preventDefault();
		showMessageTemplateWindow();
	});
	obDom.find('.comments .fileUpload form input.file').change(function(e){
		var filename=$(this).val();
		var pos=0;
		if(filename!=='') {
			bCanUpload=true;
		}
		if ((pos = filename.lastIndexOf('\\')) || (pos = filename.lastIndexOf('/'))) {
			filename = filename.substr(pos + 1);
		}
		obDom.find('.comments .fileUpload form .uploadField input').val(filename);
	});
	obDom.find('.comments .fileUpload form').submit(function(e){
		if(!bCanUpload) {
			alert('Выберите файл');
			e.preventDefault();
			return;
		}
		bCanUpload=false;
		$(this).attr('target','_ajaxUpload');
		obDom.find('.comments #_ajaxUpload').unbind('load').load(function(){
			var ret = frames._ajaxUpload.document.getElementsByTagName("body")[0].innerHTML;
			var data=$.parseJSON(ret);
			if('error' in data) {
				alert(data.errorMessage);
				obDom.find('.comments .fileUpload form input.file').trigger('change');
			} else {
				filesScroll.getContentPane().empty();
				obDom.find('.comments .fileUpload form .uploadField input').val('');
				for(var ii in data.files) {
					var obTemplate,urlTemplate;
					obTemplate=obDom.find('#waveFileTemplate').children().clone();
					if(obTemplate.length>0) {
						obTemplate.find('.icon').addClass(data.files[ii].icon);
						if(data.files[ii].type==='document') {
							urlTemplate=$('#waveFileDocumentUrlTemplate').val();
							obTemplate.find('.title a').attr('href',urlTemplate.replace('%23ID%23',data.files[ii].document.id));
						} else {
							urlTemplate=$('#waveFilePreviewUrlTemplate').val();
							obTemplate.find('.title a').attr('href',urlTemplate.replace('%23ID%23',data.files[ii].id));
						}
						if(data.files[ii].author.is_manager) {
							obTemplate.addClass('manager');
						} else {
							obTemplate.addClass('user');
						}
						obTemplate.find('.title a').html(data.files[ii].title);
						obTemplate.find('.date').html(data.files[ii].date_add);
						filesScroll.getContentPane().append(obTemplate);
						filesScroll.reinitialise();
					}
				}
			}
		});
	});

	this.onTimer=function(){
		var urlTemplate=decodeURIComponent($('#waveGetUrlTemplate').val());
		var id=itemsScroll.getContentPane().find('.item:last').attr('id');
		if(!id) {
			id=0;
		} else {
			id=id.substring(7,id.length);
		}
		var url=urlTemplate.replace('#ID#',id);
		$.ajax({
			url: url,
			data: {packageId:packageId},
			dataType: 'json',
			type:'GET',
			success: function(data){
				if('posts' in data) {
					for(var ii in data.posts) {
						addPost(data.posts[ii]);
					}
				}
				if('userOnline' in data && data.userOnline) {
					obDom.find('#wm_step_'+stepId+' .im_icon').addClass('online');
				} else {
					obDom.find('#wm_step_'+stepId+' .im_icon').removeClass('online');
				}
			},
			error: function(jqXHR, textStatus, errorThrown){
				console.log('Ошибка получения сообщений');
			}
		});
	}
}