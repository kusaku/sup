<?php
/**
 * Класс обеспечивает обработку формы генерации пароля пользователя
 */
class PeoplePasswordForm extends CFormModel {
	public $id;
	public $notice_email;

	public function rules() {
		return array(
			array(
				'id,notice_email','safe'
			)
		);
	}

	public function attributeNames() {
		return array(
			'id',
			'notice_email'
		);
	}

	public function attributeLabels() {
		return array(
			'id'=>'ID пользователя',
			'notice_email'=>'Уведомить пользователя о смене пароля'
		);
	}

	/**
	 * @throws CException
	 * @return bool
	 */
	public function save() {
		/**
		 * @var People $obPeople
		 */
		$sOpenPassword='';
		if($this->id>0) {
			$obPeople=People::model()->findByPk($this->id);
			if(!$obPeople)
				throw new CHttpException(404,'User not found');
		} else {
			throw new CHttpException(404,'User id not set');
		}
		$sOpenPassword = People::genPassword();
		$obPeople->psw=People::hashPassword($sOpenPassword);

		if ($obPeople->save()) {
			//Отправляем письмо и сохраняем его в лог
			if($this->notice_email==1) {
				try {
					/**
					 * @var FSDocumentsAPI $obDocumentsAPI
					 */
					$obDocumentsAPI=Yii::app()->getComponent('documents');
					$obDocumentsAPI->createEmailSendPassword($obPeople, $sOpenPassword)->send($obPeople->mail, $obPeople->fio);
				}
				catch(exception $e) {}
			}
			return true;
		}
		return false;
	}
}