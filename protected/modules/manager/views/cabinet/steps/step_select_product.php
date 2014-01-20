<?php
/**
 * @var Package $package
 * @var CabinetController $this
 */
?>
	<div class="tabscontainer modal">
	<ul>
		<li>
			<a href="#tabs-status">Выбор продукта</a>
		</li>
	</ul>
	<div id="tabs-comments" style="padding:10px;">
		<?php
			$obProduct=$package->getProduct();
			if($obProduct):?>
				Клиент выбрал продукт <b><?php echo $obProduct->getTitle();?></b>.
		<?php else:?>
			Клиент ещё не выбрал продукт. Ему доступен выбор из следующих продуктов:
			<ul>
				<li><b>Сайт-визитка</b></li>
				<li><b>Пакет "малый бизнес"</b></li>
				<li><b>Официальный сайт</b></li>
				<li><b>Сайт-магазин</b></li>
				<li><b>Корпоративный сайт</b></li>
				<li><b>Пакет "сайт-учителя"</b></li>
			</ul>
		<?php endif;?>
	</div>
</div>