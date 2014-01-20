var windowsCounter=0;

var PopupWindow;
PopupWindow = function (params) {
    windowsCounter++;
    var options = {
        'id':'sup_popup_' + windowsCounter,
        'index':windowsCounter,
        'changeHash':true
    };
    this.params = $.extend({}, options, params);
    if ('onClose' in params) {
        this.onClose = params.onClose;
    }
    if ('onHide' in params) {
        this.onHide = params.onHide;
    }
    if ('onShow' in params) {
        this.onShow = params.onShow;
    }
    this._init();
};

PopupWindow.prototype.params={};
PopupWindow.prototype.onClose=false;
PopupWindow.prototype.onShow=false;
PopupWindow.prototype.obWindow=false;
PopupWindow.prototype._init=function() {
	this.obWindow=$('<div class="sup_popup popup"><div class="content"><div style="width:300px;height:200px;background: white;"></div></div></div>').appendTo('body');
	this.obWindow.attr('id',this.params.id).css('z-index',10+this.params.index);
    this.obWindow.mousedown(function(e){
        var iMaxZIndex=10;
        var arWindows=$('.sup_popup:visible');
        var obTopWindow=null;
        arWindows.each(function(){
            var obThis=$(this);
            var zIndex=parseInt(obThis.css('z-index'),10);
            if(zIndex>iMaxZIndex) {
                obTopWindow=obThis;
                iMaxZIndex=zIndex;
            }
        });
        if(obTopWindow!==null) {
            //var iTmp;
            //iTmp=parseInt($(this).css('z-index'),10);
            $(this).css('z-index',iMaxZIndex+1);
            //obTopWindow.css('z-index',iTmp);
        }
        arWindows.each(function(){
			var zIndex=parseInt($(this).css('z-index'),10)-1;
			if(zIndex<10) {
				zIndex=10;
			}
            $(this).css('z-index',zIndex);
        });
    });
	this.addCloseButton();
};
PopupWindow.prototype.addCloseButton=function() {
    if (this.obWindow.children('a.popup_close').length == 0) {
        this.obWindow.append('<a href="#close" class="popup_close"></a>')
        var self = this;
        this.obWindow.children('a.popup_close').click(function (e) {
            e.preventDefault();
            self.hide();
        });
    }
};
PopupWindow.prototype._position=function() {
	var left = Math.round($(document).width() / 2) - Math.round(this.obWindow.width() / 2);
	var top = Math.round(($(window).height() - this.obWindow.height()) / 2);
	
	if(top<0) {
		top=0;
	}
	this.obWindow.css({
		'left': left,
		'top': top,
		'position':'fixed'
	});
};
PopupWindow.prototype._prepareHtml=function() {
	this.obWindow.draggable({
		handle: '.clientHead,.formHead',
		containment: 'parent'
	});
};
PopupWindow.prototype.show=function() {
	this.hidePopUploader();
	this.obWindow.show();
	this._prepareHtml();
	this._position();
	if(this.onShow) {
		this.onShow(this);
	}
	return this;
};
PopupWindow.prototype.hide=function() {
	this.obWindow.hide();
    if(this.params.changeHash) {
        window.location.hash = '!';
    }
	if(this.onHide) {
		this.onHide(this);
	}
	return this;
};
PopupWindow.prototype.close=function() {
	this.obWindow.remove();
};
PopupWindow.prototype.setContent=function(html) {
	this.obWindow.children('.content').html(html);
	return this;
}
PopupWindow.prototype.body=function() {
	return this.obWindow.children('.content');
};
PopupWindow.prototype.showLoading=function() {
	var obWin=this.body();
	if(obWin.find('.windowLoading').length===0) {
		obWin.append('<div class="windowLoading"><img src="/images/preloader.gif" width="220" height="19"/></div>');
	}
};
PopupWindow.prototype.hideLoading=function() {
	var obWin=this.body();
	obWin.find('.windowLoading').remove();
};
PopupWindow.prototype.hidePopUploader=function() {
	hidePopUpLoader();
};
PopupWindow.prototype.showPopUploader=function() {
	showPopUpLoader();
};

//Совместимость со старым кодом
var defaultWindow=false;
$(document).ready(function(){
	defaultWindow=new PopupWindow({id:'sup_popup',onHide:function(){$('#modal').hide()}});
	/*
	 * Прячем попап при нажатии Esc.
	 */
	$(document).keyup(function(e) {
		if (e.keyCode == 27) {
			hidePopUp();
		}
	});
})

function positionPopUp() {
	defaultWindow._position();
}

/* 
 * Показываем всплвающее окно.
 */
function showPopUp(){
	$('#modal').show();
	defaultWindow.show();
	defaultWindow.addCloseButton();
}

/* 
 * Прячем всплвающее окно.
 */
function hidePopUp(){
	defaultWindow.hide();
	window.location.hash='!'
	$('#modal').hide();
}

/* 
 * Показываем всплвающее окно.
 */
function showPopUpLoader(){
	$('#sup_preloader').show();
	
	var left = Math.round($(document).width() / 2) - Math.round($('#sup_preloader').width() / 2);
	if (left < 1) 
		left = 0;
	var top = Math.round($(window).height() / 2) - Math.round($('#sup_preloader').height() / 2);
	if (top < 1) 
		top = 0;
	
	$('#sup_preloader').css('left', left + 'px');
	$('#sup_preloader').css('top', top + 'px');
}

function hidePopUpLoader() {
	$('#sup_preloader').hide();
}

/*
 * Подготовка динимаческого html при его загрузке и изменении
 */
function prepareHtml(){
	// замена стандартных элементов
	// $('select').selectBox(); // Отключено, т.к. возникают сложности
	// $('input[type="checkbox"], input[type="radio"]').radiocheckBox();
	$('#sup_popup').draggable({
		handle: '.clientHead,.formHead',
		containment: 'parent'
	});
}