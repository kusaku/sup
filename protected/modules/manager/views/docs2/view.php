<div class="wrapper">
	<div class="logo">
		<h1><a href="/" title="Вернуться на главную"><img src="/images/logo.png" alt="FS SUP"/></a></h1>
	</div>
	<div class="today">
		<span class="name"><?php echo strftime('%A')?></span> - <?php echo strftime('%B %d, %Y ')?> г.
	</div>
	<div style="clear:both;"><!-- --></div>
	<div>
		<div class="buttons">
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/contract',array('id'=>$package->id))?>" title="Посмотреть актуальную версию договора">
					<img src="/images/docs2/buttons/contractFull.png" alt="" />Договор</a>
			</div>
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/contractOriginal',array('id'=>$package->id))?>" title="Посмотреть неподписанную версию договора">
					<img src="/images/docs2/buttons/contractFullOriginal.png" alt="" />Оригинал договора</a>
			</div>
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/bill',array('id'=>$package->id))?>" title="Посмотреть счёт на оплату">
					<img src="/images/docs2/buttons/bill.png" alt="" />Счёт на оплату</a>
			</div>
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/receipt',array('id'=>$package->id))?>" title="Посмотреть квитанцию на оплату">
					<img src="/images/docs2/buttons/receipt.png" alt="" />Квитанция на оплату</a>
			</div>
			<div class="button">
				<a href="<?php echo Yii::app()->createUrl('manager/docs2/act',array('id'=>$package->id))?>" title="Посмотреть акт">
					<img src="/images/docs2/buttons/act.png" alt="" />Акт о выполнении работ</a>
			</div>
		</div>
		<div class="history">
			<?php $sDate='';?>
			<?php foreach($history as $arDocument):?>
				<?php if($sDate!=date('d.m.Y',strtotime($arDocument['date_create']))):?>
					<?php $sDate=date('d.m.Y',strtotime($arDocument['date_create']));?>
					<div class="date"><span><?php echo $sDate;?></span></div>
				<?php endif?>
				<div class="document">
					<div class="icon">
						<img src="/images/docs2/icons/<?php echo $arDocument['type']?>.png" alt="<?php echo $arDocument['type']?>" width="32" height="32" />
					</div>
					<div class="formats">
						<?php foreach($arDocument['formats'] as $sType=>$arFormat):?>
							<div class="format">
								<?php if($arFormat['storage_format']=='html'):?>
									<a href="<?php echo Yii::app()->createUrl('manager/docs2/download',array('id'=>$arFormat['id']))?>" title="Скачать в HTML"><img src="/images/docs2/icons/downloadHtml.png" alt="" /></a>
									<a href="<?php echo Yii::app()->createUrl('manager/docs2/preview',array('id'=>$arFormat['id']))?>" title="Посмотреть в браузере"><img src="/images/docs2/icons/viewHtml.png" alt="" /></a>
								<?php elseif($arFormat['storage_format']=='pdf'):?>
									<a href="<?php echo Yii::app()->createUrl('manager/docs2/download',array('id'=>$arFormat['id']))?>" title="Скачать в PDF"><img src="/images/docs2/icons/downloadPdf.png" alt="" /></a>
									<a href="<?php echo Yii::app()->createUrl('manager/docs2/preview',array('id'=>$arFormat['id']))?>" title="Посмотреть в браузере"><img src="/images/docs2/icons/viewPdf.png" alt="" /></a>
								<?php else:?>
									<a href="<?php echo Yii::app()->createUrl('manager/docs2/download',array('id'=>$arFormat['id']))?>" title="Скачать"><img src="/images/docs2/icons/downloadRaw.png" alt="" /></a>
								<?php endif?>
							</div>
						<?php endforeach?>
					</div>
					<div class="title">
						<?php if($arDocument['type']=='contractFull'):?>
							<a href="<?php echo Yii::app()->createUrl('manager/docs2/contract',array('id'=>$package->id,'documentId'=>$arDocument['id']))?>"><?php echo $arDocument['title']?></a>
						<?php elseif($arDocument['type']=='contractFullOriginal'):?>
							<a href="<?php echo Yii::app()->createUrl('manager/docs2/contractOriginal',array('id'=>$package->id,'documentId'=>$arDocument['id']))?>"><?php echo $arDocument['title']?></a>
						<?php elseif($arDocument['type']=='bill'):?>
							<a href="<?php echo Yii::app()->createUrl('manager/docs2/bill',array('id'=>$package->id,'documentId'=>$arDocument['id']))?>"><?php echo $arDocument['title']?></a>
						<?php elseif($arDocument['type']=='receipt'):?>
							<a href="<?php echo Yii::app()->createUrl('manager/docs2/receipt',array('id'=>$package->id,'documentId'=>$arDocument['id']))?>"><?php echo $arDocument['title']?></a>
						<?php elseif($arDocument['type']=='act'):?>
							<a href="<?php echo Yii::app()->createUrl('manager/docs2/act',array('id'=>$package->id,'documentId'=>$arDocument['id']))?>"><?php echo $arDocument['title']?></a>
						<?php else:?>
							<?php echo $arDocument['title']?>
						<?php endif?><br/>
						<span class="time"><?php echo date('H:i:s',strtotime($arDocument['date_create']))?></span>
					</div>
					<div style="clear:both;"><!-- --></div>
				</div>
			<?php endforeach;?>
		</div>
	</div>
</div>