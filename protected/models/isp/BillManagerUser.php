<?php
/**
 * Класс обеспечивающий управление моделью записи пользователя в BillManager
 */
class BillManagerUser extends BillManager {
	public $id;
	public $name;
	public $realname;
	public $email;
	public $disabled;
	public $superuser;

	private $obPeople;
	
	public function attributeNames() {
		return array(
			'id','name','realname','email','disabled','superuser'
		);
	}
	
	public function attributeLabels() {
		return array(
			'id'=>Yii::t('billManager','id'),
			'name'=>Yii::t('billManager','name'),
			'realname'=>Yii::t('billManager','realname'),
			'email'=>Yii::t('billManager','email'),
			'disabled'=>Yii::t('billManager','disabled'),
			'superuser'=>Yii::t('billManager','superuser'),
		);
	}
	
	public function rules() {
		return array(
			array(
				'id,name,realname,email,disabled,superuser',
				'safe',
				'on'=>'init'
			),
			array(
				'id,name,realname,email,disabled,superuser',
				'required'
			),
			array(
				'email',
				'email',
			),
			array(
				'disabled',
				'match',
				'pattern'=>'/^(yes|no)$/'
			),
			array(
				'id',
				'numerical',
				'integerOnly'=>true
			)
		);
	}

	/**
	 * Метод пытается найти связанную запись в базе SUP
	 */
	public function getPeople($bRefresh=false) {
		if($bRefresh || is_null($this->obPeople)) {
			$obLink=BMUserData::model()->findByAttributes(array('user_id'=>$this->id));
			if($obLink) {
				$this->obPeople=$obLink->people;
			} else {
				$this->obPeople=null;
			}
		}
		return $this->obPeople;
	}

	public function getPeopleName($bRefresh=false) {
		$obPeople=$this->getPeople($bRefresh);
		if(!is_null($obPeople)) {
			return $obPeople->fio;
		}
		return '';
	}

	public function getPeopleId($bRefresh=false) {
		$obPeople=$this->getPeople($bRefresh);
		if(!is_null($obPeople)) {
			return $obPeople->id;
		}
		return 0;
	}

	/**
	 * Метод выполняет запрос в BillManager для получения данных пользователя по его номеру
	 */
	function load() {
		$this->obBMConnection->login();
		$arPost=array(
			'elid'=>$this->id,
			'func'=>'user.edit'
		);
		$obResult=$this->obBMConnection->asAdmin()->sendPost($arPost);
		if ($obResult->error) {
			switch($obResult->error['code']) {
				case 2:
					if(isset($obResult->error['obj'])) {
						$sField=$obResult->error['obj'];
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
						$sField=$obResult->error['val'];
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
			$this->name=(string)$obResult->name;
			$this->realname=(string)$obResult->realname;
			$this->superuser=(string)$obResult->superuser=='on'?'yes':'no';
			$this->disabled=false;
			$this->email='';
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
	 * @return array
	 */
	public function getCurrentFilter() {
		$arResult=array();
		$arPost=array('func'=>'user.filter');
		$obResult=$this->obBMConnection->asAdmin()->sendPost($arPost);
		if ($obResult->error) {
			throw new ISPAnswerException('Can`t get current filter stance: '.$obResult->error,$obResult->error['code']);
		} else {
			$arAvailableFields=array(
				'id','name','realname','email','account','userlang','elid','disabled','superuser'
			);
			foreach($arAvailableFields as $sField) {
				if(isset($obResult->$sField)) {
					$arResult[$sField]=(string)$obResult->$sField;
				}
			}
		}
		return $arResult;
	}

	/**
	 * Метод позволяет выполнить получение списка пользователей
	 */
	public function getList($arFilter=false,$limit=100) {
		//Настраиваем лимит
		$bSelfLimit=true;
		$arAvailableFields=array(
			'id','name','realname','email','account','userlang','disabled','superuser'
		);
		if($arFilter && is_array($arFilter) && count($arFilter)>0) {
			$arPost=array(
				'func'=>'user.filter',
				'sok'=>'ok'
			);
			foreach($arAvailableFields as $sField) {
				if(isset($arFilter[$sField])) {
					$arPost[$sField]=$arFilter[$sField];
				}
			}
			$obResult=$this->obBMConnection->asAdmin()->sendPost($arPost);
			if(!$obResult->ok) {
				if ($obResult->error) {
					throw new ISPAnswerException('Can\'t create domain contact record: '.$obResult->error,$obResult->error['code']);
				} else {
					throw new ISPAnswerException('Wrong protocol answer',1);
				}
			}
		}
		$arPost=array(
			'func'=>'user',
			'filter'=>'yes',
		);
		$obResult=$this->obBMConnection->asAdmin()->sendPost($arPost);
		if ($obResult->error) {
			throw new ISPAnswerException($obResult->error,$obResult->error['code']);
		} elseif ($obResult->elem) {
			$arResult=array();
			foreach($obResult->elem as $obElement) {
				$obNewElement=new BillManagerUser();
				$obNewElement->setConnection($this->obBMConnection);
				$obNewElement->id=(int) $obElement->id;
				$obNewElement->name=(string) $obElement->name;
				$obNewElement->realname=(string) $obElement->realname;
				$obNewElement->email=(string) $obElement->email;
				$disabled=(string)$obElement->disabled;
				if($disabled=='on') {
					$obNewElement->disabled='yes';
				} else {
					$obNewElement->disabled='no';
				}
				$superuser=(string)$obElement->superuser;
				if($superuser=='on') {
					$obNewElement->superuser='yes';
				} else {
					$obNewElement->superuser='no';
				}
				$arResult[$obNewElement->id]=$obNewElement;
			}
			if($bSelfLimit) {
				$arResult=array_slice($arResult,0,$limit);
			}
			return $arResult;
		}
		return array();
	}
}
