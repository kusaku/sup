<?php
/**
 * Модель обеспечивающая связь между пользователями и документами
 */

class DocumentWaveAttachment extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'document_wave_attachment';
	}

	public function relations() {
		return array(
			'documents'=>array(
				self::HAS_MANY,'Documents','document_id'
			), 'attachment'=>array(
				self::HAS_MANY,'WaveAttachments','wave_attachment_id'
			)
		);
	}
}