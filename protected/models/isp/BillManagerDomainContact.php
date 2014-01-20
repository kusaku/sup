<?php
/**
 *
 */
class BillManagerDomainContact extends BillManager {
	const USER_TYPE_PERSON='person';
	const USER_TYPE_COMPANY='company';
	const USER_TYPE_IP='generic';

	public $id;
	public $ctype;
	public $name;
	public $verified;
	public $company_ru;
	public $firstname_ru;
	public $middlename_ru;
	public $lastname_ru;
	public $company;
	public $firstname;
	public $middlename;
	public $lastname;
	public $email;
	public $phone;
	public $fax;
	public $mobile;
	public $private;
	public $la_country;
	public $la_state;
	public $la_postcode;
	public $la_city;
	public $la_address;
	public $pa_state;
	public $pa_postcode;
	public $pa_city;
	public $pa_address;
	public $pa_addressee;
	public $inn;
	public $kpp;
	public $birthdate;
	public $passport;
	public $passport_series;
	public $passport_org;
	public $passport_date;

	private $bLoaded;

	public function init() {
		$this->bLoaded=false;
	}

	public function rules() {
		return array(
			array(
				'name,verified,company_ru,company,email,phone,fax,la_country,la_state,la_postcode,la_city,la_address,inn,kpp',
				'safe',
				'on'=>'safe'
			),
			array(
				'firstname_ru,middlename_ru,lastname_ru,firstname,middlename,lastname,mobile,pa_state,pa_city,pa_postcode,
				pa_address,pa_addressee,birthdate,passport,passport_series,passport_org,passport_date',
				'safe',
				'on'=>'safe'
			),
		);
	}

	public function attributeNames() {
		return array(
			'id','ctype','name','verified','company_ru','firstname_ru','middlename_ru','lastname_ru','company','firstname',
			'middlename','lastname','email','phone','fax','mobile','private','la_country','la_state','la_postcode','la_city',
			'la_address','pa_state','pa_postcode','pa_city','pa_address','pa_addressee','inn','kpp','birthdate',
			'passport','passport_series','passport_org','passport_date',
		);
	}

	public function attributeLabels() {
		return array(
			'id'=>Yii::t('domainContact','id'),
			'ctype'=>Yii::t('domainContact','ctype'),
			'name'=>Yii::t('domainContact','name'),
			'verified'=>Yii::t('domainContact','verified'),
			'company_ru'=>Yii::t('domainContact','company_ru'),
			'firstname_ru'=>Yii::t('domainContact','firstname_ru'),
			'middlename_ru'=>Yii::t('domainContact','middlename_ru'),
			'lastname_ru'=>Yii::t('domainContact','lastname_ru'),
			'company'=>Yii::t('domainContact','company'),
			'firstname'=>Yii::t('domainContact','firstname'),
			'middlename'=>Yii::t('domainContact','middlename'),
			'lastname'=>Yii::t('domainContact','lastname'),
			'email'=>Yii::t('domainContact','email'),
			'phone'=>Yii::t('domainContact','phone'),
			'fax'=>Yii::t('domainContact','fax'),
			'mobile'=>Yii::t('domainContact','mobile'),
			'private'=>Yii::t('domainContact','private'),
			'la_country'=>Yii::t('domainContact','la_country'),
			'la_state'=>Yii::t('domainContact','la_state'),
			'la_postcode'=>Yii::t('domainContact','la_postcode'),
			'la_city'=>Yii::t('domainContact','la_city'),
			'la_address'=>Yii::t('domainContact','la_address'),
			'pa_state'=>Yii::t('domainContact','pa_state'),
			'pa_postcode'=>Yii::t('domainContact','pa_postcode'),
			'pa_city'=>Yii::t('domainContact','pa_city'),
			'pa_address'=>Yii::t('domainContact','pa_address'),
			'pa_addressee'=>Yii::t('domainContact','pa_addressee'),
			'inn'=>Yii::t('domainContact','inn'),
			'kpp'=>Yii::t('domainContact','kpp'),
			'birthdate'=>Yii::t('domainContact','birthdate'),
			'passport'=>Yii::t('domainContact','passport'),
			'passport_series'=>Yii::t('domainContact','passport_series'),
			'passport_org'=>Yii::t('domainContact','passport_org'),
			'passport_date'=>Yii::t('domainContact','passport_date'),
		);
	}

