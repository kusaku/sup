<?php
/**
 * Класс реализует ActiveRecord для записей таблицы JurPersonReference
 * @property numeric $internal - флаг указывающий, что запись является внутренней записью компании
 */
class JurPersonReference extends CActiveRecord {
	protected $arMyFields2Attributes=array(
		'title'=>'name',
		'inn'=>'vatnum',
		'kpp'=>'kpp',
		//'address'=>,
		//'real_address'=>,
		'settlement_account'=>'rs',
		'correspondent_account'=>'ks',
		'bank_title'=>'bankname',
		'bank_bik'=>'bik',
		'director_fio'=>'director',
		'director_position'=>'jobtitle',
		'director_source'=>'baseaction',
		'director_source_info'=>'baseaction'
	);

	public static function model($className = __CLASS__) {
		return parent::model($className);
	}

	/**
	 * Метод выполняет инициализацию объекта класса. Используется для того, чтобы установить режим класса по умолчанию
	 * в поддержку ООО а не ИП
	 */
	public function init() {
		$this->setScenario('ltd');
	}

	public function tableName() {
		return 'jur_person_reference';
	}

	public function relations() {
		return array(
			'packages'=>array(
				self::HAS_MANY,
				'Package',
				'package_jur_person_id'
			),
			'people'=>array(
				self::HAS_MANY,
				'People',
				'jur_person_id'
			)
		);
	}

	public function rules() {
		return array(
			array(
				'title,type,inn,address,real_address,settlement_account,correspondent_account,bank_title,bank_bik,director_fio','required','on'=>'ltd,ip'
			),
			array(
				'title,type,inn,real_address','safe','on'=>'fiz',
			),
			array(
				'kpp,director_position,director_source','required','on'=>'ltd'
			),
			/*array(
				'egrip','required','on'=>'ip'
			),*/
			array(
				'type','match','allowEmpty'=>false,'pattern'=>'/^ltd|ip|fiz$/'
			),
			array(
				'director_source','match','allowEmpty'=>false,'pattern'=>'/^charter|warrant|order|protocol$/','on'=>'ltd'
			),
			array(
				'inn','match','pattern'=>'/^\d{10}(\d{2})?$/'
			),
			array(
				'settlement_account,correspondent_account','match','on'=>'ltd,ip','allowEmpty'=>false,'pattern'=>'/^\d{20}?$/'
			),
			array(
				'bank_bik','match','on'=>'ltd,ip','allowEmpty'=>false,'pattern'=>'/^\d{9}?$/'
			),
			array(
				'kpp','match','allowEmpty'=>false,'pattern'=>'/^\d{9}?$/','on'=>'ltd'
			),
			array(
				'director_source_info','validatorSourceInfo','on'=>'ltd'
			),
			array(
				'egrip','match','allowEmpty'=>true,'pattern'=>'/^\d{15}$/','on'=>'ip'
			),
		);
	}

	public function attributeLabels() {
		return array(
			'title'=>Yii::t('rekvizform','Company title'),
			'type'=>Yii::t('rekvizform','Type'),
			'inn'=>Yii::t('rekvizform','INN'),
			'kpp'=>Yii::t('rekvizform','KPP'),
			'egrip'=>Yii::t('rekvizform','EGRIP'),
			'address'=>Yii::t('rekvizform','Address'),
			'real_address'=>Yii::t('rekvizform','Real address'),
			'settlement_account'=>Yii::t('rekvizform','Settlement account'),
			'correspondent_account'=>Yii::t('rekvizform','Correspondent account'),
			'bank_title'=>Yii::t('rekvizform','Bank title'),
			'bank_bik'=>Yii::t('rekvizform','BIK'),
			'director_fio'=>Yii::t('rekvizform','Name of director'),
			'director_position'=>Yii::t('rekvizform','Director position'),
			'director_source'=>Yii::t('rekvizform','Director rights source'),
			'director_source_info'=>Yii::t('rekvizform','Director rights source info'),
		);
	}

	/**
	 * Валидатор для поля director_source_info, проверяет наличие текста в поле, если director_source!=charter
	 */
	public function validatorSourceInfo($attribute,$params) {
		if($this->director_source!='' && $this->director_source!='charter') {
			if($this->director_source_info=='') {
				$this->addError($attribute, Yii::t('rekvizform','You should fill {attribute} field',array('{attribute}'=>$this->getAttributeLabel($attribute))));
			}
		}
	}

	/**
	 * Метод выполняет запись соответствующих значений в таблицу аттрибутов пользователей
	 */
	public function _saveAttributes($attributes=NULL) {
		$arPeople=$this->people;
		if(is_array($arPeople) && count($arPeople)>0) {
			if($attributes!=NULL) {
				$attributes=array_intersect($attributes, array_keys($this->arMyFields2Attributes));
				if(count($attributes)==0)
					return;
			} else {
				$attributes=array_keys($this->arMyFields2Attributes);
			}
			$arOuter2Inner=array_flip($this->arMyFields2Attributes);
			$arFind=array_intersect($arOuter2Inner, $attributes);
			$arAttributes=Attributes::model()->findAllByAttributes(array('type'=>array_flip($arFind)));
			foreach($arPeople as $obMan) {
				foreach ($arAttributes as $obAttribute) {
					$attr = isset($obMan->values[$obAttribute->id]) ? $obMan->values[$obAttribute->id] : new PeopleAttr();
					$attr->attribute_id = $obAttribute->id;
					$attr->people_id = $obMan->primaryKey;
					$sAttrName=$arOuter2Inner[$obAttribute->type];
					if ( empty($attr->attr->regexp) || preg_match($attr->attr->regexp, $this->$sAttrName)) {
						$attr->value = $this->$sAttrName;
					} else {
						$attr->value = $attr->attr->defval;
					}
					// сохраняем только существующие и непустые
					if (!$attr->isNewRecord or !empty($attr->value)) {
						$attr->save();
					}
				}
			}
		}
	}

