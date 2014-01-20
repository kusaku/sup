<div class="wrapper">
	<div class="editClientWindow" id="sm_content">
		<div class="clientHead">
			Заказы использованные в выборке
		</div>
		<div class="clientInfo" style="max-height:300px;overflow:auto;">
			<table width="100%">
				<tr>
					<th>ID</th>
					<th>Название</th>
					<th>Сумма</th>
					<th>Услуга</th>
					<th>Статус</th>
					<th>Дата</th>
					<th>Менеджер</th>
				</tr>
				<?php $summ=0;$count=0;?>
				<?php foreach($rows as $row):?>
					<tr>
						<td><?php echo $row['id']?></td>
						<th><?php echo $row['name']?></th>
						<th><?php echo $row['summ']?></th>
						<th><?php echo $row['serv_id']?></th>
						<th><?php echo $row['status_id']?>/<?php echo $row['payment_id']?></th>
						<th><?php echo $row['date']?></th>
						<th><?php echo $row['manager_id']?></th>
					</tr>
					<?php $summ+=$row['summ'];$count++;?>
				<?php endforeach?>
				<tr>
					<td colspan="2">Итого: <?php echo $count?></td>
					<td><?php echo $summ?></td>
					<td colspan="4"></td>
				</tr>
			</table>
		</div>
	</div>
</div>

