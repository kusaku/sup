<?php
/**
 * Класс обеспечивает обработку, подготовку и вывод формы редактирования заявки на домен
 */
class DomainRequestForm extends CFormModel {
	//Base fields
	public $id;
	public $package_id;
	public $client_id;
	public $site_id;
	public $domain;
	public $zone;
	public $mode;
	public $date_add;
	public $status;
	public $date_change;
	public $email;
	public $phone;
	public $fax;
	public $la_country;
	public $la_state;
	public $la_postcode;
	public $la_city;
	public $la_address;
	public $pa_state;
	public $pa_city;
	public $pa_postcode;
	public $pa_address;
	public $pa_addressee;
	//Company fields
	public $company_ru;
	public $company;
	public $inn;
	public $kpp;
	//Person fields
	public $firstname_ru;
	public $middlename_ru;
	public $lastname_ru;
	public $firstname;
	public $middlename;
	public $lastname;
	public $mobile;
	public $birthdate;
	public $passport;
	public $passport_series;
	public $passport_org;
	public $passport_date;

	public function attributeNames() {
		return array(
			'id','package_id','client_id','site_id','domain','zone','mode','date_add','status','date_change',
			'company_ru','company','email','phone','fax','la_country','la_state','la_postcode','la_city','la_address','inn','kpp',
			'firstname_ru','middlename_ru','lastname_ru','firstname','middlename','lastname','mobile','pa_state','pa_city','pa_postcode',
			'pa_address','pa_addressee','birthdate','passport','passport_series','passport_org','passport_date'
		);
	}

	public function rules() {
		return array(
			array(
				'id,package_id,client_id,site_id,domain,zone,mode,date_add,status,date_change',
				'safe',
				'on'=>'safe'
			),
			array(
				'company_ru,company,email,phone,fax,la_country,la_state,la_postcode,la_city,la_address,inn,kpp',
				'safe',
				'on'=>'safe'
			),
			array(
				'firstname_ru,middlename_ru,lastname_ru,firstname,middlename,lastname,mobile,pa_state,pa_city,pa_postcode,
				pa_address,pa_addressee,birthdate,passport,passport_series,passport_org,passport_date',
				'safe',
				'on'=>'safe'
			),
			array(
				'package_id,client_id,site_id,domain,zone,mode,company_ru,company,email,phone,mobile,
				fax,la_country,la_state,la_postcode,la_city,la_address,inn,kpp,firstname_ru,middlename_ru,lastname_ru,
				firstname,middlename,lastname,mobile,pa_state,pa_city,pa_postcode,
				pa_address,pa_addressee,birthdate,passport,passport_series,passport_org,passport_date',
				'safe',
				'on'=>'form'
			),
			array(
				'mode',
				'match',
				'allowEmpty'=>false,'pattern'=>'/^company|person|generic$/'
			),
			array(
				'domain,zone,mode,company,company_ru,email,phone,mobile,inn,kpp',
				'required',
				'on'=>'save_company'
			),
			array(
				'domain,zone,mode,firstname_ru,middlename_ru,lastname_ru,firstname,middlename,lastname,mobile,phone,email,passport_series',
				'required',
				'on'=>'save_person'
			),
			array(
				'phone,fax,mobile',
				'match',
				'on'=>'save_person,save_company',
				'allowEmpty'=>true,
				'pattern'=>'/^(\+\d+ \d+ \d+,?)+$/'
			)
		);
	}
	
	public function attributeLabels() {
		return array(
			'id'=>Yii::t('domainRequest','ID'),
			'package_id'=>Yii::t('domainRequest','Package'),
			'client_id'=>Yii::t('domainRequest','Client'),
			'site_id'=>Yii::t('domainRequest','Site'),
			'domain'=>Yii::t('domainRequest','Domain'),
			'zone'=>Yii::t('domainRequest','Domain zone'),
			'mode'=>Yii::t('domainRequest','Mode'),
			'date_add'=>Yii::t('domainRequest','Date add'),
			'status'=>Yii::t('domainRequest','Status'),
			'date_change'=>Yii::t('domainRequest','Date edit'),
			//Company fields
			'company_ru'=>Yii::t('domainRequest','Company (cyr)'),
			'company'=>Yii::t('domainRequest','Company'),
			'email'=>Yii::t('domainRequest','E-mail'),
			'phone'=>Yii::t('domainRequest','Phone'),
			'fax'=>Yii::t('domainRequest','Fax'),
			'la_country'=>Yii::t('domainRequest','Country'),
			'la_state'=>Yii::t('domainRequest','State'),
			'la_postcode'=>Yii::t('domainRequest','Zip code'),
			'la_city'=>Yii::t('domainRequest','City'),
			'la_address'=>Yii::t('domainRequest','Address'),
			'inn'=>Yii::t('domainRequest','VAT'),
			'kpp'=>Yii::t('domainRequest','KPP'),
			//Person fields
			'firstname_ru'=>Yii::t('domainRequest','Firstname (cyr)'),
			'middlename_ru'=>Yii::t('domainRequest','Middlename (cyr)'),
			'lastname_ru'=>Yii::t('domainRequest','Lastname (cyr)'),
			'firstname'=>Yii::t('domainRequest','Firstname'),
			'middlename'=>Yii::t('domainRequest','Middlename'),
			'lastname'=>Yii::t('domainRequest','Lastname'),
			'mobile'=>Yii::t('domainRequest','Mobile phone'),
			'pa_state'=>Yii::t('domainRequest','State'),
			'pa_city'=>Yii::t('domainRequest','City'),
			'pa_postcode'=>Yii::t('domainRequest','Zip code'),
			'pa_address'=>Yii::t('domainRequest','Address'),
			'pa_addressee'=>Yii::t('domainRequest','Address 2'),
			'birthdate'=>Yii::t('domainRequest','Birthday'),
			'passport'=>Yii::t('domainRequest','Passport'),
			'passport_series'=>Yii::t('domainRequest','Passport series'),
			'passport_org'=>Yii::t('domainRequest','Passport organisation'),
			'passport_date'=>Yii::t('domainRequest','Passport issued on'),
		);
	}

