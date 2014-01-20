<?php
/**
 * @var $this DomainRequestController
 * @var $package Package
 * @var $client People
 * @var $model DomainRequestForm
 */
echo CHtml::openTag('div',array('style'=>'width:600px;'));
	echo CHtml::openTag('div',array('class'=>'formWindow','style'=>'margin-bottom:-12px;'));
		if($model->id>0) {
			$sTitle='Редактирование заявки #'.$model->id.' на домен '.$model->domain;
			if(isset($package)) {
				$sTitle.=' к заказу #'.$package->id;
			} elseif(isset($client)) {
				$sTitle.=' от клиента '.$client->mail;
			}
		} else {
			$sTitle='Создание новой заявки на домен';
			if(isset($package)) {
				$sTitle.=' к заказу #'.$package->id;
			} elseif(isset($client)) {
				$sTitle.=' для клиента '.$client->mail;
			}
		}
		echo CHtml::tag('div',array('class'=>'formHead'),$sTitle);
		echo CHtml::openTag('div',array('class'=>'formBody','style'=>'padding:10px;'));
			/**
			 * @var $form CActiveForm
			 */
			$form=$this->beginWidget('CActiveForm',array('id'=>'dr-form','action'=>Yii::app()->createUrl('manager/domainRequest/edit',array('requestId'=>$model->id))));
			echo $form->hiddenField($model,'id',array('name'=>'requestId'));
			if($model->id==0) {
				echo $form->hiddenField($model,'client_id');
				echo $form->hiddenField($model,'package_id');
			}
			$arRA=array('class'=>'formRow');
			$arStatuses=DomainRequest::GetStatuses();
			$sPhoneHint='Формат телефона:\n +(код страны) (код города) (номер телефона).\nМожно указать несколько номеров через ",")';
			echo CHtml::openTag('div',array('class'=>'formRows'));
				echo CHtml::openTag('div',$arRA).$form->label($model,'domain').$form->textField($model,'domain').CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'zone').$form->textField($model,'zone').CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'status').CHtml::tag('b',array(),$arStatuses[$model->status]).CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'email').$form->textField($model,'email').CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'phone',array('title'=>$sPhoneHint)).$form->textField($model,'phone').CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'fax',array('title'=>$sPhoneHint)).$form->textField($model,'fax').CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'mode').$form->dropDownList($model,'mode',DomainRequest::GetModes()).CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'mobile',array('title'=>$sPhoneHint)).$form->textField($model,'mobile').CHtml::closeTag('div');
				echo CHtml::openTag('div',array('class'=>'companyData panel'.($model->mode!='company'?' hidden':'')));
					echo CHtml::openTag('div',$arRA).$form->label($model,'company_ru').$form->textField($model,'company_ru').CHtml::closeTag('div');
					echo CHtml::openTag('div',$arRA).$form->label($model,'company').$form->textField($model,'company').CHtml::closeTag('div');
					echo CHtml::openTag('div',$arRA).$form->label($model,'inn').$form->textField($model,'inn').CHtml::closeTag('div');
					echo CHtml::openTag('div',$arRA).$form->label($model,'kpp').$form->textField($model,'kpp').CHtml::closeTag('div');
				echo CHtml::closeTag('div');
				echo CHtml::openTag('div',array('class'=>'personData panel'.($model->mode!='person'?' hidden':'')));
					echo CHtml::openTag('div',$arRA).$form->label($model,'firstname_ru').$form->textField($model,'firstname_ru').CHtml::closeTag('div');
					echo CHtml::openTag('div',$arRA).$form->label($model,'middlename_ru').$form->textField($model,'middlename_ru').CHtml::closeTag('div');
					echo CHtml::openTag('div',$arRA).$form->label($model,'lastname_ru').$form->textField($model,'lastname_ru').CHtml::closeTag('div');
					echo CHtml::openTag('div',$arRA).$form->label($model,'firstname').$form->textField($model,'firstname').CHtml::closeTag('div');
					echo CHtml::openTag('div',$arRA).$form->label($model,'middlename').$form->textField($model,'middlename').CHtml::closeTag('div');
					echo CHtml::openTag('div',$arRA).$form->label($model,'lastname').$form->textField($model,'lastname').CHtml::closeTag('div');
					echo CHtml::openTag('div',$arRA).$form->label($model,'birthdate').$form->textField($model,'birthdate').CHtml::closeTag('div');
					echo CHtml::openTag('div',$arRA).$form->label($model,'passport_series').$form->textField($model,'passport_series').CHtml::closeTag('div');
					echo CHtml::openTag('div',$arRA).$form->label($model,'passport_org').$form->textField($model,'passport_org').CHtml::closeTag('div');
					echo CHtml::openTag('div',$arRA).$form->label($model,'passport_date').$form->textField($model,'passport_date').CHtml::closeTag('div');
				echo CHtml::closeTag('div');
				echo CHtml::tag('p',array('style'=>'clear:both;'),'Данные владельца домена');
				echo CHtml::openTag('div',$arRA).$form->label($model,'la_country').$form->dropDownList($model,'la_country',People::getCountriesList()).CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'la_state').$form->textField($model,'la_state').CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'la_postcode').$form->textField($model,'la_postcode').CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'la_city').$form->textField($model,'la_city').CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'la_address').$form->textField($model,'la_address').CHtml::closeTag('div');
				echo CHtml::tag('p',array('style'=>'clear:both;'),'Данные администратора домена');
				echo CHtml::openTag('div',$arRA).$form->label($model,'pa_state').$form->textField($model,'pa_state').CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'pa_postcode').$form->textField($model,'pa_postcode').CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'pa_city').$form->textField($model,'pa_city').CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'pa_address').$form->textField($model,'pa_address').CHtml::closeTag('div');
				echo CHtml::openTag('div',$arRA).$form->label($model,'pa_addressee').$form->textField($model,'pa_addressee').CHtml::closeTag('div');
			echo CHtml::closeTag('div');
			echo CHtml::openTag('div',array('class'=>'buttons','style'=>'padding:10px 0;'));
				if($client->bm_user_data) {
					//echo CHtml::tag('a',array('class'=>'billManagerViewUser','href'=>'#people_bmview_'.$client->id),'Аккаунт в BM');
				}
				if($model->status=='new') {
					/*
					if($client->bm_user_data) {
						echo CHtml::tag('a',array('class'=>'billManagerRequestDomain','href'=>'#!'),'Подать заявку');
					} else {
						echo CHtml::tag('a',array('class'=>"billManagerRegister",'href'=>'#!'),'Зарегистрировать и подать заявку');
					}*/
					echo CHtml::tag('a',array('class'=>'billManagerRequestSave buttonOrange','href'=>'#!'),'Сохранить');
					echo CHtml::tag('a',array('class'=>'billManagerRequestSaveClose buttonOrange','href'=>'#!'),'Сохранить и закрыть');
					echo CHtml::tag('a',array('class'=>'billManagerRequestCancel buttonGray','href'=>'#!'),'Закрыть');
				}
				if($model->status=='submited') {

				}
				echo CHtml::tag('span',array('class'=>'save-result','style'=>'display:none;'),'Запись успешно сохранена');
			echo CHtml::closeTag('div');
			echo CHtml::tag('div',array('style'=>"overflow:hidden;visibility: hidden;height: 1px;"),CHtml::submitButton('сохранить'));
			$this->endWidget();
		echo CHtml::closeTag('div');
	echo CHtml::closeTag('div');
echo CHtml::closeTag('div');