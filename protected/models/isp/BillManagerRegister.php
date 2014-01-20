<?php
/**
 * Класс обеспечивающий управление моделью регистрации пользователя в BillManager
 */

class BillManagerRegister extends BillManager {
	const USER_TYPE_PERSON='pperson';
	const USER_TYPE_COMPANY='pcompany';
	const USER_TYPE_IP='psoleproprietor';
	
	public $ptype;
	public $person;
	public $country;
	public $username;
	public $name;
	public $account_id;
	public $user_id;
	public $profile_id;
	
	public function attributeNames() {
		return array(
			'ptype','person','country','username','name','account_id','user_id','profile_id'
		);
	}
	
	public function attributeLabels() {
		return array(
			'ptype'=>Yii::t('billManager','ptype'),
			'person'=>Yii::t('billManager','person'),
			'country'=>Yii::t('billManager','country'),
			'username'=>Yii::t('billManager','username'),
			'name'=>Yii::t('billManager','name'),
			'account_id'=>Yii::t('billManager','account_id'),
			'user_id'=>Yii::t('billManager','user_id'),
			'profile_id'=>Yii::t('billManager','profile_id')
		);
	}
	
	public function rules() {
		return array(
			array(
				'person,ptype,country,name,username',
				'safe',
				'on'=>'init'
			),
			array(
				'person,ptype,country,username',
				'required'
			),
			array(
				'name',
				'required',
				'on'=>'company'
			),
			array(
				'username',
				'match',
				'allowEmpty'=>false,
				'pattern'=>'/^[a-z0-9]{5,14}$/'
			),
			array(
				'ptype',
				'match',
				'allowEmpty'=>false,
				'pattern'=>'/^'.self::USER_TYPE_PERSON.'|'.self::USER_TYPE_COMPANY.'|'.self::USER_TYPE_IP.'$/'
			),
			array(
				'country',
				'validatorCountry'
			),
			array(
				'user_id,account_id,profile_id',
				'numerical',
				'integerOnly'=>true
			)
		);
	}
	
	/**
	 * Валидатор для поля country, проверяет наличие указанной страны в базе стран
	 */
	public function validatorCountry($attribute,$params) {
		if(!People::getCountryById($this->$attribute)) {
			$this->addError($attribute, Yii::t('billManager','Wrong country in {attribute} field',array('{attribute}'=>$this->getAttributeLabel($attribute))));
		}
	}

	/**
	 * регистрация пользователя
	 * @throws ISPException
	 * @throws ISPAnswerException
	 * @internal param array $data ассоциативный массив с данными
	 * @return array $result
	 */
	 
	public function register() {
		$arPost=$this->attributes;
		$arPost['func']='register';
		$arPost['sok']='ok';
		$obResult=$this->obBMConnection->sendPost($arPost);
		
		if ($obResult->error) {
			switch($obResult->error['code']) {
				case 2:
					if(isset($obResult->error['obj'])) {
						$sField=(string)$obResult->error['obj'];
						if(in_array($sField,$this->attributeNames())) {
							$this->addError($sField, Yii::t('billManager','User with {field} already registered',array('{field}'=>$this->getAttributeLabel($sField))));
						} else {
							throw new ISPAnswerException((string)$obResult->error['obj'],2);
						}
					} else {
						throw new ISPAnswerException($obResult->error,(string)$obResult->error['code']);
					}
				break;
				case 3:
					throw new ISPAnswerException((string)$obResult->error['obj'],3);
				break;
				case 4:
					if(isset($obResult->error['val'])) {
						$sField=(string)$obResult->error['val'];
						if(in_array($sField,$this->attributeNames())) {
							$this->addError($sField, Yii::t('billManager','{field} filled wrong or not filled',array('{field}'=>$this->getAttributeLabel($sField))));
						} else {
							throw new ISPAnswerException((string)$obResult->error['val'],4);
						}
					} else {
						throw new ISPAnswerException($obResult->error,(string)$obResult->error['code']);
					}
				break;
				default:
					throw new ISPAnswerException($obResult->error,(string)$obResult->error['code']);
			}
		} elseif ($obResult->ok) {
			$sAccountId='account.id';
			$this->account_id=(string)$obResult->$sAccountId;
			$sUserId='user.id';
			$this->user_id=(string)$obResult->$sUserId;
			$sProfileId='profile.id';
			$this->profile_id=(string)$obResult->$sProfileId;
			return true;
		} else {
			throw new ISPAnswerException('Wrong protocol answer');
			$cdata = array();
			foreach ($result as $name=>$value) {
				$cdata[$name] = (string) $value;
			}
		}
	}
}
