<?php
/**
 * Класс выполняет обработку функции GetPackages
 */
class GetPackagesAction extends ApiUserAction implements IApiGetAction {
	/**
	 * Алгоритм получения списка пакетов для протокола
	 */
	function run03() {
		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if($obToken->getUserId()==0)
			throw new CHttpException(403,'Auth required',403);

		$arResult=array();
		//Получаем модель
		$obPackageModel=Package::model();
		//подготовливаем условия выборки
		$obCriteria=new CDbCriteria();
		$obCriteria->select=array('id','name','site_id','status_id','payment_id','summ','client_id');
		$obCriteria->condition="client_id=".$obToken->getUserId();

		$arFilter=array(
			'type'=>'my',
			'status'=>'all'
		);
		if(isset($_REQUEST['filter'])) {
			if($_REQUEST['filter']=='all') {
				$arFilter['type']='all';
			} elseif(is_array($_REQUEST['filter'])) {
				if(isset($_REQUEST['filter']['type']) && $_REQUEST['filter']['type']=='all')
					$arFilter['type']='all';
				if(isset($_REQUEST['filter']['status']) && in_array($_REQUEST['filter']['status'],array('all','active','done')))
					$arFilter['status']=$_REQUEST['filter']['status'];
			}
		}
		if($arFilter['type']=='all') {
			//Если надо выбрать все заказы
			if(!$obToken->hasRole('SuperAdmin')) {
				if(!$obToken->hasRole('Manager')) {
					if($obToken->hasRole('BasePartner')) {
						//Если есть только партнёрские права, ищем всех пользователей связанных со мной, при этом
						//ищем их заказы
						$obCriteria->join='INNER JOIN partner_people ON partner_people.id_client=client_id';
						$obCriteria->condition="partner_people.id_partner=".$obToken->getUserId();
					} elseif($obToken->hasRole('Client')) {
						$obCriteria->condition="client_id=".$obToken->getUserId();
					} else {
						throw new CHttpException(403,'Auth required',403);
					}
				} else {
					$obCriteria->join='INNER JOIN people_to_manager.user_id=client_id ';
					$obCriteria->condition="people_to_manager.manager_id=".$obToken->getUserId();
				}
			} else {
				throw new CHttpException(503,'Not implemented',503);
			}
		}
		if($arFilter['status']=='active') {
			//Если надо выбрать только активные заказы
			$obCriteria->condition.=' AND (status_id<70 OR status_id!=17)';
		} elseif($arFilter['status']=='done') {
			$obCriteria->condition.=' AND (status_id>=70 OR status_id=17)';
		}

		if(!isset($_GET['justCount']) || $_GET['justCount']==0) {
			//сортировка
			$sSortBy='id';
			if(isset($_GET['sortBy']) && in_array($_GET['sortBy'],$obCriteria->select))
				$sSortBy=$_GET['sortBy'];
			$sSortDir='desc';
			if(isset($_GET['sortDir']) && $_GET['sortDir']=='asc')
				$sSortDir='asc';
			$obCriteria->order="$sSortBy $sSortDir";
			//Пределы
			if(isset($_GET['limit']) && intval($_GET['limit'])>0)
				$obCriteria->limit=intval($_GET['limit']);
			if(isset($_GET['offset']) && intval($_GET['offset'])>0)
				$obCriteria->offset=intval($_GET['offset']);
			$arPackages=$obPackageModel->findAll($obCriteria);
			$arResult['list']=array();
			foreach($arPackages as $obPackage) {
				//TODO Потом убрать!!!
				$obProduct=$obPackage->GetProduct();
				if($obProduct && !in_array($obProduct->id,array(4,5,6,33,126,144,148,149,150)))
					continue;
				elseif(!$obToken->hasRole('BasePartner') && !$obProduct && count($obPackage->services)>0)
					continue;
				$arRow=array(
					'id'=>$obPackage->id,
					'icon'=>$obPackage->getIcon(),
					'summ'=>$obPackage->summ,
					'status'=>array(
						'id'=>0,'title'=>'','text_ident'=>''
					),
					'payment'=>array(
						'id'=>0,'title'=>'','text_ident'=>''
					),
					'client'=>array(
						'id'=>$obPackage->client_id,
						'fio'=>$obPackage->client->fio,
						'mail'=>$obPackage->client->mail
					)
				);
				if($obProduct)
					if(count($obProduct->descriptions)>0)
						$arRow['title']=$obProduct->descriptions[0]->title; //TODO Предусмотреть название по умолчанию
					else
						$arRow['title']=$obProduct->name;
				else
					$arRow['title']=$obPackage->name;
				if($obPackage->site_id>0) {
					$obSite=$obPackage->site;
					$arRow['site']=array(
						'id'=>$obSite->id,
						'url'=>$obSite->url
					);
				}
				$obStatus=$obPackage->wf_status;
				if($obStatus)
					$arRow['status']=array(
						'id'=>$obStatus->id,
						'title'=>$obStatus->name,
						'text_ident'=>$obStatus->text_ident
					);
				$obPayment=$obPackage->pay_status;
				if($obPayment)
					$arRow['payment']=array(
						'id'=>$obPayment->id,
						'title'=>$obPayment->name,
						'text_ident'=>$obPayment->text_ident
					);
				$arResult['list'][]=$arRow;
			}
		} else {
			$iCount=$obPackageModel->count($obCriteria);
			$arResult['data']=$iCount;
		}
		$arResult['result']=200;
		$arResult['resultText']='ok';
		$this->getController()->render('json',array('data'=>$arResult));
	}

