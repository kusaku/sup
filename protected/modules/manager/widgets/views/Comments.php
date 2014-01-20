<div class="comments">
	<div class="leftColumn">
		<div class="items">
			<?php if($posts):
				foreach($posts as $arPost):?>
					<div class="item <?php echo $arPost['author']['is_manager']?'manager':'user'?>" id="comment<?php echo $arPost['id']?>">
						<div class="avatar"><div class="avatarImg">
							<?php if(isset($arPost['author']['avatar']) && $arPost['author']['avatar']!=''):?>
								<img src="<?php echo $arPost['author']['avatar']?>" alt="<?php echo $arPost['author']['fio']?$arPost['author']['fio']:$arPost['author']['mail']?>"/>
							<?php endif?>
						</div></div>
						<div class="info">
							<span class="author"><?php echo $arPost['author']['fio']?$arPost['author']['fio']:$arPost['author']['mail']?></span>
							<span class="date"><?php echo $arPost['date_add']?></span>
						</div>
						<div class="text"><?php 
						  echo nl2br(strip_tags($arPost['content']));
						?></div>
						<?php if($arPost['author']['is_manager'] && $arPost['author']['id']==Yii::app()->user->id):?>
						    <div class="bar"><a href="#edit" class="edit" title="Отредактировать сообщение"><img src="/images/comments/pencil.png" alt="Отредактировать" /></a></div>
						<?php endif?>
					</div>
				<?php endforeach;
			endif;?>
		</div>
		<div class="input">
			<form action="<?php echo Yii::app()->createUrl('manager/cabinet/waveAdd')?>" method="post">
				<input type="hidden" name="key" value="<?php echo $wave->text_ident?>" />
				<textarea name="content"></textarea>
				<a href="#waveMessageTemplates" class="waveMessageTemplates"><img src="/images/comments/text.png" alt="Выбрать из шаблона (Shift+Enter)" title="Выбрать из шаблона (Shift+Enter)" /></a>
				<input type="submit" name="send" value="Отправить (Ctrl+Enter)" class="send" />
			</form> 
		</div>
	</div>
	<div class="rightColumn">
		<div class="fileList">
			<?php if($attachments):
				foreach($attachments as $arFile):?>
					<div class="item <?php echo $arFile['author']['is_manager']?'manager':'user'?>">
						<div class="icon <?php echo $arFile['icon']?>"></div>
						<?php if($arFile['type']=='document'):?>
							<div class="title"><a href="<?php echo Yii::app()->createUrl('manager/docs2/preview',array('id'=>$arFile['document']['id']))?>" target="_blank"><?php echo $arFile['title']?></a></div>
						<?php else:?>
							<div class="title"><a href="<?php echo Yii::app()->createUrl('manager/cabinet/waveDownload',array('id'=>$arFile['id']))?>" target="_blank"><?php echo $arFile['title']?></a></div>
						<?php endif?>
						<div class="date"><?php echo $arFile['date_add']?></div>
					</div>
				<?php endforeach;
			endif;?>
		</div>
		<div class="fileUpload">
			<form action="<?php echo Yii::app()->createUrl('manager/cabinet/waveUpload')?>" method="post" enctype="multipart/form-data">
				<input type="hidden" name="key" value="<?php echo $wave->text_ident?>" />
				<div class="uploadField">
					<input type="text" name="filename" value=""/>
				</div>
				<input type="file" name="file" class="file" />
				<input type="submit" name="send" value="Загрузить" class="send" /> 
			</form>
			<iframe id="_ajaxUpload" name="_ajaxUpload" src="" frameborder="no" style="width:0px;height:0px;"></iframe>
		</div>
	</div>	
</div>
<div id="waveItemTemplate" style="display:none;">
	<div class="item">
		<div class="avatar"><div class="avatarImg"></div></div>
		<div class="info">
			<span class="author"></span>
			<span class="date"></span>
		</div>
		<div class="text"></div>
		<div class="bar"></div>
	</div>
</div>
<div id="waveItemEditTemplate" style="display:none;">
    <form action="<?php echo Yii::app()->createUrl('manager/cabinet/waveUpdate',array('key'=>$wave->text_ident,'id'=>'#ID#'))?>" class="commentEditForm">
        <textarea name="text"></textarea>
        <div class="bar">
            <a href="#save" class="save">Сохранить</a>
            <a href="#cancel" class="cancel">Отменить</a>
        </div>
    </form>
</div>
<div id="waveFileTemplate" style="display:none;">
	<div class="item">
		<div class="icon"></div>
		<div class="title"><a href="" target="_blank"></a></div>
		<div class="date"></div>
	</div>
</div>
<input type="hidden" id="waveFileDocumentUrlTemplate" value="<?php echo Yii::app()->createUrl('manager/docs2/preview',array('id'=>'#ID#'))?>" />
<input type="hidden" id="waveFilePreviewUrlTemplate" value="<?php echo Yii::app()->createUrl('manager/cabinet/waveDownload',array('id'=>'#ID#'))?>" />
<input type="hidden" id="waveGetUrlTemplate" value="<?php echo Yii::app()->createUrl('manager/cabinet/waveGet',array('key'=>$wave->text_ident,'fromId'=>'#ID#'))?>" />
