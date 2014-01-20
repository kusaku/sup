<?php
/**
 * @var WaveMessageTemplates[] $models
 * @var WaveMessagesController $this
 */
?>
<a href="<?php echo $this->createUrl('waveMessages/edit',array('id'=>0));?>">Создать сообщение</a>
<table width="100%" class="services">
	<col width="100"/>
	<col width="100%"/>
	<thead>
	<tr>
		<th>ID</th>
		<th>Сообщение</th>
		<th></th>
	</tr>
	</thead>
	<tbody>
		<?php foreach($models as $obMessage):?>
			<tr>
				<td><a href="<?php echo $this->createUrl('waveMessages/edit',array('id'=>$obMessage->id));?>"><?php echo $obMessage->id?></a></td>
				<td><?php echo LangUtils::truncate(strip_tags($obMessage->content),300)?></td>
				<td class="leaf-buttons"><?php echo CHtml::tag('a',array('class'=>'delete','href'=>$this->createUrl('waveMessages/delete',array('id'=>$obMessage->id))),'');?></td>
			</tr>
		<?php endforeach?>
	</tbody>
</table>
