/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/*
 *	Сохранение заказа. Это новый или не оплаченный заказ.
 */
function packSave(){
	document.forms['megaform'].submit();
}

/*
 *	Обновление заказа. Это оплаченный заказ - привязываем сайт или меняем менеджера.
 */
function packUpdate(){
	var error = false;
	$('.redmineMessage').each(function(){
		if ($(this).val() != ''){
			error = true;
		}
	});

	if (error)
		if (!confirm("Есть не сохранённые сообщения! \nOK - Вернуться к редактированию заказа? \nОтмена - сохранить изменения проекта, но потерять сообщения!")) {
			error = false;
		}

	if (!error){
		document.forms['megaform'].submit();
	}

}

/*
 *	По заказу выполнены все работы. Закрываем все задачи.
 */
function packageIsReady(package_id, liid){
	$('#modal').fadeIn(0);
	if (package_id != null) {
		$.ajax({
			url: '/package/packageIsReady/' + package_id,
			dataType: 'html',
			success: function(data){
				$('#li' + liid).replaceWith(data);
				flagsUpdate();
				$('#modal').fadeOut(0);
			},
			error: function(jqXHR, textStatus, errorThrown){
				$('#modal').fadeOut(0);
				$('#li' + liid).replaceWith($('<span/>').text(textStatus));
			}
		});
	}
	$('#modal').fadeOut(0);
}