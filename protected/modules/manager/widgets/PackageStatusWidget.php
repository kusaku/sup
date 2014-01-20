<?php
/**
 * Указанный виджет отображает текущую информацию связанную с пользователем
 */

class PackageStatusWidget extends CWidget {
	public $package;
	public $client;
	//private $bCanChange=true;
	
	public function init() {
     	//if(Yii::app()->user->checkAccess('admin')) 
			//$this->group_id=0;   
    }
 
    public function run() {
    	if($arStatuses=PackageStatus::model()->findAll(array('order'=>'id ASC'))) {
    		$arResult=array();
    		foreach($arStatuses as $obStatus) {
    			$arRow=array(
    				'id'=>$obStatus->primaryKey,
    				'name'=>$obStatus->name,
    				'selected'=>$obStatus->primaryKey==$this->package->status_id,
    				'disabled'=>false
				);
				if($arRow['id']!=15) {
					if($arRow['id']<$this->package->status_id) {
						$arRow['disabled']=true;
					} 
					if($arRow['id']!=30 && $this->package->status_id==30 && $this->package->payment_id<20) {
						$arRow['disabled']=true;
					}
				}
				$arResult[]=$arRow;
    		}
        	$this->render('PackageStatus',array('arStatuses'=>$arResult));
		}
    }
}
