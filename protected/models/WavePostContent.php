<?php
/**
 * Модель обеспечивающая управление текстом сообщения комментариев
 * @property $id integer
 * @property $content string
 * @property $post_id integer
 * @property $author_id integer
 * @property $date_add string
 * @property $version integer
 * @property $active integer
 */
class WavePostContent extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'wave_post_content';
	}

	public function relations() {
		return array(
			'post'=>array(
				self::BELONGS_TO,'WavePosts','post_id'
			), 'attachments'=>array(
				self::MANY_MANY,'WaveAttachments','wave_attachment_post_content(post_content_id,attachment_id)'
			), 'author'=>array(
				self::BELONGS_TO,'People','author_id'
			), 
		);
	}
}