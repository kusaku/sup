<?php
/**
 * Класс обеспечивает обработку формы редактирования пользователя
 */
class WithdrawForm extends CFormModel {

	public $id;
	public $approved;
	public $rejected;

	public function rules() {
		return array(
			array('id','required'),
			array(
				'id','numerical','min'=>1,'integerOnly'=>true, 'allowEmpty'=>false
			),
			array(
				'approved,rejected','safe'
			),
		);
	}

	public function save() {
		$obWithdraw = Withdraw::model()->findByPk($this->id);
		if(isset($this->approved)){
			$obWithdraw->approve();
		}elseif(isset($this->rejected)){
			$obWithdraw->reject();
		} else {
			Message::setMessage(Message::ERROR, Yii::t('withdraw','Status isn\'t set'));
			return false;
		}
		$arErrors = $obWithdraw->getErrors();
		if(!$arErrors){
			Message::setMessage(Message::SUCCESS, Yii::t('withdraw','Withdraw processed'));
			return true;
		}
		foreach ($arErrors as $error){
			Message::setMessage(Message::ERROR, $error[0]);
		}
		return false;
	}
}