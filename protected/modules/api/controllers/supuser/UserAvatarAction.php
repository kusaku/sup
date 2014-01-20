<?php
/**
 * Класс выполняет обработку функции AddService
 */
class UserAvatarAction extends ApiUserAction implements IApiGetAction {
	/**
	 * Метод обеспечивает выполнение действие
	 */ 
	public function run() {
		$this->_checkProtocolRequirements();
		if(!isset($_GET['id'])) {
			throw new CHttpException(400,'Bad request',400);
		}
		$this->checkAccess();
		
		$obUser=People::model()->findByPk(intval($_GET['id']));
		if(!$obUser)
			throw new ApiException(1,'user not found');
		$arData=$obUser->getAvatar();
		if($arData['avatar']=='')
			throw new ApiException(2,'no avatar');
		
		if(isset($_GET['hash']) && $_GET['hash']==$arData['avatar_hash']) {
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'data'=>array(
					'hash'=>$arData['avatar_hash']
				)
			);
		} else {
			$sFile=base64_encode(file_get_contents(Yii::getPathOfAlias('webroot').$arData['avatar']));
			$sExtension=pathinfo(Yii::getPathOfAlias('webroot').$arData['avatar'],PATHINFO_EXTENSION);
			$arResult=array(
				'result'=>200,
				'resultText'=>'ok',
				'data'=>array(
					'hash'=>$arData['avatar_hash'],
					'file'=>$sFile,
					'ext'=>$sExtension,
					'path'=>$arData['avatar']
				)
			);
		}
		$this->getController()->render('json',array('data'=>$arResult));
	}
}