	/**
	 * Метод загружает данные формы из модели заявок на домены
	 * @throws CException
	 * @return bool
	 */
	public function load() {
		$obDomainRequest=DomainRequest::model()->findByPk($this->id);
		if($obDomainRequest) {
			$this->setScenario('safe');
			$this->attributes=$obDomainRequest->attributes;
			if($obDomainRequest->mode=='company' && $obDomainRequest->company) {
				$this->attributes=$obDomainRequest->company->attributes;
			} elseif($obDomainRequest->mode=='person' && $obDomainRequest->person) {
				$this->attributes=$obDomainRequest->person->attributes;
			}
			if($this->package_id>0) {
				$obPackage=Package::model()->findByPk($this->package_id);
				if($this->client_id==0)
					$this->client_id=$obPackage->client_id;
			}
			if($this->client_id==0)
				throw new CException('Wrong data structure',1);
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Метод выполняет запись данных формы в базу
	 * @throws CException
	 * @return bool
	 */
	public function save() {
		if($this->id==0) {
			if($this->package_id>0) {
				if($obPackage=Package::model()->findByPk($this->package_id)) {
					$this->client_id=$obPackage->client_id;
				} else {
					$this->addError('package_id',Yii::t('domainRequest','Package assigned to request not found'));
					return false;
				}
			} elseif($this->client_id>0) {
				if($obClient=People::model()->findByPk($this->client_id)) {
					$this->client_id=$obClient->id;
				} else {
					$this->addError('client_id',Yii::t('domainRequest','Client assigned to request not found'));
					return false;
				}
			} else {
				$this->addError('package_id',Yii::t('domainRequest','Package ID not assigned to request'));
				$this->addError('client_id',Yii::t('domainRequest','Client ID not assigned to request'));
				return false;
			}
			$obDomainRequest=new DomainRequest();
			$obDomainRequest->setScenario('new');
			$obDomainRequest->attributes=$this->attributes;
			if($this->mode=='company') {
				$obDomainData=new DomainRequestCompany();
			} elseif($this->mode=='person') {
				$obDomainData=new DomainRequestPerson();
			} else {
				$this->addError('mode',Yii::t('domainRequest','Unsupported mode'));
				return false;
			}
			$obDomainData->attributes=$this->attributes;
			$obDomainRequest->date_add=date('Y-m-d H:i:s');
			$obDomainRequest->date_change=date('Y-m-d H:i:s');
			if($obDomainRequest->save()) {
				$obDomainData->request_id=$obDomainRequest->id;
				$this->id=$obDomainRequest->id;
				return $obDomainData->save();
			}
		} else {
			if($this->status=='new') {
				$obDomainRequest=DomainRequest::model()->findByPk($this->id);
				$obDomainRequest->setScenario('edit');
				$obDomainRequest->attributes=$this->attributes;
				$obDomainRequest->date_change=date('Y-m-d H:i:s');
				if($obDomainRequest->mode=='company') {
					if($obDomainRequest->company==NULL) {
						$obDomainRequest->company=new DomainRequestCompany();
						$obDomainRequest->company->request_id=$obDomainRequest->id;
					}
					$obDomainRequest->company->attributes=$this->attributes;
					return $obDomainRequest->save() && $obDomainRequest->company->save();
				} else {
					if($obDomainRequest->person==NULL) {
						$obDomainRequest->person=new DomainRequestPerson();
						$obDomainRequest->person->request_id=$obDomainRequest->id;
					}
					$obDomainRequest->person->attributes=$this->attributes;
					return $obDomainRequest->save() && $obDomainRequest->person->save();
				}
			} else {
				throw new CException('Can\'t change not new requests',2);
			}
		}
		return false;
	}
}