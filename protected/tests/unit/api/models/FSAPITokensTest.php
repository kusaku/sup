<?php

class FSAPITokensTest extends CDbTestCase {
	public $fixtures=array(
		'applications'=>':api_tokens',
	);

	protected function setUp() {
		Yii::app()->getModule('api');
	}
}