<?php
/**
 * Класс выполняет обработку функции GetPackageInfo
 */
class GetPackageInfoAction extends ApiUserAction implements IApiGetAction {
	protected $obToken;
	protected $obPackage;
	/**
	 * Метод обеспечивает проверку прав доступа для текущего и обрабатываемого пользователя
	 */
	protected function _prepareAndCheckPackage() {
		$this->obToken=$this->getController()->getModule()->getApplicationTokens();
		if($this->obToken->getUserId()==0)
			throw new CHttpException(403,'Auth required',403);
		$this->obPackage=Package::model()->findByPk(intval($_GET['packageID']));
		if(!$this->obPackage)
			throw new ApiException(1,'no package');
		//Если хотим не свой пакет, надо убедиться, что есть права
		if($this->obPackage->client_id!=$this->obToken->getUserId()) {
			if(!$this->obToken->hasRole('SuperAdmin')) { //Не суперадмин
				if(!$this->obToken->hasRole('Manager')) {  //Не менеджер
					if($this->obToken->hasRole('BasePartner')) { // Партнёр?
						if($this->obPackage->client->owner_partner) { //А у пользователья есть партнёр владелец?
							if($this->obPackage->client->owner_partner->id_partner!=$this->obToken->getUserId()) { //Владелец не я
								throw new ApiException(3,'no access'); // Значит лесом
							}
						} else { //Нет владельца
							throw new ApiException(3,'no access'); //Тоже лесом
						}
					} else { //Не партнёр и не манагер, значит лесом
						throw new ApiException(3,'no access');
					}
				} else { //Манагер
					if($this->obUser->pgroup_id!=7) { //Ух ты пользователь то не клиент
						throw new ApiException(3,'no access');
					}
				}
			}
		}
	}

	/**
	 * Метод обеспечивает выполнение операцииы
	 */
	function run() {
		//Обработаем родительский вызов
		$this->_checkProtocolRequirements();

		if(!isset($_GET['packageID']))
			throw new CHttpException(400,'Bad request',400);

		$this->checkAccess();

		$this->_prepareAndCheckPackage();

		$arResult=$this->obPackage->getPackageInfo();
		$obWorkflow=$this->obPackage->initWorkflow();
		$arResult=array(
			'result'=>200,
			'resultText'=>'ok',
			'data'=>array(
				'package'=>$arResult
			)
		);
		//Если спросили мастера
		if(isset($_GET['getWizzardData']) && $_GET['getWizzardData']==1) {
			//$obWorkflow=$obPackage->workflow;
			$arWizzard=array(
				'step_id'=>$obWorkflow->step_id,
				'text_ident'=>$obWorkflow->step->text_ident,
				'active_menu_id'=>$obWorkflow->step->wizzard_menu_id,
			);
			$arMenus=PackageWizzardMenu::model()->menu()->findAll();
			foreach($arMenus as $obMenu) {
				$arWizzard['wizzard_menu'][]=array(
					'id'=>$obMenu->id,
					'order'=>$obMenu->order,
					'title'=>$obMenu->title,
					'visible'=>$obMenu->visible,
					'text_ident'=>$obMenu->text_ident,
					'number'=>$obMenu->number
				);
			}
			if($arSteps=$obWorkflow->step->steps)
				foreach($arSteps as $obNextStep) {
					if($obNextStep->to_step_id>$arWizzard['step_id'])
						$arWizzard['next_steps'][]=$obNextStep->to_step_id;
					else
						$arWizzard['previous_steps'][]=$obNextStep->to_step_id;
				}
			$obWorkflow->update();
			$arResult['data']['wizzard']=$arWizzard;
		}
		$this->getController()->render('json',array('data'=>$arResult));
	}
}