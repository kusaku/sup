<?php
class ReportController extends Controller {
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
				'allow', 'roles'=>array('admin')
			),
			array(
				'deny', 'users'=>array('*')
			)
		);
	}

	public function actionFinance() {
		$query="SELECT mail, fio FROM (
					(SELECT mail, fio FROM
						`people` INNER JOIN
						`package` ON `people`.`id`=`package`.`client_id` INNER JOIN
						`payment` ON `payment`.`package_id`=`package`.`id`
					GROUP BY mail) UNION
					(SELECT mail, fio FROM
						`people` INNER JOIN
						`package` ON `people`.`id`=`package`.`client_id`
						WHERE
							(`package`.`payment_id`>17 AND `package`.`status_id`>=30) OR
							(`package`.`status_id`>17 AND `package`.`payment_id`=0)
					GROUP BY mail)
				) AS A GROUP BY mail";
		$obCommand=Yii::app()->db->createCommand($query);
		$obResult=$obCommand->query();
		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Cache-Control: public, must-revalidate, max-age=0');
		header('Pragma: public');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Content-Type: text/plain');
		header('Content-disposition: attachment; filename="finance_data_'.date('d.m.Y H_i_s').'.csv"');
		$h=fopen('php://output','w');
		fwrite($h,chr(239).chr(187).chr(191));
		foreach($obResult as $arItem) {
			$arItem['mail']=str_replace('"',"'",trim($arItem['mail']));
			$arItem['fio']=str_replace('"',"'",trim($arItem['fio']));
			$sString='"'.$arItem['mail'].'";"'.$arItem['fio'].'"'."\n";
			fputs($h,$sString);
			//fputcsv($h,$arItem,';');
		}
		fclose($h);
	}

	public function actionActive() {
		$arFinance=array();
		$query="SELECT mail FROM (
					(SELECT mail FROM
						`people` INNER JOIN
						`package` ON `people`.`id`=`package`.`client_id` INNER JOIN
						`payment` ON `payment`.`package_id`=`package`.`id`
					GROUP BY mail) UNION
					(SELECT mail FROM
						`people` INNER JOIN
						`package` ON `people`.`id`=`package`.`client_id`
						WHERE
							(`package`.`payment_id`>17 AND `package`.`status_id`>=30) OR
							(`package`.`status_id`>17 AND `package`.`payment_id`=0)
					GROUP BY mail)
				) AS A GROUP BY mail";
		$obCommand=Yii::app()->db->createCommand($query);
		$obResult=$obCommand->query();
		foreach($obResult as $arItem) {
			$arFinance[trim($arItem['mail'])]=1;
		}
		$query="SELECT mail, fio FROM
					`people` INNER JOIN
					`package` ON `people`.`id`=`package`.`client_id`
				WHERE
					`package`.`status_id`>15
				GROUP BY mail";
		$obCommand=Yii::app()->db->createCommand($query);
		$obResult=$obCommand->query();
		header('Content-Description: File Transfer');
		header('Content-Transfer-Encoding: binary');
		header('Cache-Control: public, must-revalidate, max-age=0');
		header('Pragma: public');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
		header('Content-Type: text/plain');
		header('Content-disposition: attachment; filename="active_data_'.date('d.m.Y H_i_s').'.csv"');
		$h=fopen('php://output','w');
		fwrite($h,chr(239).chr(187).chr(191));
		foreach($obResult as $arItem) {
			if(!isset($arFinance[trim($arItem['mail'])])) {
				$arItem['mail']=str_replace('"',"'",trim($arItem['mail']));
				$arItem['fio']=str_replace('"',"'",trim($arItem['fio']));
				$sString='"'.$arItem['mail'].'";"'.$arItem['fio'].'"'."\n";
				fputs($h,$sString);
				//fputcsv($h,$arItem,';');
			}
		}
		fclose($h);
	}

	public function actionAdvert() {
		$obForm=new AdvertReportForm();
		if(Yii::app()->request->isPostRequest && isset($_POST['AdvertReportForm'])) {
			$obForm->attributes=$_POST['AdvertReportForm'];
			if($obForm->validate()) {
				$dt_from=date('Y-m-d H:i:s',strtotime($obForm->dt_from));
				$dt_to=date('Y-m-d H:i:s',strtotime($obForm->dt_to));
				$query="SELECT mail, fio, package_id FROM (
						(SELECT mail, fio, `package`.`id` AS package_id FROM
							`people` INNER JOIN
							`package` ON `people`.`id`=`package`.`client_id` INNER JOIN
							`payment` ON `payment`.`package_id`=`package`.`id` INNER JOIN
							`serv2pack` ON `package`.`id`=`serv2pack`.`pack_id`
						WHERE
							`serv2pack`.`serv_id` IN (130,131,134) AND
							`package`.`dt_beg`>'{$dt_from}' AND `package`.`dt_beg`<'{$dt_to}'
						GROUP BY mail) UNION
						(SELECT mail, fio, `package`.`id` AS package_id FROM
							`people` INNER JOIN
							`package` ON `people`.`id`=`package`.`client_id` INNER JOIN
							`serv2pack` ON `package`.`id`=`serv2pack`.`pack_id`
							WHERE
								((`package`.`payment_id`>17 AND `package`.`status_id`>=30) OR
								(`package`.`status_id`>17 AND `package`.`payment_id`=0)) AND
								`serv2pack`.`serv_id` IN (130,131,134) AND
								`package`.`dt_beg`>'{$dt_from}' AND `package`.`dt_beg`<'{$dt_to}'
						GROUP BY mail)
					) AS A GROUP BY mail";
				$obCommand=Yii::app()->db->createCommand($query);
				$obResult=$obCommand->query();
				header('Content-Description: File Transfer');
				header('Content-Transfer-Encoding: binary');
				header('Cache-Control: public, must-revalidate, max-age=0');
				header('Pragma: public');
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
				header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
				header('Content-Type: text/plain');
				header('Content-disposition: attachment; filename="advert_data_'.date('d.m.Y H_i_s').'.csv"');
				$h=fopen('php://output','w');
				fwrite($h,chr(239).chr(187).chr(191));
				foreach($obResult as $arItem) {
					$arItem['mail']=str_replace('"',"'",trim($arItem['mail']));
					$arItem['fio']=str_replace('"',"'",trim($arItem['fio']));
					$sString='"'.$arItem['mail'].'";"'.$arItem['fio'].'";"'.$arItem['package_id'].'"'."\n";
					fputs($h,$sString);
					//fputcsv($h,$arItem,';');
				}
				fclose($h);
				die();
			}
		}
		$this->render('advert',array('model'=>$obForm));
	}
}
