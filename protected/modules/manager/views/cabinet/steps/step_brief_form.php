<?php
/**
 * @var PackageWorkflowStep $step
 * @var Package $package
 * @var CabinetController $this
 */
?>
<div class="tabscontainer modal">
	<ul>
		<li>
			<a href="#tabs-comments">Комментарии</a>
		</li>
		<li>
			<a href="#tabs-status">Состояние шага</a>
		</li>
	</ul>
	<div id="tabs-comments">
		<?php $this->widget('manager.widgets.CommentsWidget',array('key'=>'package_'.$package->id.'_'.$step->text_ident));?>
	</div>
	<div id="tabs-status" style="padding:10px;">
		<?php
		$obWorkflow=$package->initWorkflow();
		$data=$obWorkflow->getData($step->primaryKey);
		echo CHtml::tag('p',array(),'Состояние возможности перехода на следующий шаг:');
		if(is_array($data)) {
			if(isset($data['nextStepAllowed']) && $data['nextStepAllowed']==1) {
				echo CHtml::tag('b',array(),'доступен').CHtml::button('Отключить',array('id'=>'offNextStep'));
			} else {
				echo CHtml::tag('b',array(),'недоступен').CHtml::button('Включить',array('id'=>'onNextStep'));
			}
		} else {
			echo CHtml::tag('b',array(),'недоступен').CHtml::button('Включить',array('id'=>'onNextStep'));
		}
		?>
	</div>
</div>