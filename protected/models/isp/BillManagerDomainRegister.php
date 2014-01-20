<?php
/**
 * Класс обеспечивающий управление моделью регистрации заявки на домен в BillManager
 * @property string $operation - вид операции над доменом register|transfer (регистрация или перенос)
 *
 */
class BillManagerDomainRegister extends BillManager {
	private $arAvailablePrices=array(
		84=>array(
			'price'=>54,
			'period'=>30,
			'autoprolong'=>30
		),
		85=>array(
			'price'=>55,
			'period'=>34,
			'autoprolong'=>30
		),
		86=>array(
			'price'=>56,
			'period'=>38,
			'autoprolong'=>30
		),
		87=>array(
			'price'=>57,
			'period'=>42,
			'autoprolong'=>30
		),
		88=>array(
			'price'=>38,
			'period'=>16,
			'autoprolong'=>30
		),
		89=>array(
			'price'=>58,
			'period'=>48,
			'autoprolong'=>30
		),
		90=>array(
			'price'=>59,
			'period'=>52,
			'autoprolong'=>30
		),
		91=>array(
			'price'=>60,
			'period'=>56,
			'autoprolong'=>30
		),
		92=>array(
			'price'=>61,
			'period'=>60,
			'autoprolong'=>30
		),
		93=>array(
			'price'=>62,
			'period'=>64,
			'autoprolong'=>30
		),
		94=>array(
			'price'=>63,
			'period'=>68,
			'autoprolong'=>30
		),
		95=>array(
			'price'=>64,
			'period'=>72,
			'autoprolong'=>30
		)
	);

/*<select tabindex="1" class="input" onchange="" name="price" dependindex="3"><option value="54">Регистрация\продление домена .com</option>
<option value="73">Регистрация\продление домена .me</option>
<option value="55">Регистрация\продление домена .net</option>
<option value="56">Регистрация\продление домена .org</option>
<option value="60">Регистрация\продление домена spb.ru</option>
<option value="57">Регистрация\продление домена .рф</option>
<option value="38">Регистрация\продление домена .ru</option>
<option value="58">Регистрация\продление домена .info</option>
<option value="59">Регистрация\продление домена .su</option>
<option value="61">Регистрация\продление домена spb.su</option>
<option value="62">Регистрация\продление домена msk.ru</option>
<option value="63">Регистрация\продление домена msk.su</option>
<option value="64">Регистрация\продление домена net.ru</option>
<option value="65">Регистрация\продление домена .tv</option>
<option value="66">Регистрация\продление домена .pro</option>
<option value="67">Регистрация\продление домена org.ru</option>
<option value="69">Регистрация\продление домена .cc</option>
<option value="70">Регистрация\продление домена com.ru</option></select>*/

	public $operation;
	public $domain;
	public $price;
	public $promocode;
	public $customer;
	public $account;
	public $period;
	public $ns_list='ns1.fabricasaitov.ru ns2.fabricasaitov.ru';


	public function attributeNames() {
		return array(
			'operation','domain','price','promocode'
		);
	}

	public function attributeLabels() {
		return array(
			'operation'=>Yii::t('billManager','operation'),
			'domain'=>Yii::t('billManager','domain'),
		);
	}

	/**
	 * Метод выполняет установку цены и других внутренних параметров в зависимости от доменной зоны
	 * @param $sZone
	 */
	public function setDomainZone($sZone) {

	}

	public function rules() {
		return array(
			array(
				'operation',
				'match',
				'pattern'=>'/^(register|transfer)$/',
				'on'=>'create'
			),
		);
	}

	/**
	 * регистрация пользователя
	 * @param array $data ассоциативный массив с данными
	 * @return array $result
	 */
	public function register() {
		$arPost=array(
			'func'=>'domain.order.4',
			'customer'=>$this->customer,
			'domain'=>$this->domain,
			'price'=>$this->price,
			'account'=>$this->account,
			'nslist_0'=>$this->ns_list,
			'operation'=>$this->operation,
			'period_0'=>$this->period,
			'projectns'=>$this->ns_list,
			'registrar'=>2,
			'sok'=>'ok'
		);
		$obResult=$this->obBMConnection->asUser()->sendPost($arPost);

		if ($obResult->error) {
			$iCode=isset($obResult->error['code'])?intval((string)$obResult->error['code']):0;
			$sObj=isset($obResult->error['obj'])?(string) $obResult->error['obj']:'';
			$sVal=isset($obResult->error['val'])?(string) $obResult->error['val']:'';
			switch($iCode) {
				case 2:
					if($sObj=='') {
						if(in_array($sObj,$this->attributeNames())) {
							$this->addError($sObj, Yii::t('billManager','Domain with {field} already registered',array('{field}'=>$this->getAttributeLabel($sObj))));
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
			print_r($obResult);
			die();
			$this->id=intval((string)$obResult->id);
			return true;
		} else {
			throw new ISPAnswerException('Wrong protocol answer',2);
		}
		return false;
	}
}
