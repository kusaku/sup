<?php 
class DashboardController extends Controller {

	/**
	 * Использовать фильтр прав доступа
	 * @return array
	 */
	public function filters() {
		return array(
			'accessControl'
		);
	}
	
	/**
	 * Параметры фильтра прав доступа
	 * @return array
	 */

	public function accessRules() {
		// доступные роли:
		// list('guest', 'admin', 'moder', 'topmanager', 'manager', 'master', 'partner', 'client', 'leadmaster', 'remotemaster', 'superpartner', 'marketolog');
		return array(
			array(
				'allow','actions'=>array(
					'index','details','index2'
				),'roles'=>array(
					'admin','moder','topmanager'
				),
			),array(
				'deny','users'=>array(
					'*'
				),
			),
		);
	}

	public function actionIndex() {
		setlocale(LC_TIME,'ru');
		$obReport=new DashboardReport();
		$this->renderPartial('index', $obReport->withNewOrders()->withPaid()->withPaying()->getResult());
	}

	public function actionDetails() {
		$request = Yii::app()->getRequest();
		$people_id = $request->getParam('people_id') ? (int) $request->getParam('people_id') : 0;
		$date = $request->getParam('till') ? (string) $request->getParam('till') : date('Y-m-d');
		$period = $request->getParam('period') ? (string) $request->getParam('period') : 'day';
		$timestamp = strtotime($date);
		switch ($period) {
			case 'year':
				$dt_beg = date('Y-01-01 00:00:00', $timestamp);
				break;
			case 'month':
				$dt_beg = date('Y-m-01 00:00:00', $timestamp);
				break;
			case 'week':
				$dt_beg = date('Y-m-d 00:00:00', strtotime('previous mon', $timestamp));
				break;
			case 'day':
				$dt_beg = date('Y-m-d 00:00:00', $timestamp);
				break;
			default:
				$dt_beg = date('Y-m-d 00:00:00', strtotime($period));
				break;
		}
		
		// гранулярность - сутки, верхний порог - строго меньше, поэтому прибавляем 86400 секунд
		$dt_end = date('Y-m-d H:i:s', $timestamp + 86400);
		
		$arResult = array(
			'people_id'=>$people_id,'date'=>$date,'period'=>$period,
		);
		$managers = array(
		);
		
		// выбираем менеджеров
		if ($people_id > 0) {
			$manager = People::model()->findByPk($people_id);
			$managers[$manager->primaryKey] = array(
				'new'=>0,'ongo'=>0,'bill'=>0,'paid'=>0,'newSum'=>0.0,'ongoSum'=>0.0,'billSumm'=>0.0,
					'paidSumm'=>0.0,'totalSum'=>0.0,'fio'=>$manager->fio
			);
		} else {
			foreach (PeopleGroup::model()->findByPk(4)->peoples as $manager) {
				$managers[$manager->primaryKey] = array(
					'new'=>0,'ongo'=>0,'bill'=>0,'paid'=>0,'newSum'=>0.0,'ongoSum'=>0.0,'billSumm'=>0.0,
						'paidSumm'=>0.0,'totalSum'=>0.0,'fio'=>$manager->fio
				);
			}
			
		}
		
		$arManagerIds = array_keys($managers);
		$sManagersWhere = join(',', $arManagerIds);
		$sBaseQuery = 'SELECT C.id, C.name, C.summ, B.serv_id, A.status_id, A.payment_id, A.date, A.manager_id FROM `package_status_log` as A INNER JOIN `serv2pack` as B on A.package_id=B.pack_id INNER JOIN `package` as C on A.package_id=C.id ';
		$sOtherQuery = 'SELECT A.package_id FROM `package_status_log` as A INNER JOIN `serv2pack` as B ON A.package_id=B.pack_id INNER JOIN `package` as C ON C.id=A.package_id WHERE B.serv_id IN (4,5,6,33,126) ';
		$typesMap = array(
			'site3999'=>'B.serv_id IN (4,126)','site9999'=>'B.serv_id=5','site15999'=>'B.serv_id=6',
				'site18999'=>'B.serv_id=33','siteSmallBiz'=>'B.serv_id=126'
		);
		$case_rule_map = $this->getCaseMap($dt_beg, $dt_end, $sOtherQuery, $sManagersWhere);
		$obDB = Yii::app()->getDb();
		$arResult['rows'] = array(
		);
		if (isset($case_rule_map[$request->getParam('mode')])) {
			if (isset($typesMap[$request->getParam('type')])) {
				$sSql = $sBaseQuery.str_replace('#SERV_ID#', $typesMap[$request->getParam('type')], $case_rule_map[$request->getParam('mode')]['query']);
				$obQuery = $obDB->createCommand($sSql);
				$arResult['rows'] = $obQuery->queryAll();
			} elseif ($request->getParam('type') == 'siteOther') {
				$obQuery = $obDB->createCommand($case_rule_map[$request->getParam('mode')]['otherQuery']);
				$obRes = $obQuery->query();
				$arIds = array(
				);
				foreach ($obRes as $arRow) {
					$arIds[] = $arRow['package_id'];
				}
				if (count($arIds) > 0) {
					$sSql = $sBaseQuery.str_replace('#SERV_ID#', 'C.id NOT IN ('.join(',', $arIds).')', $case_rule_map[$request->getParam('mode')]['query']);
				} else {
					$sSql = $sBaseQuery.str_replace('#SERV_ID#', ' 1=1 ', $case_rule_map[$request->getParam('mode')]['query']);
				}
				$obQuery = $obDB->createCommand($sSql);
				$arResult['rows'] = $obQuery->queryAll();
			} elseif ($request->getParam('type') == 'total') {
				$sSql = $sBaseQuery.$case_rule_map[$request->getParam('mode')]['totalQuery'];
				$obQuery = $obDB->createCommand($sSql);
				$arResult['rows'] = $obQuery->queryAll();
			}
			usort($arResult['rows'], array(
				$this,'sortPackages'
			));
		}
		$this->renderPartial('packages', $arResult);
	}

	public function sortPackages($a, $b) {
		return $a['date'] > $b['date'];
	}
}
