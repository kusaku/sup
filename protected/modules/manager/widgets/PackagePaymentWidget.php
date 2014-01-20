<?php
/**
 * Указанный виджет отображает текущую информацию связанную с пользователем
 */

class PackagePaymentWidget extends CWidget {
	public $package;
	public $client;
	//private $bCanChange=true;
	
	public function init() {
     	//if(Yii::app()->user->checkAccess('admin')) 
			//$this->group_id=0;   
    }
 
    public function run() {
    	if($arPStatuses=PackagePayment::model()->findAll(array('order'=>'id ASC'))) {
    		$arResult=array();
    		foreach($arPStatuses as $obStatus) {
    			$arRow=array(
    				'id'=>$obStatus->primaryKey,
    				'name'=>$obStatus->name,
    				'selected'=>$obStatus->primaryKey==$this->package->payment_id,
    				'disabled'=>false
				);
				if($this->package->status_id<30) {
					$arRow['disabled']=true;
				} else {
					if($arRow['id']<$this->package->payment_id) {
						$arRow['disabled']=true;
					} 
				}
				$arResult[]=$arRow;
    		}
        	$this->render('PackagePayment',array('arStatuses'=>$arResult));
		}
    }
}