	function run() {
		$this->_checkProtocolRequirements();
		$this->checkAccess();

		//Если стоит версия протокола 0.3 работаем по новому методу
		if(isset($_REQUEST['version']) && $_REQUEST['version']>='0.3')
			return $this->run03();

		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if($obToken->getUserId()==0)
			throw new CHttpException(403,'Auth required',403);
		$arResult=array();
		//Получаем модель
		$obPackageModel=Package::model();
		//подготовливаем условия выборки
		$obCriteria=new CDbCriteria();
		$obCriteria->select=array('id','name','site_id','status_id','payment_id','summ','client_id');
		$obCriteria->condition="client_id=".$obToken->getUserId();
		if(!isset($_GET['justCount']) || $_GET['justCount']==0) {
			//сортировка
			$sSortBy='id';
			if(isset($_GET['sortBy']) && in_array($_GET['sortBy'],$obCriteria->select))
				$sSortBy=$_GET['sortBy'];
			$sSortDir='desc';
			if(isset($_GET['sortDir']) && $_GET['sortDir']=='asc')
				$sSortDir='asc';
			$obCriteria->order="$sSortBy $sSortDir";
			//Пределы
			if(isset($_GET['limit']) && intval($_GET['limit'])>0)
				$obCriteria->limit=intval($_GET['limit']);
			if(isset($_GET['offset']) && intval($_GET['offset'])>0)
				$obCriteria->offset=intval($_GET['offset']);
			$arPackages=$obPackageModel->findAll($obCriteria);
			$arResult['list']=array();
			foreach($arPackages as $obPackage) {
				//TODO Потом убрать!!!
				$obProduct=$obPackage->GetProduct();
				if($obProduct && !in_array($obProduct->id,array(4,5,6,33,126,144)))
					continue;
				elseif(!$obProduct && count($obPackage->services)>0)
					continue;
				$arRow=array(
					'id'=>$obPackage->id,
					'icon'=>$obPackage->getIcon(),
					'summ'=>$obPackage->summ,
					'status'=>array(
						'id'=>0,'title'=>'','text_ident'=>''
					),
					'payment'=>array(
						'id'=>0,'title'=>'','text_ident'=>''
					),
				);
				if($obProduct)
					if(count($obProduct->descriptions)>0)
						$arRow['title']=$obProduct->descriptions[0]->title; //TODO Предусмотреть название по умолчанию
					else
						$arRow['title']=$obProduct->name;
				else
					$arRow['title']=$obPackage->name;
				if($obPackage->site_id>0) {
					$obSite=$obPackage->site;
					$arRow['site']=array(
						'id'=>$obSite->id,
						'url'=>$obSite->url
					);
				}
				$obStatus=$obPackage->wf_status;
				if($obStatus)
					$arRow['status']=array(
						'id'=>$obStatus->id,
						'title'=>$obStatus->name,
						'text_ident'=>$obStatus->text_ident
					);
				$obPayment=$obPackage->pay_status;
				if($obPayment)
					$arRow['payment']=array(
						'id'=>$obPayment->id,
						'title'=>$obPayment->name,
						'text_ident'=>$obPayment->text_ident
					);
				$arResult['list'][]=$arRow;
			}
		} else {
			$iCount=$obPackageModel->count($obCriteria);
			$arResult['data']=$iCount;
		}
		$arResult['result']=200;
		$arResult['resultText']='ok';
		$this->getController()->render('json',array('data'=>$arResult));
	}
}