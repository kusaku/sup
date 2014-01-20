<?php
/**
 * Указанный виджет отображает текущую информацию связанную с пользователем
 * @property $arLeafs ServiceTree[]
 */
class ServiceTreeBranchWidget extends CWidget {
	public $arLeafs;
	public $depth;

	public function init() {
	}

	public function run() {
		if(is_array($this->arLeafs) && count($this->arLeafs)>0) {
			$arResult=array();
			$arResult['branch']=$this->arLeafs;
			$arResult['depth']=$this->depth;
			$this->render('ServiceTreeBranch',$arResult);
		}
	}
}
