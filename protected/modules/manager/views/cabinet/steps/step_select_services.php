<?php
/**
 * @var Package $package
 * @var CabinetController $this
 */
?>
<div class="tabscontainer modal">
	<ul>
		<li>
			<a href="#tabs-status">Выбор услуг</a>
		</li>
		<li>
			<a href="#tabs-info">Личные данные</a>
		</li>
	</ul>
	<div id="tabs-status" style="padding:10px;">
		<?php
		if($package->getProduct()):
			$arPackageInfo=$package->getPackageInfo();
			?>
			<h4>Клиент заказал <b>"<?php echo $arPackageInfo['product']['title']?>"</b>:</h4>
			<?php echo $arPackageInfo['full_description']?>
			<?php if(isset($arPackageInfo['services']) && count($arPackageInfo['services'])>0):?>
				<div class="line"></div>
				<?php if(count($arPackageInfo['services'])==1):?>
					<h4>а так же дополнительный модуль:</h4>
				<?php else:?>
					<h4>а так же дополнительные модули:</h4>
				<?php endif?>
				<ul>
					<?php foreach($arPackageInfo['services'] as $arItem):?>
						<li><?php echo $arItem['title']?> - <span class="price"><?php echo $arItem['summ']?> руб.</span></li>
					<?php endforeach?>
				</ul>
			<?php endif?>
		<?php else:?>
			Клиент ещё не выбрал продукт.
		<?php endif?>
	</div>
	<div id="tabs-info" style="padding:10px;">
		<?php
		if($package->getProduct()):?>
			<h4>Клиент указал контактные данные:</h4>
			<p>Имя клиента: <b><?php echo $package->client->fio?></b></p>
			<p>E-mail: <b><?php echo $package->client->mail?></b></p>
			<p>Телефон: <b><?php echo $package->client->phone?></b></p>
		<?php else:?>
			Клиент ещё не заполнял контактные данные.
		<?php endif?>
	</div>
</div>