<?php
/**
 * Модель обеспечивающая связь между приложенным файлом и версией сообщения
 */

class WaveAttachmentPostContent extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'wave_attachment_post_content';
	}

	public function relations() {
		return array(
			'post'=>array(
				self::BELONGS_TO,'WavePosts','post_content_id'
			), 'attachment'=>array(
				self::BELONGS_TO,'WaveAttachments','attachment_id'
			), 
		);
	}
}