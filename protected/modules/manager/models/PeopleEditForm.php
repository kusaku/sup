<?php
/**
 * Класс обеспечивает обработку формы редактирования пользователя
 */
class PeopleEditForm extends CFormModel {

	public $id;
	public $pgroup_id;
	public $mail;
	public $login;
	public $psw;
	public $rm_token;
	public $fio;
	public $state;
	public $phone;
	public $firm;
	public $descr;
	public $parent_id;
	public $image;
	public $notice_email;

	public $arAttributes=array();

	public function rules() {
		return array(
			array(
				'id,pgroup_id,mail,login,rm_token,fio,state,phone,firm,descr,parent_id,image,arAttributes,notice_email','safe','on'=>'safe'
			),
			array(
				'mail,fio','required','on'=>'form'
			),
			array(
				'mail','email','on'=>'form'
			),
			array(
				'pgroup_id','validateGroup','on'=>'form'
			),
			/*array(
				'login','length','min'=>3,'max'=>128,'on'=>'form'
			),*/
			array(
				'psw','safe','on'=>'form'
			),
			array(
				'id,fio,state,phone,firm,descr,rm_token,login,notice_email','safe'
			)
		);
	}

	public function attributeNames() {
		return array(
			'id',
			'pgroup_id',
			'rm_token',
			'mail',
			'login',
			'psw',
			'fio',
			'state',
			'phone',
			'firm',
			'descr',
			'parent_id',
			'image',
			'notice_email'
		);
	}

	public function attributeLabels() {
		return array(
			'id'=>Yii::t('peopleform','ID'),
			'pgroup_id'=>Yii::t('peopleform','Group'),
			'mail'=>Yii::t('peopleform','E-mail'),
			'login'=>Yii::t('peopleform','Login'),
			'psw'=>Yii::t('peopleform','Password'),
			'rm_token'=>Yii::t('peopleform','Redmine API key'),
			'fio'=>Yii::t('peopleform','Name'),
			'state'=>Yii::t('peopleform','Country'),
			'phone'=>Yii::t('peopleform','Phone'),
			'firm'=>Yii::t('peopleform','Company'),
			'descr'=>Yii::t('peopleform','Description'),
			'parent_id'=>Yii::t('peopleform','Parent'),
			'image'=>Yii::t('peopleform','Photo'),
			'notice_email'=>Yii::t('peopleform','Send registration email')
		);
	}

	/**
	 * @return People
	 */
	public function getPeople() {
		if($this->id>0) {
			return People::getById($this->id);
		}
		return null;
	}

	public function init() {
		$this->pgroup_id=7;
		foreach (Attributes::model()->with('children')->getGroups() as $group) {
			foreach ($group->children as $attr) {
				$this->arAttributes[$attr->type]=$attr->defval!=''?$attr->defval:'';
			}
		}
	}

	/**
	 * Метод заполняет поле arAttributes значениями связанными с моделью
	 * @param $obPeople People
	 */
	public function fillAttributes($obPeople) {
		foreach (Attributes::model()->with('children')->getGroups() as $group) {
			foreach ($group->children as $attr) {
				$this->arAttributes[$attr->type]=isset($obPeople->attr[$attr->type]) ? $obPeople->attr[$attr->type]->values[0]->value : '';
			}
		}
	}

	/**
	 * Валидатор для поля pgroup_id, проверяет правильность установленной группы
	 */
	public function validateGroup($attribute,$params) {
		$id=$this->$attribute;
		if(!PeopleGroup::model()->findByPk($id)) {
			$this->addError($attribute, Yii::t('peopleform','{attribute} has wrong group value',array('{attribute}'=>$this->getAttributeLabel($attribute))));
		}
	}

	/**
	 * @throws CException
	 * @return bool
	 */
	public function save() {
		/**
		 * @var People $obPeople
		 */
		$sOpenPassword='';
		if($this->id>0) {
			$obPeople=People::model()->findByPk($this->id);
			if(!$obPeople)
				throw new CException('User not found');
		} else {
			$obPeople=new People();
			$obPeople->pgroup_id=7;
			if($this->psw=='') {
				$sOpenPassword = People::genPassword();
				$this->psw=$sOpenPassword;
			}
		}
		$bNew = $obPeople->isNewRecord;
		// роль, логин и пароль пользователя может изменить только админ или модер
		if (Yii::app()->user->checkAccess('admin') or Yii::app()->user->checkAccess('moder')) {
			if($this->pgroup_id>0)
				$obPeople->pgroup_id=$this->pgroup_id;
			if($this->login!='')
				$obPeople->login=trim($this->login);
		}

		if($this->psw!='') {
			$obPeople->psw=People::hashPassword($this->psw);
		}
		$obPeople->rm_token=$this->rm_token;
		$obPeople->fio=trim($this->fio);
		$obPeople->parent_id=intval($this->parent_id);
		$obPeople->mail=trim($this->mail);
		$obPeople->state=htmlspecialchars(trim($this->state),ENT_COMPAT,'utf-8',false);
		$obPeople->phone=htmlspecialchars(trim($this->phone),ENT_COMPAT,'utf-8',false);
		$obPeople->firm=htmlspecialchars(trim($this->firm),ENT_COMPAT,'utf-8',false);
		$obPeople->descr=htmlspecialchars(trim($this->descr),ENT_COMPAT,'utf-8',false);

		// включаем правила валидации для модели People и сценария peopleControllerSave
		$obPeople->setScenario("peopleControllerSave");

		if ($obPeople->login == '' && $obPeople->mail != '') {
			$obPeople->login = $obPeople->mail;
		}

		if ($obPeople->validate() and $obPeople->save()) {
			$this->attributes=$obPeople->attributes;
			$this->id=$obPeople->id;
			$this->psw='';
			if ($bNew) {
				$obAPIModule = Yii::app()->getModule('api');
				$obAPIModule->getUserAuth()->assign(Yii::app()->params['apiConfig']['new_people_auth_item'], $obPeople->id);
				//Отправляем письмо и сохраняем его в лог
				if($this->notice_email==1) {
					try {
						Yii::app()->getComponent('documents')->createEmailRegister($obPeople, $sOpenPassword)->send($obPeople->mail, $obPeople->fio);
					}
					catch(exception $e) {}
				}
			}
			// сохраняем атрибуты
			foreach (Attributes::model()->with('children')->getGroups() as $group) {
				foreach ($group->children as $attr) {
					if(isset($this->arAttributes[$attr->type])) {
						$value=$this->arAttributes[$attr->type];
						$id=$attr->id;
						$attr = isset($obPeople->values[$id]) ? $obPeople->values[$id] : new PeopleAttr();
						$attr->attribute_id = $id;
						$attr->people_id = $obPeople->primaryKey;

						if ( empty($attr->attr->regexp) || preg_match($attr->attr->regexp, $value)) {
							$attr->value = $value;
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
			return true;
		} else {
			$this->addErrors($obPeople->getErrors());
		}
		return false;
	}
}