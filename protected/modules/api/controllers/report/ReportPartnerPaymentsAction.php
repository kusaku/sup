<?php
/**
 * Класс выполняет запрос на получение счёта приложения
 */
class ReportPartnerPaymentsAction extends ApiUserAction implements IApiGetAction {

	private $_userId = 0;

	function run() {
		$this->_checkProtocolRequirements();
		$obToken=$this->getController()->getModule()->getApplicationTokens();
		$this->_userId = $obToken->getUserId();
		if($this->_userId == 0){
			throw new CHttpException(403,'Auth required',403);
		}
		$this->checkAccess();

		
		if(!isset($_GET['begin']) || !isset($_GET['end'])) {
			throw new CHttpException(400,'Bad request',400);
		}
		
		$iBegin = strtotime($_GET['begin']);
		$iEnd = strtotime($_GET['end']);

		if($iBegin > $iEnd) {
			throw new ApiException(1,'invalid dates');
		}

		$arResult['data'] = $this->_getReport($iBegin, $iEnd);
		$arResult['result']=200;
		$arResult['resultText']='ok';

		$this->getController()->render('json',array('data'=>$arResult));
	}

	private function _getReport($iBegin, $iEnd) {

		// установка критериев отбора
		$obCriteria = new CDbCriteria();

		// выборка по дате
		$obCriteria->compare('package.dt_beg', '>=' . date('Y-m-d', $iBegin));
		$obCriteria->compare('package.dt_beg', '<' . date('Y-m-d', $iEnd + 86399));

		// выборка по периоду
		$obCriteriaPay = new CDbCriteria();
		// выберем все платежи
		$obCriteriaPay->scopes = array(
			'recpay'
		);

		// клонируем критерий и добавляем к нему партнера
		$obCriteriaPack = new CDbCriteria();
		$obCriteriaPack->addColumnCondition(array(
			'id_partner' => $this->_userId,
		));

		$arPayments = Payment::model()->with(array(
			'package' => array(
				'joinType' => 'INNER JOIN',
				'condition' => $obCriteria->condition,
				'params' => $obCriteria->params
			),
			'package.client.owner_partner' => array(
				'joinType' => 'INNER JOIN',
				'condition' => $obCriteriaPack->condition,
				'params' => $obCriteriaPack->params
			),
			'package.client',
			'package.manager',
			'package.site',
			'package.promocode',
			'package.services',
			'package.wf_status',
			'package.pay_status',
		))->findAll($obCriteriaPay);

		$arResultPayments = array();
		$iMoneyTotal = 0;
		$iPaymentsTotal = 0;

		foreach ($arPayments as $obPayment) {

			$obPackage = $obPayment->package;

			if (!count($obPackage->servPack)){
				continue;
			}

			$iMoneyTotal += $obPayment->amount;
			$iPaymentsTotal++;

			$arResultPayments[] = array(
				'payment_id' => $obPayment->primaryKey,
				'payment_status' => $obPackage->pay_status->name,
				'payment_dt' => $obPayment->dt != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($obPayment->dt)) : '',
				'payment_summ' => $obPayment->amount,
				'client_id' => $obPackage->client_id,
				'client_mail' => $obPackage->client->mail,
				'client_fio' => $obPackage->client->fio,
				'manager_id' => $obPackage->manager_id,
				'manager_mail' => $obPackage->manager->mail,
				'manager_fio' => $obPackage->manager->fio,
				'package_id' => $obPackage->primaryKey,
				'package_type' => isset($obPackage->services) ? $obPackage->services[0]->name : '',
				'package_status' => $obPackage->wf_status->name,
				'package_name' => $obPackage->name,
				'package_descr' => $obPackage->descr,
				'package_site' => $obPackage->site['url'],
				'package_promocode' => $obPackage->promocode['code'],
				'package_dt_beg' => $obPackage->dt_beg != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($obPackage->dt_beg)) : '',
				'package_dt_end' => $obPackage->dt_end != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($obPackage->dt_end)) : '',
				'package_dt_change' => $obPackage->dt_change != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($obPackage->dt_change)) : '',
			);
		}

		return array(
			'payments' => $arResultPayments,
			'total' => array(
				'money' => $iMoneyTotal,
				'payments' => $iPaymentsTotal,
			)
		);
	}
}
