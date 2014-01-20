<?php
/**
 * @var PeopleEditForm $model
 * @var PeopleController $this
 */
echo CHtml::openTag('div',array('class'=>'formWindow','style'=>'width:600px;'));
if($model->id>0) {
	$sTitle='Редактирование клиента/пользователя @'.$model->id;
} else {
	$sTitle='Добавление клиента/пользователя';
}
echo CHtml::tag('div',array('class'=>'formHead'),$sTitle);
echo CHtml::openTag('div',array('class'=>'formBody','style'=>'padding:0 0 10px;'));
/**
 * @var FSActiveForm $form
 */
$form=$this->beginWidget('FSActiveForm',array('method'=>'POST','id'=>'people-edit-form'));
echo $form->hiddenField($model,'id');
echo $form->hiddenField($model,'parent_id');
echo CHtml::openTag('div',array('class'=>'scroll-pane tabscontainer modal'));
echo CHtml::openTag('ul');
echo CHtml::tag('li',array(),CHtml::tag('a',array('href'=>'#tabs-client-0'),'Основное'));
foreach (Attributes::model()->with('children')->getGroups() as $group) {
	echo CHtml::tag('li',array(),CHtml::tag('a',array('href'=>'#tabs-client-'.$group->primaryKey),$group->name));
}
echo CHtml::closeTag('ul');
echo CHtml::openTag('div',array('style'=>'max-height:375px;overflow:auto;','id'=>'tabs-client-0'));
echo CHtml::openTag('div',array('class'=>'formRows'));
$arRA=array('class'=>'formRow');
if (Yii::app()->user->checkAccess('admin') or Yii::app()->user->checkAccess('moder')) {
	echo $form->formRowDropDownList($model,'pgroup_id',CHtml::listData(PeopleGroup::model()->findAll(),'id','name'),$arRA);
	echo $form->formRowTextField($model,'login',$arRA);
	echo $form->formRowTextField($model,'psw',$arRA);
	echo $form->formRowTextField($model,'rm_token',$arRA);
} else {
	echo CHtml::tag('div',$arRA,$form->label($model,'pgroup_id').CHtml::tag('span',array('class'=>'value'),PeopleGroup::model()->findByPk($model->pgroup_id)->name));
	echo $form->formRowTextField($model,'login',$arRA,array('disabled'=>true));
	if(Yii::app()->user->id == $model->id) {
		echo $form->formRowTextField($model,'psw',$arRA);
		echo $form->formRowTextField($model,'rm_token',$arRA);
	}
}
echo $form->formRowTextField($model,'fio',$arRA);
echo $form->formRowTextField($model,'mail',$arRA);
echo $form->formRowTextField($model,'phone',$arRA);
echo $form->formRowTextField($model,'firm',$arRA);
echo $form->formRowTextField($model,'state',$arRA);
if($model->id>0) {
	echo CHtml::tag('div',array('class'=>'formRow'),CHtml::label(CHtml::link('Контакты','#peopleContact_'.$model->id),'').CHtml::link('Посмотреть журнал','#loggerForm_'.$model->id));
	//echo CHtml::tag('div',array('class'=>'formRow'),CHtml::label('&nbsp;','').);
} else {
	if(Yii::app()->user->id==132601 || Yii::app()->user->checkAccess('admin')) {
		echo $form->formRowCheckbox($model,'notice_email',$arRA,array('uncheckValue'=>NULL));
	} else {
		echo $form->hiddenField($model,'notice_email');
	}
}
echo $form->formRowTextarea($model,'descr',array('class'=>'formRow fullRow'));
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');
foreach (Attributes::model()->with('children')->getGroups() as $group) {
	echo CHtml::openTag('div',array('style'=>'max-height:375px;overflow:auto;','id'=>'tabs-client-'.$group->primaryKey));
	echo CHtml::openTag('div',array('class'=>'formRows'));
	if($group->primaryKey == 4000 && $model->id>0) {
		echo CHtml::tag('div',array('class'=>'formRow fullRow'),CHtml::tag('a',array('href'=>'#jurPersonCard_'.$model->id),'Редактировать реквизиты юридического лица'));
	}
	foreach ($group->children as $attr) {
		$sData=$attr->set;
		echo CHtml::openTag('div',$arRA);
		echo CHtml::tag('label',array(),$attr->name);
		if($arData=unserialize($sData)) {
			echo CHtml::dropDownList('attr['.$attr->type.']',$model->arAttributes[$attr->type],CHtml::listData($arData,'value','name'));
		} else {
			echo CHtml::textField('attr['.$attr->type.']',$model->arAttributes[$attr->type]);
		}
		echo CHtml::closeTag('div');
	}
	if ($group->primaryKey == 2000) {
		echo $form->formRowDivider();
		if($obPeople=$model->getPeople()) {
			if(!is_null($obPeople->bm_user_data)) {
				print_r($obPeople->bm_user_data);
			} elseif(!empty($model->arAttributes['bm_id'])) {
				echo "oldvalue";
				/*<label>
				<em>Открыть BILLManager</em>
				<span><a class="add_open" id="linkid-<?= $people->primaryKey; ?>" onclick="saveAndProceed('#sup_popup form', function(data){if (data.success) bmOpen(<?= $people->primaryKey; ?>); else $('#linkid-<?= $people->primaryKey; ?>').tipBox('Ошибка сохранения!').tipBox('show');});" href="#"></a></span>
				</label>
				<label>
					<li>Подгрузить данные из BILLManager</li>
					<span><a class="add_bm" id="linkid-<?= $people->primaryKey; ?>" onclick="saveAndProceed('#sup_popup form', function(data){if (data.success) bmUpdateAttributes(<?= $people->primaryKey; ?>); else $('#linkid-<?= $people->primaryKey; ?>').tipBox('Ошибка сохранения!').tipBox('show');});" href="#"></a></span>
				</label>
				<?php else : ?>
				<?php if ($people->primaryKey): ?>
					<label>
						<em>Регистрация в BILLManager</em>
						<span><a class="add_bm" id="linkid-<?= $people->primaryKey; ?>" onclick="saveAndProceed('#sup_popup form', function(data){if (data.success) bmRegister(<?= $people->primaryKey; ?>); else $('#linkid-<?= $people->primaryKey; ?>').tipBox('Ошибка сохранения!').tipBox('show');});" href="#"></a></span>
					</label>
					<?php endif; ?>
				<?php endif; ?>*/
			} else {
				echo $form->formRowLink('Указать связь с BillManager','#billManager',$arRA,array('class'=>'linkToBM'));
			}
		}
	}
	echo CHtml::closeTag('div');
	echo CHtml::closeTag('div');
}
echo CHtml::closeTag('div');
echo CHtml::openTag('div',array('class'=>'buttons'));
echo CHtml::tag('a',array('class'=>'plus','title'=>'Сохранить клиента и добавить ему заказ'),'');
echo CHtml::tag('a',array('class'=>'buttonSave buttonOrange','title'=>'Сохранить клиента'),'Сохранить');
echo CHtml::tag('a',array('class'=>'buttonSaveClose buttonOrange','title'=>'Сохранить клиента и закрыть окно'),'Сохранить и закрыть');
echo CHtml::tag('a',array('class'=>'buttonCancel buttonGray','title'=>'Закрыть окно редактирования'),'Закрыть');
echo CHtml::tag('div',array('style'=>"overflow:hidden;visibility: hidden;height: 1px;"),CHtml::submitButton('сохранить'));
if(isset($save) && $save=='ok') {
	echo CHtml::tag('span',array('class'=>'save-result'),'Запись успешно сохранена');
}
$this->endWidget();
echo CHtml::closeTag('div');
echo CHtml::closeTag('div');
