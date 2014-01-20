<?php
/**
 * Указанный виджет отображает текущую информацию связанную с пользователем
 */

class PackageStatusInfoWidget extends CWidget {
	public $package;
	public $client;
	
	public function init() {
    }
 
    public function run() {
    	$this->render('PackageStatusInfo',array('package'=>$this->package,'client'=>$this->client));
    }
}