	/**
	 * Метод сохраняет значения из полей модели в ISP manager
	 */
	public function save() {
		$arPost=array(
			'func'=>'domaincontact.edit',
			'sok'=>'ok'
		);
		if($this->id>0) {
			$arPost['elid']=$this->id;
			foreach($this->attributeNames() as $sField) {
				if($this->$sField!='')
					$arPost[$sField]=$this->$sField;
			}
		} else {
			$arPostCreate=array(
				'func'=>'contcat.create.1',
				'ctype'=>$this->ctype,
				'cname'=>$this->name,
				'sok'=>'ok'
			);
			$obResult=$this->obBMConnection->asUser()->sendPost($arPostCreate);
			if ($obResult->error) {
				throw new ISPAnswerException('Can\'t create domain contact record: '.$obResult->error,$obResult->error['code']);
			} elseif ($obResult->ok) {
				$sDomaincontactId='domaincontact.id';
				$this->id=intval((string)$obResult->$sDomaincontactId);
				$arPost['elid']=$this->id;
				foreach($this->attributeNames() as $sField) {
					if($this->$sField!='')
						$arPost[$sField]=$this->$sField;
				}
			} else {
				throw new ISPAnswerException('Wrong protocol answer',1);
			}
		}
		$obResult=$this->obBMConnection->asUser()->sendPost($arPost);

		if ($obResult->error) {
			$iCode=isset($obResult->error['code'])?intval((string)$obResult->error['code']):0;
			$sObj=isset($obResult->error['obj'])?(string) $obResult->error['obj']:'';
			$sVal=isset($obResult->error['val'])?(string) $obResult->error['val']:'';
			switch($iCode) {
				case 2:
					if($sObj=='') {
						if(in_array($sObj,$this->attributeNames())) {
							$this->addError($sObj, Yii::t('billManager','Domain contact with {field} already registered',array('{field}'=>$this->getAttributeLabel($sObj))));
						} else {
							throw new ISPAnswerException($sObj,2);
						}
					} else {
						throw new ISPAnswerException((string)$obResult->error,$iCode);
					}
					break;
				case 3:
					throw new ISPAnswerException($sObj,3);
					break;
				case 4:
					if($sVal!='') {
						if(in_array($sVal,$this->attributeNames())) {
							$this->addError($sVal, Yii::t('billManager','{field} filled wrong or not filled',array('{field}'=>$this->getAttributeLabel($sVal))));
						} else {
							throw new ISPAnswerException($sVal,4);
						}
					} else {
						throw new ISPAnswerException((string)$obResult->error,$iCode);
					}
					break;
				default:
					throw new ISPAnswerException((string)$obResult->error,$iCode);
			}
		} elseif ($obResult->ok) {
			$this->id=intval((string)$obResult->id);
			return true;
		} else {
			throw new ISPAnswerException('Wrong protocol answer',2);
		}
		return false;
	}

	/**
	 * Метод загружает в поля модели значения из ISPManager
	 * @return bool
	 * @throws ISPAnswerException
	 */
	public function load() {
		$arPost=array(
			'func'=>'domaincontact.edit',
			'elid'=>$this->id,
		);
		$obResult=$this->obBMConnection->asUser()->sendPost($arPost);
		if ($obResult->error) {
			switch($obResult->error['code']) {
				case 2:
					if(isset($obResult->error['obj'])) {
						$sField=(string)$obResult->error['obj'];
						if(in_array($sField,$this->attributeNames())) {
							$this->addError($sField, Yii::t('billManager','User with {field} already registered',array('{field}'=>$this->getAttributeLabel($sField))));
						} else {
							throw new ISPAnswerException($obResult->error['obj'],2);
						}
					} else {
						throw new ISPAnswerException($obResult->error,$obResult->error['code']);
					}
					break;
				case 3:
					throw new ISPAnswerException($obResult->error['obj'],3);
					break;
				case 4:
					if(isset($obResult->error['val'])) {
						$sField=(string)$obResult->error['val'];
						if(in_array($sField,$this->attributeNames())) {
							$this->addError($sField, Yii::t('billManager','{field} filled wrong or not filled',array('{field}'=>$this->getAttributeLabel($sField))));
						} else {
							throw new ISPAnswerException($obResult->error['val'],4);
						}
					} else {
						throw new ISPAnswerException($obResult->error,$obResult->error['code']);
					}
					break;
				default:
					throw new ISPAnswerException($obResult->error,$obResult->error['code']);
			}
		} elseif ($obResult->id && (int)$obResult->id==$this->id) {
			$arFields=$this->attributeNames();
			foreach($obResult->children() as $sField=>$sValue) {
				if(in_array($sField,$arFields)) {
					$this->$sField=(string)$sValue;
				}
			}
			return true;
		} else {
			throw new ISPAnswerException('Wrong protocol answer');
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
		}
		return false;
	}

	/**
	 * @return BillManagerDomainContact[]|bool
	 * @throws ISPAnswerException
	 */
	public function getList() {
		$arPost=array(
			'func'=>'domaincontact'
		);
		$obResult=$this->obBMConnection->asUser()->sendPost($arPost);
		if ($obResult->error) {
			throw new ISPAnswerException($obResult->error,$obResult->error['code']);
		} elseif ($obResult->elem) {
			$arResult=array();
			foreach($obResult->elem as $obElement) {
				$obNewElement=new BillManagerDomainContact();
				$obNewElement->setConnection($this->obBMConnection);
				$obNewElement->id=(int) $obElement->id;
				$obNewElement->name=(string) $obElement->name;
				$arResult[$obNewElement->id]=$obNewElement;
			}
			return $arResult;
		}
		return false;
	}
}