	/**
	 * Метод возвращает активные поля модели (в зависимости от режима)
	 */
	public function getActiveAttributeNames() {
		if($this->type=='ip') {
			return array('type','title','inn','egrip','address','real_address','settlement_account','correspondent_account',
			'bank_title','bank_bik','director_fio');
		}
		return array('type','title','inn','kpp','address','real_address','settlement_account','correspondent_account',
			'bank_title','bank_bik','director_fio','director_position','director_source','director_source_info');
	}

	/**
	 * Перекрываем родительскую функцию создания новой записи
	 */
	public function insert($attributes=NULL) {
		if(parent::insert($attributes)) {
			if(!$this->internal==0)
				$this->_saveAttributes($attributes);
			return true;
		}
		return false;
	}

	/**
	 * Перекрываем родительскую функцию обновления записи
	 */
	public function update($attributes=NULL) {
		if(parent::update($attributes)) {
			if(!$this->internal==0)
				$this->_saveAttributes($attributes);
			return true;
		}
		return false;
	}

	/**
	 * Метод возвращает реквизиты юридического лица ФС-Групп
	 */
	static public function fsgroupData() {
		return array(
			'title'=>'ООО «ФС груп»',
			'type'=>'ltd',
			'director_position'=>'директор',
			'director_fio'=>'Захарьев Д.Л.',
			'director_source'=>'charter',
			'director_source_info'=>'',
			'inn'=>'7813527798',
			'kpp'=>'781301001',
			'address'=>'194044, г. Санкт-Петербург, ул. Смолячкова, д. 4/2',
			'settlement_account'=>'40702810990650000023',
			'bank_title'=>'ДО "Петровский" ОАО "Банк Санкт-Петербург"',
			'correspondent_account'=>'30101810900000000790',
			'bank_bik'=>'044030790',
			'stamp_url'=>'/images/stamp.png',
			'sign_url'=>'/images/signature.png',
		);
	}

	/**
	 * Метод конвертирует данные из старого формата клиента в новый
	 * @param People $obClient
	 * @return array
	 */
	static public function convertPeopleAttributes($obClient) {
		$arResult=array('title'=>$obClient->firm,
			'type'=>'ltd',
			'director_position'=>!empty($obClient->attr['jobtitle'])?$obClient->attr['jobtitle']->values[0]->value:'директор',
			'director_source'=>'text',
			'director_source_info'=>!empty($obClient->attr['baseaction'])?$obClient->attr['baseaction']->values[0]->value:'устава',
			'inn'=>!empty($obClient->attr['vatnum'])?$obClient->attr['vatnum']->values[0]->value:'',
			'kpp'=>!empty($obClient->attr['kpp'])?$obClient->attr['kpp']->values[0]->value:'',
			'address'=>join(' ',
				array_filter(
					array(
						!empty($obClient->attr['la_country'])?People::getCountryById($obClient->attr['la_country']->values[0]->value):'',
						!empty($obClient->attr['la_state'])?$obClient->attr['la_state']->values[0]->value:'',
						!empty($obClient->attr['la_postcode'])?$obClient->attr['la_postcode']->values[0]->value:'',
						!empty($obClient->attr['la_city'])?$obClient->attr['la_city']->values[0]->value:'',
						!empty($obClient->attr['la_address'])?$obClient->attr['la_address']->values[0]->value:''
					),
					array('JurPersonReference','isEmpty')
				)
			),
			'settlement_account'=>!empty($obClient->attr['rs'])?$obClient->attr['rs']->values[0]->value:'',
			'bank_title'=>!empty($obClient->attr['bankname'])?$obClient->attr['bankname']->values[0]->value:'',
			'correspondent_account'=>!empty($obClient->attr['ks'])?$obClient->attr['ks']->values[0]->value:'',
			'bank_bik'=>!empty($obClient->attr['bik'])?$obClient->attr['bik']->values[0]->value:'',
			'stamp_url'=>'',
			'sign_url'=>'',
		);
		if(isset($obClient->attr['director']) && !empty($obClient->attr['director'])) {
			$arResult['director_fio']=$obClient->attr['director']->values[0]->value;
		} elseif(isset($obClient->attr['person']) && !empty($obClient->attr['person'])) {
			$arResult['director_fio']=$obClient->attr['person']->values[0]->value;
		} else {
			$arResult['director_fio']=$obClient->fio;
		}
		return $arResult;
	}

	static function isEmpty($val) {
		if($val=='') return false;
		return true;
	}

	/**
	 * Метод возвращает список значений доступны для поля director_source
	 */
	public static function getSourceList() {
		return array(
			'charter'=>Yii::t('jurPersonReference','Charter'),
			'warrant'=>Yii::t('jurPersonReference','Warrant'),
			'order'=>Yii::t('jurPersonReference','Order'),
			'protocol'=>Yii::t('jurPersonReference','Protocol'),
		);
	}

	public static function getTypeList() {
		return array(
			'ltd'=>Yii::t('jurPersonReference','LTD'),
			'ip'=>Yii::t('jurPersonReference','IP')
		);
	}
}
