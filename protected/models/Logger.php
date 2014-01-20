<?php
/**
 * Класс обеспечивает работу журнала
 * @property integer $id
 * @property integer $client_id
 * @property integer $manager_id
 * @property string  $info
 * @property integer $type
 * @property string  $dt
 *
 * @property People  $client
 * @property People  $manager
 */
class Logger extends CActiveRecord {

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'logger';
	}
	
	public function relations() {
		return array(
			'client'=>array(
				self::BELONGS_TO,
				'People',
				'client_id'
			),
			'manager'=>array(
				self::BELONGS_TO,
				'People',
				'manager_id'
			)
		);
	}
	
	public static function put($attributes) {
		$record = self::model();
		$record->setIsNewRecord(true);
		$record->id=null;
		$record->client_id = (int) $attributes['client_id'];
		$record->manager_id = (int) $attributes['manager_id'];
		$record->info = (string) $attributes['info'];
		$record->dt = date('Y-m-d H:i:s');
		if ($record->insert())
			return $record;
		else
			return NULL;
	}

	public function getFormatedText() {
		if(preg_match('#^\[auto\]#',$this->info)) {
			$arReplacements=array(
				'#(\[auto\])#'=>'<span style="color:green;">$1</span>',
				'#([\w\d-\.]+@[\w\d\-\.]+)#'=>' <a href="mailto:$1" style="color:#e06e08;" target="_blank">$1</a> '
			);
			$sText=preg_replace(array_keys($arReplacements),array_values($arReplacements),$this->info);
		} else {
			$sText=nl2br(strip_tags($this->info));
			$arReplacements=array(
				'#(^|\n)(\d\d:\d\d:\d\d)( (\w+):)?#u'=>'$1<b>$2</b> <i>$3</i>',
				'#(\[auto\])#'=>'<span style="color:green;">$1</span>',
				'#(http:\/\/[\w\d-_\./\#]+)#u'=>'<a href="$1" style="color:blue;" target="_blank">$1</a>',
				'#\s(\d+)\s#'=>' <span style="color:#7b0013;font-weight: bold;">$1</span> ',
				'#([\w\d-\.]+@[\w\d\-\.]+)#'=>' <a href="mailto:$1" style="color:#e06e08;" target="_blank">$1</a> '
			);
			$sText=preg_replace(array_keys($arReplacements),array_values($arReplacements),$sText);
		}
		return $sText;
	}
	
	public function scopes() {
		return array(
			'lastfirst'=>array(
				'order'=>'dt DESC'
			)
		);
	}
	
	/**
	 * Метод возвращает список записей журнала по номеру пользователя
	 * или за указанную дату
	 * @return Logger[]
	 */
	public static function get($var) {
		if (is_numeric($var)) {
			$obCriteria=new CDbCriteria(array(
				'order'=>'dt DESC'
			));
			return self::model()->lastfirst()->findAllByAttributes(array(
				'client_id'=>$var
			),$obCriteria);
		}
		if (is_string($var)) {
			return self::model()->lastfirst()->findAllByAttributes(array(
				'dt'=>date('Y-m-d H:i:s', strtotime($var))
			));
		}
	}
}
