<?php
/**
 * Класс обеспечивает обработку формы редактирования пользователя
 */
class MessagesTemplateForm extends CFormModel {
	public $id;
	public $content;
	public $people_id;

	public function rules() {
		return array(
			array(
				'id','safe'
			),
			array(
				'id,content,people_id','safe','on'=>'safe'
			),
			array(
				'content','required','on'=>'form'
			),
		);
	}

	public function attributeNames() {
		return array('id','content','people_id');
	}

	public function attributeLabels() {
		return array(
			'id'=>'ID',
			'content'=>'Сообщение',
			'people_id'=>'Автор',
		);
	}

	/**
	 * @throws CException
	 * @return bool
	 */
	public function save() {
		/**
		 * @var WaveMessageTemplates $obMessage
		 */
		if($this->id>0) {
			$obMessage=WaveMessageTemplates::model()->findByPk($this->id);
			if(is_null($obMessage))
				throw new CException('Message not found');
		} else {
			$obMessage=new WaveMessageTemplates();
			$obMessage->common=0;
		}
		$obMessage->content=$this->content;
		$obMessage->people_id=Yii::app()->user->id;
		return $obMessage->save();
	}
}