<?php

class FSAPIApplicationsTest extends CDbTestCase {
	public $fixtures=array(
		'applications'=>':api_applications',
	);

	protected function setUp() {
		Yii::app()->getModule('api');
	}

	function testCheckKey() {
		/**
		 * @var FSAPIApplications $obApplication
		 */
		$obApplication=FSAPIApplications::model()->findByPk(1);
		$this->assertTrue($obApplication->checkKey('123456'));
		$this->assertFalse($obApplication->checkKey('235342'));
	}
}