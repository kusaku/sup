<div class="wrapper">
	<div class="logo">
		<h1><a href="/" title="Вернуться на главную"><img src="/images/logo.png" alt="FS SUP"/></a></h1>
	</div>
	<div class="today">
		<span class="name"><?php echo strftime('%A')?></span> - <?php echo strftime('%B %d, %Y ')?> г.
	</div> 
	<div style="clear:both;"><!-- --></div>
	<div class="documentMenu buttons">
		<div class="button">
			<a href="<?php echo Yii::app()->createUrl('manager/docs2',array('id'=>$package->id))?>" title="Вернуться к списку документов">
				<img src="/images/docs2/buttons/arrow_left.png" alt="" />Назад к документам</a>
		</div>
		<div class="button">
			<a href="<?php echo Yii::app()->createUrl('manager/docs2/'.$this->getAction()->getId(),array('id'=>$package->id,'format'=>'pdf'))?>" title="Открыть в браузере в формате PDF">
				<img src="/images/docs2/buttons/file_extension_pdf.png" alt="" />Посмотреть в PDF</a>
		</div>
		<div class="button">
			<a href="<?php echo Yii::app()->createUrl('manager/docs2/'.$this->getAction()->getId(),array('id'=>$package->id,'format'=>'pdf','download'=>1))?>" title="Скачать в формате PDF">
				<img src="/images/docs2/buttons/file_extension_pdf_save.png" alt="" />Скачать в PDF</a>
		</div>
		<div class="button">
			<a href="<?php echo Yii::app()->createUrl('manager/docs2/'.$this->getAction()->getId(),array('id'=>$package->id))?>" class="printPage" title="Распечатать страницу">
				<img src="/images/docs2/buttons/printer.png" alt="" />Распечатать</a>
		</div>
	</div>
	<div class="documentLayout">
		<div class="documentContent">
			<?php echo $contract; ?>
		</div>
	</div>
</div>