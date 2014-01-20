<?php
/**
 * Класс родитель для всех действий в рамках контроллеров модуля API
 * требующих проверку доступа вида "приложение" 
 */
class ApiApplicationAction extends ApiAction {
	public function checkAccess($params=array()) {
		if(!Yii::app()->getModule('api')->getApplicationUser()->checkAccess($this->getId(),$params))
			throw new CHttpException(401,'Application auth required',401);
	}
}
