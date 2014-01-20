<?php 
/**
 * Класс обеспечивает обработку всех входящих запросов и передачу операций соответствующим Action обработчикам
 * @author Egor Bolgov <egor.b@fabricasaitov.ru>
 * @since 12.05.12
 */
class DefaultController extends ApiController {
	/**
	 * Действие по умолчанию выполняемое для всех запросов
	 */
	public function actionIndex() {
		$obAPIRequestParser=new CFSApiRequestParser($this->getModule());
		$obAPIRequestParser->run();
	}
}
