/*global SupWindow:true,hashHandler:true*/
/**
 * @author aks
 */
/**
 * Список доступных отчетов
 * @param {Object} client_id
 */
function selectReportType(){
	showPopUpLoader();
	$.ajax({
		url: '/manager/report/index',
		dataType: 'html',
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp();
			$( "#sup_popup .datepicker" ).datepicker({
				showOtherMonths: true,
				selectOtherMonths: true,
				dateFormat: "dd.mm.yy",
				changeMonth: true,
				changeYear: true
			});
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').html($('<div style="width:300px;"/>').append($('<h1/>').text(jqXHR.statusText + ':' + jqXHR.status)).append(jqXHR.responseXML ? $('<div/>').html(jqXHR.responseXML) : $('<div/>').text(jqXHR.responseText)).html());
			showPopUp();
		}
	});
}

function generateReport(){
	showPopUpLoader();
	$.ajax({
		url: '/manager/report/generate',
		data: {
			'reportType': $('#reportType').val(),
		   'manager_id': $('#manager_id').val()
		},
		dataType: 'html',
		success: function(data){
			$('#sup_popup').html(data);
			showPopUp();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#sup_popup').html($('<div style="width:300px;"/>').append($('<h1/>').text(jqXHR.statusText + ':' + jqXHR.status)).append(jqXHR.responseXML ? $('<div/>').html(jqXHR.responseXML) : $('<div/>').text(jqXHR.responseText)).html());
			showPopUp();
		}
	});
}

var ReportDetailsWindow;

/**
 * 
 * @param index
 * @param callback
 * @param parent
 * @constructor
 */
ReportDetailsWindow=function(obDom,callback,parent) {
	SupWindow.prototype.init.call(this,parent,callback);
	this.isInit=false;
	this.obDom=obDom;
	this.bPositioned=false;
	this.bInitHandlers=false;
};

ReportDetailsWindow.prototype=new SupWindow();
ReportDetailsWindow.prototype.obDom=null;
ReportDetailsWindow.prototype.reload=function() {
	var self=this;
	self.obPopup.showLoading();
	var sHead='<div class="formWindow" style="width:600px;"><div class="formHead">Подробная информация</div><div class="formBody" style="padding-top:0px;max-height:600px;overflow: auto;">',
	sFoot='</div></div>';
	self.setContent(sHead+self.obDom.html()+sFoot);
	self.isInit=true;
	self.initHandlers();
	return this;
};
ReportDetailsWindow.prototype.initHandlers=function() {
	if(!this.isInit || this.bInitHandlers) {
		return;
	}
	if(!this.bPositioned) {
		this.obPopup._position();
		this.bPositioned=true;
	}
	var self=this;
	var obWin=this.obPopup.body();
	this.bInitHandlers=true;
};

$(document).ready(function(){
	hashHandler.addHandler('reports',function(arHash){
		if(arHash[0]==='#showTotalList' && arHash.length===1) {
			new ReportDetailsWindow($('#totalList')).show();
		} else if(arHash[0]==='#showСonditionallyList' && arHash.length===1) {
			new ReportDetailsWindow($('#conditionallyList')).show();
		} else if(arHash[0]==='#showReallyList' && arHash.length===1) {
			new ReportDetailsWindow($('#reallyList')).show();
		} else if(arHash[0]==='#showPaidList' && arHash.length===1) {
			new ReportDetailsWindow($('#paidList')).show();
		}
	});
});
