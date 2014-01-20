<?php
/**
 * Класс родитель для всех действий в рамках контроллеров модуля API
 */
class ApiUserAction extends ApiAction {
	function checkAccess($params=array()) {
		$obModule=Yii::app()->getModule('api');
		$obToken=$obModule->getApplicationTokens();
		if(!$obToken->getIsStarted())
			throw new CHttpException(401,'Application auth required',401);
		if($obToken->getUserId()==0)
			throw new CHttpException(403,'Auth required',403);
		if(!$obToken->checkUserAccess($this->getId(),$params))
			throw new CHttpException(402,'Access denied '.$this->getId(),402);
	}
}
