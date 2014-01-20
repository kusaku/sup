<?php

/**
 * Класс выполняет обработку функции AddService
 */
class ListPartnersAction extends ApiUserAction implements IApiGetAction {

	protected $_bAll = false;
	protected $_myself = 0;

	function run() {
		$this->_checkProtocolRequirements();

		$this->checkAccess();

		$obToken = $this->getController()->getModule()->getApplicationTokens();
		if ($obToken->getUserId() == 0) {
			throw new CHttpException(403, 'Auth required', 403);
		}

		//подготовливаем условия выборки
		$obCriteria = new CDbCriteria();
		$obCriteria->with = array(
			'user_data',
			'manager',
			'infocode',
		);

		//Всегда можем выбрать информацию о себе (как партнере)
		if($this->_myself > 0){
			$obCriteria->addCondition('t.id = :id');
			$obCriteria->params += array(
				':id' => $this->_myself,
			);
		}
		//Ограничиваем выводом только своих партнеров и неприсвоенных, если мы не имеем права выводить всех.
		if($this->_myself <= 0 && !$this->_bAll){
			$obCriteria->addCondition('t.manager_id IN(:manager_id,0)');
			$obCriteria->params += array(
				':manager_id' => $this->getController()->getModule()->getApplicationTokens()->getUserId(),
			);
		}
		//Выводим определенного партнера, если запрашиваем только его и он нам доступен.
		if($this->_myself <= 0 && (isset($_GET['id']) and $id = (int)$_GET['id'])){
			$obCriteria->addCondition('t.id = :id');
			$obCriteria->params += array(
				':id' => $id,
			);
		}

		$arPartners = Partner::model()->findAll($obCriteria);
		$arResult = array(
			'list' => array(),
		);
		foreach ($arPartners as $obPartner) {
			$obUserData = $obPartner->user_data;
			$arRow['partner_id'] = $obUserData['id'];
			$arRow['partner_fio'] = $obUserData['fio'];
			$arRow['partner_mail'] = $obUserData['mail'];
			$arRow['partner_state'] = $obUserData['state'];
			$arRow['partner_phone'] = $obUserData['phone'];
			$arRow['partner_descr'] = $obUserData['descr'];
			$arRow['partner_regdate'] = $obUserData['regdate'];
			$arRow['partner_datesign'] = $obPartner->date_sign;
			$arRow['partner_status'] = $obPartner->status;
			$arRow['partner_infocode'] = isset($obPartner->infocode) ? $obPartner->infocode['value'] : '';
			$arRow['partner_firm'] = $obUserData['firm'];
			$arRow['partner_percent'] = $obPartner->percent;
			$arRow['partner_min_withdrawal'] = $obPartner->min_withdrawal;
			$arRow['partner_type'] = $obPartner->type;
			$arRow['manager_id'] = $obPartner->manager_id;
			$arRow['manager_fio'] = $obPartner->manager['fio'];
			$arRow['manager_mail'] = $obPartner->manager['mail'];

			$arResult['list'][] = $arRow;
		}
		$arResult['result'] = 200;
		$arResult['resultText'] = 'ok';

		$this->getController()->render('json', array('data' => $arResult));
	}

}
