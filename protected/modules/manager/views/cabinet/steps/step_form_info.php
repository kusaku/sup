<?php
/**
 * @var Package $package
 * @var CabinetController $this
 * @var PackageWorkflowStep $step
 */
?>
<div class="tabscontainer modal">
	<ul>
		<li>
			<a href="#tabs-status">Результаты заполнения анкеты</a>
		</li>
	</ul>
	<div id="tabs-status" style="padding:10px;">
		<?php if($package->questionnaire):?>
		<div class="questionnaire-page">
			<?php foreach($package->questionnaire as $obItem):?>
				<table class="questionnaire">
					<col width="200" />
					<col width="600" />
					<tr>
						<td align="right" valign="top">Дата заполнения:</td>
						<td align="left" valign="top"><?php echo $obItem->date_filled?></td>
					</tr>
					<tr>
						<td align="right" valign="top">Тематика:</td>
						<td align="left" valign="top"><?php echo htmlspecialchars($obItem->description)?></td>
					</tr>
					<tr>
						<td align="right" valign="top">Цвета:</td>
						<td align="left" valign="top"><?php echo htmlspecialchars($obItem->colors)?></td>
					</tr>
					<tr>
						<td align="right" valign="top">Сайты которые нравятся:</td>
						<td align="left" valign="top"><?php echo htmlspecialchars($obItem->favorite_sites)?></td>
					</tr>
				</table>
			<?php endforeach?>
		</div>
		<?php else:?>
			<div>Пользователь ещё не заполнял анкету к этому заказу</div>
		<?php endif?>
	</div>
</div>