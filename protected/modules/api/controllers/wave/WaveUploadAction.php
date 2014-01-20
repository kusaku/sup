<?php
/**
 * Класс выполняет обработку функции WaveUpload
 */
class WaveUploadAction extends ApiUserWaveAction implements IApiPostAction {
	public function run() {
		$this->_checkProtocolRequirements();
		
		if(!isset($_GET['key']) || !isset($_GET['postID']) || !isset($_GET['filename'])) {
			throw new CHttpException(400,'Bad request',400);
		}
		$this->checkAccess();
		
		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if(!$this->checkAccessByKey($_REQUEST['key']))
			throw new CHttpException(403,'Auth required',403);
		
		$obTransaction=Waves::model()->dbConnection->beginTransaction();
		try {
			//Определяем обсуждение или создаём новое связанное с ключём
			/**
			 * @var $obWave Waves
			 */
			$obWave=Waves::model()->findByAttributes(array('text_ident'=>$_GET['key']));
			if(!$obWave) {
				throw new ApiException(1,'Wave not found');
			}
			$obPost=$obWave->getPost($_GET['postID']);
			if(!$obPost) {
				throw new ApiException(2,'Post not found');
			}
			$fFile=fopen('php://input','r');
			try {
				$obAttachment=$obPost->uploadFileStream($_GET['filename'],$fFile);
			} catch(Exception $e) {
				fclose($fFile);
				throw $e;
			}
			fclose($fFile);
		 	if($obTransaction->active) $obTransaction->commit();
            
            //Уведомляем менеджера
            if($obPackage=$this->getPackageByKey($obWave->text_ident)) {
                if($obPackage->manager_id!=$obPost->author_id) {
                    $obNotify=new ManagerNotifier();
                    $obNotify->log='[auto] Пользователь загрузил файл [Order ID #'.$obPackage->getNumber().']';
                    $obNotify->calendar='[auto] Пользователь '.$obPost->author->mail.' ['.$obPost->author->id.'] загрузил файл к комментарию к заказу №'
                        .$obPackage->getNumber().' на странице: <b>'.$obPackage->workflow->step->title
                        .'</b>. <a href="/manager#cabinet_'.$obPackage->id.'_wm_step_'.$obPackage->workflow->step->primaryKey.'" class="eventReadyButton">Посмотреть</a>';
                    $obNotify->mail='[auto] Пользователь '.$obPost->author->mail.' ['.$obPost->author->id.'] загрузил файл к комментарию к заказу №'
                        .$obPackage->getNumber().' на странице: <b>'.$obPackage->workflow->step->title
                        ."</b>.<br/>\n Имя файла:<br/>\n"
                        .htmlspecialchars($obAttachment->title,ENT_COMPAT,'utf-8')."<br/>\n<br/>\n"
                        .'<a href="https://sup.fabricasaitov.ru/manager#cabinet_'.$obPackage->id.'_step_'.$obPackage->workflow->step->primaryKey
                        .'">Посмотреть и ответить</a> (https://sup.fabricasaitov.ru/manager#cabinet_'.$obPackage->id.'_step_'.$obPackage->workflow->step->primaryKey.')';
                    $obNotify->manager_id=$obPackage->manager_id;
                    $obNotify->client_id=$obPackage->client_id;
                    $obNotify->Send();
                }
            }
            
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'data'=>$obPost->getAsArray()
			);
			$this->getController()->render('json',array('data'=>$arResult));
		} catch(exception $e) {
			if($obTransaction->active) $obTransaction->rollBack();
			throw new ApiException(3,$e->getMessage());//'Package update error');
		}
	}
}
