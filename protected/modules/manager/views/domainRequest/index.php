<?php
/**
 * @var $package Package
 * @var $requests DomainRequest[]
 */?>
<div class="wrapper">
	<div class="editClientWindow" id="sm_content" style="margin-bottom:-12px;">
		<div class="clientHead">
			<?php
			if(isset($package)) {
				echo 'Заказ #'.$package->getNumber().': ';
			}
			?>Список заявок на домен
		</div>
		<div class="clientInfo" style="padding:10px;">
			<table class="tablesorter" style="border:1px solid #B6C3C7;">
				<thead>
					<tr>
						<th>ID</th>
						<th>Домен</th>
						<th>Зона</th>
						<th>Вид регистрации</th>
						<th>Дата заявки</th>
						<th>Статус</th>
						<th>Дата изменения</th>
					</tr>
				</thead>
				<?php foreach($requests as $obRequest):?>
					<tr>
						<td>
							<?php echo $obRequest->id;?>
						</td>
						<td>
							<a href="#domainRequest_<?php echo $obRequest->id;?>" class="domainRequestLink"><?php echo $obRequest->domain;?></a>
						</td>
						<td>
							<?php echo $obRequest->zone;?>
						</td>
						<td>
							<?php echo $obRequest->mode;?>
						</td>
						<td>
							<?php echo $obRequest->date_add;?>
						</td>
						<td>
							<?php echo $obRequest->status;?>
						</td>
						<td>
							<?php echo $obRequest->date_change;?>
						</td>
					</tr>
				<?php endforeach;?>
			</table>
			<div class="buttons" style="padding:10px 0;">
				<?php if(isset($package)) {
					?><a href="#domainRequest_package_<?php echo $package->id?>" class="domainAdd">Добавить заявку</a><?
				} elseif(isset($client)) {
					?><a href="#domainRequest_client_<?php echo $client->id?>" class="domainAdd">Добавить заявку</a><?
				} else {
					?><a href="#domainRequest" class="domainAdd">Добавить заявку</a><?
				}?>
			</div>
		</div>
	</div>
</div>