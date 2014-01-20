/*global PopupWindow:true*/
var SupWindow;

/**
 * Функция генерирует окно с формой заявки на доменное имя
 *
 * @constructor
 */
SupWindow = function() {};

SupWindow.prototype={
	isInit:false,
	obPopup:false,
	obParent:false,
	obShowCallback:false,
	bInitHandlers:false,
	'init':function(parent,callback) {
		if(typeof(callback) === 'function') {
			this.obShowCallback = callback;
		}
		if(typeof(parent)==='object') {
			this.obParent=parent;
		}
		return this;
	},
	'show':function () {
		var self=this;
		this.obPopup=new PopupWindow({
			'onShow':function(win) {
				self.initHandlers();
				if(self.obShowCallback) {
					self.obShowCallback();
				}
			}, 'onHide':function(win) {
				win.close();
				self.close();
			}
		});
		this.obPopup.show().showLoading();
		this.reload();
		return this;
	},
	'setContent':function(data) {
		this.obPopup.setContent(data);
		this.bInitHandlers=false;
		return this.obPopup;
	},
	'initHandlers':function() {},
	'save':function(callback) {},
	'reload':function() {},
	'close':function() {
		this.obPopup.close();
		window.location.hash='!';
	}
};