<?php
class EmailController extends DocController {
	function actionRegister($user,$pwd) {
		$this->sOutput=$this->renderPartial('register',array('user'=>$user,'pwd'=>$pwd),true);
	}

	function actionSendpassword($user,$pwd) {
		$this->sOutput=$this->renderPartial('sendpassword',array('user'=>$user,'pwd'=>$pwd),true);
	}

	function actionNewpackage($user,$package) {
		$this->sOutput=$this->renderPartial('newpackage',array('user'=>$user,'package'=>$package),true);
	}

	function actionNewpackagemanager($user,$package) {
		$this->sOutput=$this->renderPartial('newpackagemanager',array('user'=>$user,'package'=>$package),true);
	}

	function actionNewpassword($model,$application,$user) {
		$this->sOutput=$this->renderPartial('newpassword',array('model'=>$model,'application'=>$application,'user'=>$user),true);
	}

	function actionPartnerpackage($package,$user) {
		$this->sOutput=$this->renderPartial('partnerpackage',array('package'=>$package,'user'=>$user),true);
	}

	function actionWavenewpost($user,$package) {
		$this->sOutput=$this->renderPartial('wavenewpost',array('package'=>$package,'user'=>$user),true);
	}

	function actionWavenewfile($user,$package) {
		$this->sOutput=$this->renderPartial('wavenewfile',array('package'=>$package,'user'=>$user),true);
	}

	function actionPackagepayed($user,$package) {
		$this->sOutput=$this->renderPartial('packagepayed',array('user'=>$user,'package'=>$package),true);
	}

	function actionPartnermanagerwithdraw($withdraw,$user) {
		$this->sOutput=$this->renderPartial('partnermanagerwithdraw',array('withdraw'=>$withdraw,'user'=>$user),true);
	}

	function actionPartnerwithdrawprocessed($withdraw,$user) {
		$this->sOutput=$this->renderPartial('partnerwithdrawprocessed',array('withdraw'=>$withdraw,'user'=>$user),true);
	}

	function actionPartnermanagerwithdrawprocessed($withdraw,$user) {
		$this->sOutput=$this->renderPartial('partnermanagerwithdrawprocessed',array('withdraw'=>$withdraw,'user'=>$user),true);
	}

	function actionPartnerManagerRegister($user,$manager) {
		$this->sOutput=$this->renderPartial('partnermanagerregister',array('user'=>$user,'manager'=>$manager),true);
	}

	function actionPartnerRegister($user,$pwd) {
		$this->sOutput=$this->renderPartial('partnerregister',array('user'=>$user,'pwd'=>$pwd),true);
	}
}
