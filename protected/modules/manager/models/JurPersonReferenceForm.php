<?php
/**
 * Класс выполняет обеспечение функционирования формы редактирования профиля пользователя
 */
class JurPersonReferenceForm extends CFormModel {
	public $id;
	public $title;
	public $type;
	public $inn;
	public $kpp;
	public $egrip;
	public $address;
	public $real_address;
	public $settlement_account;
	public $correspondent_account;
	public $bank_title;
	public $bank_bik;
	public $director_fio;
	public $director_position;
	public $director_source;
	public $director_source_info;
	public $package_id;
	public $internal;
	public $user_id;

	public function rules() {
		return array(
			array(
				'id,title,type,inn,address,real_address,settlement_account,correspondent_account,bank_title,
				bank_bik,director_fio,kpp,director_position,director_source,director_source_info,egrip,internal',
				'safe',
				'on'=>'safe'
			),
			array(
				'title,type,inn,address,real_address,settlement_account,correspondent_account,bank_title,bank_bik,director_fio','required'
			),
			array(
				'kpp,director_position,director_source','required','on'=>'ltd'
			),
			/*array(
				'egrip','required','on'=>'ip'
			),*/
			array(
				'type','match','allowEmpty'=>false,'pattern'=>'/^ltd|ip$/'
			),
			array(
				'director_source','match','allowEmpty'=>false,'pattern'=>'/^charter|warrant|order|protocol$/','on'=>'ltd'
			),
			array(
				'inn','match','allowEmpty'=>false,'pattern'=>'/^\d{10}(\d{2})?$/'
			),
			array(
				'settlement_account,correspondent_account','match','allowEmpty'=>false,'pattern'=>'/^\d{20}?$/'
			),
			array(
				'bank_bik','match','allowEmpty'=>false,'pattern'=>'/^\d{9}?$/'
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
	 * Метод выполняет запрос на сохранение данных пользователя на сервере
	 * @return bool
	 */
	public function save() {
		if($this->id>0) {
			$obModel=JurPersonReference::model()->findByPk($this->id);
			if($obModel) {
				$obModel->setScenario($this->type);
				$obModel->attributes=$this->attributes;
				return $obModel->save();
			} else {
				$this->addError('id','wrong id or data not found');
			}
		} else {
			/**
			 * @var $obUser People
			 */
			$obUser=People::model()->findByPk($this->user_id);
			if($obUser) {
				$obModel=new JurPersonReference();
				$obModel->setScenario($this->type);
				$obModel->attributes=$this->attributes;
				if($obModel->save()) {
					$obUser->jur_person_id=$obModel->id;
					return $obUser->update(array('jur_person_id'));
				}
			} else {
				$this->addError('user_id','user not found');
			}
		}
		return false;
	}

	/**
	 * Метод возвращает список значений доступны для поля director_source
	 */
	public static function getSourceList() {
		return JurPersonReference::getSourceList();
	}

	public static function getTypeList() {
		return JurPersonReference::getTypeList();
	}
}