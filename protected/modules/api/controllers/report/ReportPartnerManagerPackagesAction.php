<?php
/**
 * Класс выполняет запрос на получение счёта приложения
 */
class ReportPartnerManagerPackagesAction extends ApiUserAction implements IApiGetAction {

	private $_userId = 0;
	protected $_forAllPartners = true;
	protected $_type = Partner::TP_DEFAULT;
	private $_obToken = null;

	function run() {
		$this->_checkProtocolRequirements();
		$this->_obToken=$this->getController()->getModule()->getApplicationTokens();
		$this->_userId = $this->_obToken->getUserId();
		if($this->_userId == 0){
			throw new CHttpException(403,'Auth required',403);
		}
		$this->checkAccess();

		if(!(isset($_GET['begin']) && isset($_GET['end']))) {
			throw new CHttpException(400,'Bad request',400);
		}

		$iBegin = $_GET['begin'] === '0' ? 0 : strtotime($_GET['begin']);
		$iEnd = $_GET['end'] === '0' ? 0 : strtotime($_GET['end']);

		if($iBegin != 0 && $iEnd != 0 && $iBegin > $iEnd) {
			throw new ApiException(1,'invalid dates');
		}

		isset($_GET['paid']) and $bPaid = (boolean)$_GET['paid'];
		isset($_GET['finished']) and $bFinished = (boolean)$_GET['finished'];

		$arResult['data'] = $this->_getReport($iBegin, $iEnd, $bPaid, $bFinished);
		$arResult['result']=200;
		$arResult['resultText']='ok';

		$this->getController()->render('json',array('data'=>$arResult));
	}

	private function _getReport($iBegin, $iEnd, $bPaid = true, $bFinished = false) {

		// установка критериев отбора
		$obCriteria = new CDbCriteria();

		if($this->_forAllPartners){
			$obCriteria->condition = 'owner_partner.id_partner IS NOT NULL';
		} else {
			$obCriteria->compare('owner_partner.id_partner', '=' . $this->_userId);
		}

		$obCriteria->compare('partner_data.type',$this->_type);
		if($this->_type == Partner::TP_CONSULTANT){
			$obCriteria->order = 't.id ASC';
			$obCriteria->group = 't.client_id';
		}

		// выборка по дате
		if(!($iBegin == 0 && $iEnd == 0)){
			$obCriteria->compare('t.dt_beg', '>=' . date('Y-m-d', $iBegin));
			$obCriteria->compare('t.dt_beg', '<' . date('Y-m-d', $iEnd + 86399));
		}

		$obCriteria->scopes = array();

		// выберем все оплаченные
		$bPaid and $obCriteria->scopes += array('paid');

		// выбераем все выполненные
		$bFinished and $obCriteria->scopes += array('finished');

		/**
		 * Если мы менеджер партнеров, но не супер менеджер,
		 * необходимо выводить только те заказы, которые принадлежат партнером этого менеджера
		 */
		if($this->_obToken->hasRole('PartnerManager') && !$this->_obToken->hasRole('PartnerSuperManager')){
			$obCriteria->addCondition('manager.id = :id_manager');
			$obCriteria->params += array(':id_manager'=>$this->_userId);
		}

		$arPackages = Package::model()->with(array(
			'client',
			'client.owner_partner.partner',
			'client.owner_partner.partner.partner_data',
			'client.owner_partner.partner.partner_data.manager',
			'promocode',
			'wf_status',
			'pay_status',
		))->findAll($obCriteria);

		$arResultPackages = array();
		$iMoneyTotal = 0;
		$iPaymentsTotal = 0;

		foreach ($arPackages as $obPackage) {

			$iMoneyTotal += $obPackage->summ;
			$iPaymentsTotal++;
			$obPartner = $obPackage->client->owner_partner->partner;

			$arManagerData = isset($obPartner->partner_data->manager)
					? array(
						'id'=>$obPartner->partner_data->manager->id,
						'mail'=>$obPartner->partner_data->manager->mail,
						'fio'=>$obPartner->partner_data->manager->fio,
					)
					: array();

			$arResultPackages[] = array(
				'partner_mail' => $obPartner->mail,
				'partner_id' => $obPartner->primaryKey,
				'partner_fio' => $obPartner->fio,
				'partner_infocode' => isset($obPartner->partner_data->infocode) ? $obPartner->partner_data->infocode->value : '',
				'partner_percent' => $obPartner->partner_data->percent,
				'partner_status' => $obPartner->partner_data->status,
				'partner'=>array(
					'manager'=>$arManagerData,
				),
				'client_mail' => $obPackage->client->mail,
				'client_id' => $obPackage->client->primaryKey,
				'client_fio' => $obPackage->client->fio,
				'client_promocode' => isset($obPackage->client->promocodes[0]) ? $obPackage->client->promocodes[0]->code : '',
				'package_id' => $obPackage->primaryKey,
				'package_promocode' => $obPackage->promocode['code'],
				'package_summ' => $obPackage->summ,
				'package_partner_percent' => $obPackage->partner_percent,
				'package_pay_status' => $obPackage->pay_status->text_ident,
				'package_status' => $obPackage->wf_status->text_ident,
				'package_dt_beg' => $obPackage->dt_beg != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($obPackage->dt_beg)) : '',
				'package_dt_end' => $obPackage->dt_end != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($obPackage->dt_end)) : '',
				'package_dt_change' => $obPackage->dt_change != '0000-00-00 00:00:00' ? date('Y.m.d', strtotime($obPackage->dt_change)) : '',
				'package_name' => $obPackage->name,
				'package_descr' => $obPackage->descr,
				'package_site' => $obPackage->site['url'],
			);
		}

		return array(
			'packages' => $arResultPackages,
		);
	}
}
