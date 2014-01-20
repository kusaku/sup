<?php
/**
 * @var integer $issue_id - номер задачи в Redmine, взятый из поля в заказе
 * @var integer $serv_id - ID услуги или 0 для главной задачи
 * @var integer $pack_id - ID заказа
 * @var PackageController $this
 */
$sIssueUrl=Yii::app()->params['redmineConfig']['url'].'/issues/'.$issue_id;
if ($issue_id) {
	try {
		$issue = Redmine::readIssue($issue_id, false);
		$issueIsOpen = !in_array($issue['status']['id'], array(5,8));
		echo CHtml::openTag('div',array('id'=>'redmineIssue'));
		echo CHtml::openTag('h3');
		echo CHtml::tag('div',array(),$issue['subject'].CHtml::link('#'.$issue_id,$sIssueUrl,array('target'=>'_blank')));
		echo CHtml::tag('div',array('class'=>'progressBar'),CHtml::tag('div',array('class'=>'progressStat','style'=>'width:'.$issue['done_ratio'].'%'),$issue['done_ratio'].'%'));
		if ($issueIsOpen) {
			//TODO Заменить onClick на нормальное действие
			echo CHtml::link('Закрыть задачу','#',array('class'=>"orangeButton",'onClick'=>'redmineCloseIssue('.$issue_id.','.$pack_id.','.$serv_id.'); $(this).attr("onClick", "")'));
		}
		echo CHtml::closeTag('h3');
		if ($issue['description']) {
			echo CHtml::tag('div',array(),CHtml::tag('b',array(),'описание:').nl2br($issue['description']));
		}
		echo CHtml::tag('div',array(),CHtml::tag('b',array(),'исполнитель:').@$issue['assigned_to']['name']);
		if (count($issue['journals'])> 1) {
			echo CHtml::tag('div',array(),CHtml::tag('b',array(),'сообщения:'));
			foreach ($issue['journals'] as $journal) {
				if (is_array($journal) and ! empty($journal['notes'])) {
					echo CHtml::tag('div',array('class'=>'note'),
					CHtml::tag('div',array(),
							CHtml::tag('b',array(),$journal['user']['name']).'@ '.date('d-m-Y H:i', strtotime($journal['created_on']))
						).CHtml::tag('div',array(),nl2br($journal['notes'])));
				}
			}
		}
		echo CHtml::openTag('div');
		echo CHtml::tag('b',array(),'добавить сообщение:');
		echo CHtml::textArea('redmineMessageInput'.$issue_id,'',array('class'=>'redmineMessage','id'=>'redmineMessageInput'.$issue_id));
		//TODO Заменить onClick на нормальное действие
		echo CHtml::tag('div',array(),CHtml::link('Опубликовать','#',array('class'=>"orangeButton",'onClick'=>'redmineSendMessage('.$issue_id.','.$pack_id.','.$serv_id.'); $(this).attr("onClick", "")')));
		echo CHtml::closeTag('div').CHtml::closeTag('div');
	} catch(CHttpException $e) {
		echo CHtml::openTag('div',array('id'=>'redmineIssue'));
		echo CHtml::tag('h3',array(),'Не удалось открыть задачу '.CHtml::link('#'.$issue_id,$sIssueUrl,array('target'=>'_blank')));
		echo CHtml::tag('h3',array(),'Есть ли у Вас доступ в Redmine? Существует ли такая задача?');
		echo CHtml::tag('div',array(),CHtml::tag('pre',array(),$e->getMessage()));
		echo CHtml::openTag('div');
		echo CHtml::tag('div',array(),'Связать с задачей');
		echo CHtml::label('Номер задачи:','input'.$serv_id);
		echo CHtml::textField('input'.$serv_id,'',array('id'=>'input'.$serv_id,'size'=>6,'maxlength'=>6));
		//TODO Заменить onClick на нормальное действие
		echo CHtml::link('Связать','#',array('onClick'=>'bindRedmineIssue('.$pack_id.','.$serv_id.'); $(this).attr("onClick", "");', 'class'=>"orangeButton"));
		echo CHtml::closeTag('div').CHtml::closeTag('div');
	}
} else {
	echo CHtml::openTag('div',array('id'=>'redmineIssue'));
	echo CHtml::tag('h3',array(),'В редмайне пока пусто');
	echo CHtml::openTag('div');
	echo CHtml::tag('div',array(),'Требуется выбрать мастера и отдать задачу в работу.');
	echo CHtml::label('Выберите мастера:','people_idmaster');
	if($serv_id>0) {
		$this->renderPartial('/snippets/userselect', array('group_id'=>5,'selected'=>Serv2pack::getByIds($serv_id, $pack_id)->master_id,'index'=>'master'));
	} else {
		$this->renderPartial('/snippets/userselect', array('group_id'=>4,'selected'=>Yii::app()->user->id,'index'=>'master'));
	}
	echo CHtml::link('Отдать в работу','#',array('class'=>'orangeButton','onClick'=>'newRedmineIssue('.$pack_id.','.$serv_id.'); $(this).attr("onClick", "");'));
	echo CHtml::closeTag('div');
	echo CHtml::openTag('div');
	echo CHtml::tag('div',array(),'Связать с задачей');
	echo CHtml::label('Номер задачи:','input'.$serv_id);
	echo CHtml::textField('input'.$serv_id,'',array('id'=>'input'.$serv_id,'size'=>6,'maxlength'=>6));
	//TODO Заменить onClick на нормальное действие
	echo CHtml::link('Связать','#',array('onClick'=>'bindRedmineIssue('.$pack_id.','.$serv_id.'); $(this).attr("onClick", "");', 'class'=>"orangeButton"));
	echo CHtml::closeTag('div').CHtml::closeTag('div');
}
