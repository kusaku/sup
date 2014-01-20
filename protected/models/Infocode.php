<?php

/**
 * @property integer $id ID
 * @property string $type infocode type (other | partner)
 * @property string $value infocode value
 * @property integer $created creation timestamp
 * @property string $descr Description
 */
class Infocode extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return 'infocode';
	}

	const TYPE_PARTNER = 'partner';
	const TYPE_OTHER = 'other';

	public function rules() {
		return array(
			array('descr', 'safe', 'on'=>'edit'),
			array('value,type,descr', 'safe', 'on' => 'add'),
			array('value,type,descr', 'required', 'on' => 'add'),
			array('descr', 'required', 'on' => 'edit'),
			array('value', 'filter', 'filter'=>'trim', 'on' => 'add'),
			array('value', 'unique', 'caseSensitive'=>false, 'on' => 'add'),
			array('*', 'unsafe', 'on'=>'edit,add'),
			array('*', 'safe'),
			array('id,type,value,created,descr', 'safe', 'on'=>'search')
		);
	}


	public function relations() {
		return array(
			'people'=>array(
				self::HAS_ONE,
				'People',
				'infocode_id',
			),
			'partner'=>array(
				self::HAS_ONE,
				'Partner',
				'infocode_id',
			),
			'packages'=>array(
				self::HAS_MANY,
				'Package',
				'infocode_id',
			),
		);
	}

	public function attributeLabels() {
		return array(
			'value'=>Yii::t('infocode','value'),
			'type'=>Yii::t('infocode','type'),
			'descr'=>Yii::t('infocode','descr'),
			'created'=>Yii::t('infocode','created'),
		);
	}

	protected function beforeSave() {
		if($this->isNewRecord){
			$this->created = time();
		}
		return parent::beforeSave();
	}

	private static $_arTypes = array();

	public function getTypes() {
		return self::$_arTypes ? self::$_arTypes : self::$_arTypes = array(
			self::TYPE_OTHER => Yii::t('infocode', self::TYPE_OTHER),
			self::TYPE_PARTNER => Yii::t('infocode', self::TYPE_PARTNER),
		);
	}

/**
 * Возвращает следующий доступный инфокод среди не занятых, больший или равный минимуму.
 * Во входных данных обрезаются нули идущие перед значащей цифрой.
 * @return integer найденный доступный инфокод
 */
	public static function getNextInfocodeValue(){
		//инициализация
		$iMin = 100;
		$bMin = false;

		$sql = 'SELECT `value` FROM `infocode`';
		$command=Yii::app()->db->createCommand($sql);
		$arInfocodes=$command->setFetchMode(PDO::FETCH_COLUMN)->queryAll();

		//причесываем исходные данные
		natsort($arInfocodes);
		$arInfocodes=array_values($arInfocodes);

		foreach($arInfocodes as $iKey => $arInfocode){
			if($arInfocode >= $iMin){
				//был ли минимальный инфокод?
				if($arInfocode == $iMin){
					$bMin = true;
				}
				//если подряд равные коды, то переходим к следующему
				if(isset($arInfocodes[$iKey+1]) && intval($arInfocodes[$iKey]) == intval($arInfocodes[$iKey+1])){
					continue;
				}
				//Если находим дырку в инфокодах, то возвращаем первое значение из этой дырки
				if(isset($arInfocodes[$iKey+1]) && intval($arInfocodes[$iKey])+1 != intval($arInfocodes[$iKey+1])){
					$iInfocode = intval($arInfocodes[$iKey])+1;
					break;
				}
			}
		}
		//Если все перебраны и дырок нет, то возвращаем последнее значение +1
		$iInfocode = isset($iInfocode) ? $iInfocode : intval($arInfocodes[$iKey])+1;
		//Если все перебрали и так не нашли минимальный, то возвращаем минимальный
		return $bMin ? $iInfocode : $iMin;
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search(){
		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id,true);
		$criteria->compare('type',$this->type,true);
		$criteria->compare('value',$this->value,true);
		$criteria->compare('created',$this->created?'>='.strtotime($this->created):'',true);
		$criteria->compare('created',$this->created?'<'.(strtotime($this->created)+24*3600):'',true);
		$criteria->compare('descr',$this->descr,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'sort'=>array(
				'defaultOrder'=>array(
					'id'=>CSort::SORT_DESC,
				)
			),
			'pagination'=>array(
				'pageSize'=>50,
			),
		));
	}

}
