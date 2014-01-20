<?php
/**
 * Модель обеспечивающая управление комментариями
 * @property integer $id
 * @property string $text_ident
 * @property integer $author_id
 * @property string $date_edit
 * @property string $date_add
 *
 * @property WavePosts[] $posts
 * @property WaveAttachments[] $attachments
 */
class Waves extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'waves';
	}

	public function relations() {
		return array(
			'posts'=>array(
				self::HAS_MANY,'WavePosts','wave_id'
			), 'attachments'=>array(
				self::HAS_MANY,'WaveAttachments','wave_id','order'=>'attachments.date_add asc'
			), 'author'=>array(
				self::BELONGS_TO,'People','author_id'
			), 'messages'=>array(
				self::HAS_MANY,'WavePosts','wave_id','order'=>'messages.date_add asc'
			)
		);
	}
	
	public function postsFrom($id,$user_id=0) {
		if($user_id>0) {
			$this->ping($user_id);
		}
		$obCriteria=new CDbCriteria();
		$obCriteria->addCondition('wave_id='.$this->id);
		$obCriteria->addCondition('id>'.$id);
		return WavePosts::model()->findAll($obCriteria);
	}
	
	/**
	 * Метод выполняет добавление сообщения к этому обсуждению
	 */
	public function addPost($user_id,$content) {
		$this->date_edit=date('Y-m-d H:i:s');
		if($this->isNewRecord)
			$this->date_add=date('Y-m-d H:i:s');
		$this->save();
		$obPost=new WavePosts();
		$obPost->wave_id=$this->id;
		$obPost->date_add=date('Y-m-d H:i:s');
		$obPost->author_id=$user_id;
		$obPost->save();
		$obPost->addContent($user_id,$content);
		return $obPost->id;
	}
	
	/**
	 * Метод обновляет информацию о опросе ленты
	 */
	public function ping($user_id) {
		$obModel=WavePing::model();
		$obPing=$obModel->findByPk(array('wave_id'=>$this->id,'author_id'=>$user_id));
		if(!$obPing) {
			$obPing=new WavePing();
			$obPing->wave_id=$this->id;
			$obPing->author_id=$user_id;
		}
		$obPing->date=date('Y-m-d H:i:s');
		$obPing->save();
	}
	
	public function isUserOnline($user_id) {
		$obPing=WavePing::model()->findByAttributes(array('wave_id'=>$this->id,'author_id'=>$user_id));
		if($obPing && (strtotime($obPing->date)+300>time())) {
			return true;
		}
		return false;
	}

	/**
	 * Метод ищет и возвращает пост связанный с данным обсуждением
	 * @param integer $id номер сообщения, которое необходимо получить
	 *
	 * @return WavePosts
	 */
	public function getPost($id) {
		if($obPost=WavePosts::model()->findByAttributes(array('wave_id'=>$this->id,'id'=>$id))) {
			$obPost->wave=$this;
		}
		return $obPost;
	}
	
	/**
	 * Метод генерирует массив стандартного вида из объекта пользователя
	 * @param People $obUser
	 */
	public static function getUserArray($obUser) {
		$arResult=array(
			'id'=>$obUser->id,
			'fio'=>$obUser->fio,
			'mail'=>$obUser->mail,
			'is_manager'=>!in_array($obUser->pgroup_id,array(6,7)),
		);
		$arResult=array_merge($arResult,$obUser->getAvatar());
		return $arResult;
	}
}