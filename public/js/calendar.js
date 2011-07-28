/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/* Загружаем данные для главной страницы.
 */
function loadCalendar(){
    $.ajax({
        url: '/calendar',
        dataType: 'html',
        success: function(data){
            $('#sup_content').after('<div class="calendar" id="calendarDock">'+data+'</div>');
			$('.eventCloseButton').bind('click', function(){
				hideEvent(this);
			});
			$('.eventReadyButton').bind('click', function(){
				calendarEventReady(this);
			});
			//$("#calendarDock").slideUp(0); // По умолчанию скрывать события.

			if ( $("#calendarDock").children().size() > 0 )
				$("#eventsCount").html($("#calendarDock").children().size());
        }
    });
};


function editCalendarEvent(id){
    showPopUpLoader();
    $.ajax({
        url: '/calendar/' + id,
        dataType: 'html',
        success: function(data){
            $('#sup_popup').html(data);
			showPopUp(); // Окно сформировано - показываем его
			$( "#datepicker" ).datepicker({
				showOtherMonths: true,
				selectOtherMonths: true,
				dateFormat: "yy-mm-dd",
				changeMonth: true,
				changeYear: true
			});
        }
    });
}

/**
 * Отмечаем напоминание как выполненное.
 */
function calendarEventReady(obj){
	id = $(obj).parent().parent().attr("event_id");
    $.ajax({
        url: '/calendar/ready/' + id,
        dataType: 'html',
        success: function(data){
            if (data == '1') hideEvent(obj);
        }
    });
}

function hideEvent(obj){
	$(obj).parent().parent().hide(500);
}

function calendarToggle(){
	$("#calendarDock").slideToggle();
}