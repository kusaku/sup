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
			<a href="#tabs-status">Выбор способа оплаты</a>
		</li>
	</ul>
	<div id="tabs-status" style="padding:10px;">
		<?php
		$obWorkflow=$package->initWorkflow();
		$data=$obWorkflow->getData($step->primaryKey);
		if($data!='') {
			if($obPayMethod=PayMethod::model()->findByPk($data)) {
				echo "Клиент выбрал способ оплаты:<b>".$obPayMethod->category->title.'/'.$obPayMethod->title.'</b>';
				if($obPayMethod->payer_type=='man') {
					echo ", мы считаем этого клиента <b>физическим лицом</b>";
				} else {
					echo ", мы считаем этого клиента <b>юридическим лицом</b>";
				}
				echo ".";
			} else {
				echo "Клиент выбрал способ оплаты на данный момент не зарегистрированный в системе.";
			}
		} else {
			echo "Клиент ещё не выбирал способ оплаты.";
		}
		?>
	</div>
</div>