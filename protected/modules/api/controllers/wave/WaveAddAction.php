<?php
/**
 * Класс выполняет обработку функции WaveAdd
 */
class WaveAddAction extends ApiUserWaveAction implements IApiPostAction {
	public function run() {
		$this->_checkProtocolRequirements();

		if(!isset($_REQUEST['key']) || !isset($_REQUEST['content'])) {
			throw new CHttpException(400,'Bad request',400);
		}
		$this->checkAccess();

		$obToken=$this->getController()->getModule()->getApplicationTokens();
		if(!$this->checkAccessByKey($_REQUEST['key']))
			throw new CHttpException(403,'Auth required',403);

		$obTransaction=Waves::model()->dbConnection->beginTransaction();
		try {
			//Определяем обсуждение или создаём новое связанное с ключём
			$obWave=Waves::model()->findByAttributes(array('text_ident'=>$_REQUEST['key']));
			if(!$obWave) {
				$obWave=new Waves();
				$obWave->text_ident=$_REQUEST['key'];
				$obWave->author_id=$obToken->getUserId();
			}
			$iPostId=$obWave->addPost($obToken->getUserId(), $_REQUEST['content']);
			$obPost=WavePosts::model()->findByPk($iPostId);
			//Уведомляем менеджера
			if($obPackage=$this->getPackageByKey($obWave->text_ident)) {
				if($obPackage->manager_id!=$obPost->author_id) {
					$obNotify=new ManagerNotifier();
					$obNotify->log='[auto] Пользователь оставил комментарий [Order ID #'.$obPackage->getNumber().']';
					$obNotify->calendar='[auto] Пользователь '.$obPost->author->mail.' ['.$obPost->author->id.'] добавил комментарий к заказу №'
						.$obPackage->getNumber().' на странице: <b>'.$obPackage->workflow->step->title
						.'</b>. <a href="/manager#cabinet_'.$obPackage->id.'_wm_step_'.$obPackage->workflow->step->primaryKey.'" class="eventReadyButton">Посмотреть и ответить</a>';
					$obNotify->mail='[auto] Пользователь '.$obPost->author->mail.' ['.$obPost->author->id.'] добавил комментарий к заказу №'
						.$obPackage->getNumber().' на странице: <b>'.$obPackage->workflow->step->title
						."</b>.<br/>\nЕго сообщение:<br/>\n"
						.htmlspecialchars($obPost->message->content,ENT_COMPAT,'utf-8')."<br/>\n<br/>\n"
						.'<a href="https://sup.fabricasaitov.ru/manager#cabinet_'.$obPackage->id.'_step_'.$obPackage->workflow->step->primaryKey
						.'">Посмотреть и ответить</a> (https://sup.fabricasaitov.ru/manager#cabinet_'.$obPackage->id.'_step_'.$obPackage->workflow->step->primaryKey.')';
					$obNotify->manager_id=$obPackage->manager_id;
					$obNotify->client_id=$obPackage->client_id;
					if(!$obNotify->Send()) {
						throw new Exception('Error on send notify');
					}
				}
			}

			if($obTransaction->active) $obTransaction->commit();
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'data'=>$obPost->getAsArray()
			);
			$this->getController()->render('json',array('data'=>$arResult));
		} catch(exception $e) {
			if($obTransaction->active) $obTransaction->rollBack();
			throw new ApiException(1,$e->getMessage());//'Package update error');
		}
	}
}
