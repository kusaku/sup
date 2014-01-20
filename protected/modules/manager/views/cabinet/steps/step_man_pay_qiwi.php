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
			<a href="#tabs-status">Выбор Qiwi</a>
		</li>
	</ul>
	<div id="tabs-status" style="padding:10px;">
		<?php
		$obWorkflow=$package->initWorkflow();
		$data=$obWorkflow->getData($step->primaryKey);
		if(is_array($data)) {
			echo "Клиент указал номер телефона для оплаты: <b>".$data['idto'].'</b>';
		} else {
			echo "Клиент ещё не заполнял форму оплаты Qiwi.";
		}
		?>
	</div>
</div>