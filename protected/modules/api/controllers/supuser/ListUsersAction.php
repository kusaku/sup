<?php
/**
 * Класс выполняет обработку функции AddService
 */
class ListUsersAction extends ApiUserAction implements IApiGetAction {
	function run() {
		$this->_checkProtocolRequirements();

		$this->checkAccess();

		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if($obToken->getUserId()==0)
			throw new CHttpException(403,'Auth required',403);

		//Получаем модель
		$obUserModel=People::model();
		//подготовливаем условия выборки
		$obCriteria=new CDbCriteria();
		$obCriteria->select=array('id','mail','fio','state','phone','descr','regdate');
		if(!$obToken->hasRole('SuperAdmin')) {
			if(!$obToken->hasRole('Manager')) {
				if($obToken->hasRole('BasePartner')) {
					//Если есть только партнёрские права, ищем всех пользователей связанных со мной
					$obCriteria->join='INNER JOIN partner_people ON partner_people.id_client=id';
					$obCriteria->condition="partner_people.id_partner=".$obToken->getUserId();
				} elseif($obToken->hasRole('Client')) {
					$obCriteria->condition="id=".$obToken->getUserId();
				} else {
					throw new CHttpException(403,'Auth required',403);
				}
			} else {
				$obCriteria->join='INNER JOIN people_to_manager.user_id=id ';
				$obCriteria->condition="people_to_manager.manager_id=".$obToken->getUserId();
			}
		} else {
			throw new CHttpException(503,'Not implemented',503);
		}
		//$obCriteria->join.=' LEFT JOIN people_to_manager ON people_to_manager.user_id=id';

		$sSortBy='id';
		$sSortDir='desc';
		$obCriteria->order="$sSortBy $sSortDir";
		$obCriteria->limit=100; ///@TODO Добавить поддержку смещения
		$arUsers=$obUserModel->findAll($obCriteria);
		$arResult['list']=array();
		$arManagers=array();
		foreach($arUsers as $obUser) {
			$arRow=array(
				'mail'=>$obUser->mail,
				'login'=>$obUser->login,
				'id'=>$obUser->id,
				'fio'=>$obUser->fio,
				'state'=>$obUser->state,
				'phone'=>$obUser->phone,
				'firm'=>$obUser->firm,
				'descr'=>$obUser->descr,
				'regdate'=>$obUser->regdate,
			);
			if($obUser->manager) { //@TODO Добавить оптимизацию выборки
				if(!array_key_exists($obUser->manager->manager_id, $arManagers))
					$arManagers[$obUser->manager->manager_id]=People::model()->findByPk($obUser->manager->manager_id);
				$arRow['manager']=array(
					'name'=>$arManagers[$obUser->manager->manager_id]->fio,
					'chat_link'=>'',
					'id'=>$arManagers[$obUser->manager->manager_id]->id,
					'email'=>$arManagers[$obUser->manager->manager_id]->mail
				);
			}
			$arResult['list'][]=$arRow;
		}
		$arResult['result']=200;
		$arResult['resultText']='ok';
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
