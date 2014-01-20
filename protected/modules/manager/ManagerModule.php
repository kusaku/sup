<?php
/**
 * 
 */ 
class ManagerModule extends CWebModule {

	public $defaultController = 'default';
	
	/**
	 * Initializes the module.
	 */
	public function init() {
		// import the module-level models and components
		$this->setImport(array(
			// models
			$this->getId().'.models.*',
			// components
			$this->getId().'.components.*', )
		);
	}
	
	/**
	 * The pre-filter for controller actions.
	 * This method is invoked before the currently requested controller action and all its filters
	 * are executed. You may override this method in the following way:
	 * <pre>
	 * if(parent::beforeControllerAction($controller,$action))
	 * {
	 *     // your code
	 *     return true;
	 * }
	 * else
	 *     return false;
	 * </pre>
	 * @param CController $controller the controller
	 * @param CAction $action the action
	 * @return boolean whether the action should be executed.
	 * @since 1.0.4
	 */
	public function beforeControllerAction($controller, $action) {
		if (($parent = $this->getParentModule()) === null)
			$parent = Yii::app();
		return $parent->beforeControllerAction($controller, $action);
	}
	
	/**
	 * The post-filter for controller actions.
	 * This method is invoked after the currently requested controller action and all its filters
	 * are executed. If you override this method, make sure you call the parent implementation at the end.
	 * @param CController $controller the controller
	 * @param CAction $action the action
	 * @since 1.0.4
	 */
	public function afterControllerAction($controller, $action) {
		if (($parent = $this->getParentModule()) === null)
			$parent = Yii::app();
		$parent->afterControllerAction($controller, $action);
	}
}
