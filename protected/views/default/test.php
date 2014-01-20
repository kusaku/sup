<form action="/order/save" method="post">
	<h1>Заказ</h1>
	<div>
		<label for="name">Ваше имя</label>
		<input type="text" id="name" name="name">
	</div>	
	<div>
		<label for="mail">Ваша e-mail</label>
		<input type="text" id="mail" name="mail">
	</div>
	<div>
		<label for="phone">Телефон</label>
		<input type="text" id="phone" name="phone">
	</div>	
<div id="placeholder"></div>
	<input type="submit">
</form>
<script type="text/javascript">
	jQuery.getJSON( '/order', function(data){
		var container = $('<div/>');
		for (var gid in data) {
			var group = data[gid];
			
			$('<h1/>').text(group.name).appendTo(container);			
			for (var sid in group.childs) {
				var child = group.childs[sid];
				var service = $('<div/>');
				$('<label/>').text(child.name).attr('for', 'service['+sid+']').appendTo(service);
				$('<input/>').attr('id', 'service['+sid+']').attr('type', group.exclusive==1 ? 'radio' : 'checkbox').attr('name', child.exclusive ? 'service['+gid+']' : 'service['+sid+']').val(sid).appendTo(service);
				service.appendTo(container);
			}
		}
		console.log(container);
		$('#placeholder').append(container);
		
	} );
</script>
