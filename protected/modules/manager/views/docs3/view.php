<div class="wrapper">
	<div class="logo">
		<h1><a href="/" title="Вернуться на главную"><img src="/images/logo.png" alt="FS SUP"/></a></h1>
	</div>
	<div class="today">
		<span class="name"><?php echo strftime('%A')?></span> - <?php echo strftime('%B %d, %Y ')?> г.
	</div>
	<div style="clear:both;"><!-- --></div>
	<div>
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
									<a href="<?php echo Yii::app()->createUrl('manager/docs3/download',array('id'=>$arFormat['id'],'user_id'=>$user->id))?>" title="Скачать в HTML"><img src="/images/docs2/icons/downloadHtml.png" alt="" /></a>
									<a href="<?php echo Yii::app()->createUrl('manager/docs3/preview',array('id'=>$arFormat['id'],'user_id'=>$user->id))?>" title="Посмотреть в браузере"><img src="/images/docs2/icons/viewHtml.png" alt="" /></a>
								<?php elseif($arFormat['storage_format']=='pdf'):?>
									<a href="<?php echo Yii::app()->createUrl('manager/docs3/download',array('id'=>$arFormat['id'],'user_id'=>$user->id))?>" title="Скачать в PDF"><img src="/images/docs2/icons/downloadPdf.png" alt="" /></a>
									<a href="<?php echo Yii::app()->createUrl('manager/docs3/preview',array('id'=>$arFormat['id'],'user_id'=>$user->id))?>" title="Посмотреть в браузере"><img src="/images/docs2/icons/viewPdf.png" alt="" /></a>
								<?php else:?>
									<a href="<?php echo Yii::app()->createUrl('manager/docs3/download',array('id'=>$arFormat['id'],'user_id'=>$user->id))?>" title="Скачать"><img src="/images/docs2/icons/downloadRaw.png" alt="" /></a>
								<?php endif?>
							</div>
						<?php endforeach?>
					</div>
					<div class="title">
						<?php echo $arDocument['title']?><br/>
						<span class="time"><?php echo date('H:m:s',strtotime($arDocument['date_create']))?></span>
					</div>
					<div style="clear:both;"><!-- --></div>
				</div>
			<?php endforeach;?>
		</div>
	</div>
</div>