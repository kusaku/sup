<?php
/**
 * Модель обеспечивающая управление шаблонами сообщений комментариев
 * @property integer $id
 * @property string $content
 * @property integer $common
 * @property integer $people_id
 */
class WaveMessageTemplates extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'wave_messages_templates';
	}

	public function rules() {
		return array(
			array('content','safe'),
			array('content','required')
		);
	}

	public function relations() {
		return array(
			'owner_id'=>array(
				self::HAS_ONE,'WavePeopleMessageTemplate','message_id'
			)
		);
	}

	public function scopes() {
		return array(
			'onlyMy'=>array(
				'condition'=>'`common`=0 AND `people_id`='.Yii::app()->user->id
			)
		);
	}

	public function attributeLabels() {
		return array(
			'id'=>'ID',
			'content'=>'Сообщение'
		);
	}
}