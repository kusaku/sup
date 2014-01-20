<?php
/**
 * Модель обеспечивающая управление приложениями к комментариям
 * @property Waves $wave
 * @property People $author
 * @property integer $wave_id
 * @property integer $author_id
 * @property string $date
 */
class WavePing extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'wave_ping';
	}

	public function relations() {
		return array(
			'wave'=>array(
				self::BELONGS_TO,'Waves','wave_id'
			), 'author'=>array(
				self::BELONGS_TO,'People','author_id'
			)
		);
	}
}