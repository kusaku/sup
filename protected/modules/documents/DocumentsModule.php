<?php
/**
 * Класс реализует управление документами системы
 * @author Egor Bolgov <egor.b@fabricasaitov.ru>
 * @since 14.08.12
 */

class DocumentsModule extends CWebModule {
	public $defaultController = 'default';
	
	/**
	 * Initializes the module.
	 */
	public function init() {
		// import the module-level models and components
		$this->setImport(array(
			// components
			$this->getId().'.components.*',
		));
	}
}