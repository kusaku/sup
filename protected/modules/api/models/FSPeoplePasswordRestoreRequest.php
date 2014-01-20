<?php
/**
 * Модель обеспечивает хранение записей о запросах паролей пользователями в базе данных
 */
 
class FSPeoplePasswordRestoreRequest extends CActiveRecord {
	const GEN_CHARS='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890!@#$%^&*()';
	
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	/**
	 * Метод возвращает имя таблицы
	 */
	public function tableName() {
		return 'people_password_restore_requests';
	}
	
	/**
	 * Метод генерирует новый код восстановления пароля. В случае, если код уже есть,
	 * выполняет повторную генерацию
	 * @todo Переделать генерацию для исключения ситуации зацикливания
	 */
	public function genCode($userId,$userEmail) {
		$code=substr(sha1(str_shuffle(self::GEN_CHARS).$userId.$userEmail.time().rand(0,1000)),rand(0,20),10);
		if($this->findByAttributes(array('code'=>$code)))
			return $this->genCode($userId,$userEmail);
		return $code;
	}
	
	public function getCodeLink() {
		return str_replace('#CODE#',$this->code,$this->codeUrl);
	}
}
