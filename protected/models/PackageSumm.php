<?php
/**
 * @property Package $package
 */
class PackageSumm {
	private $package;
	private $arExcluded=array(133);
	private $bCounted=false;
	private $fClearSumm;
	private $fFullSumm;
	private $fExcluded;
	private $fPaid;

	public function __construct($package) {
		if(is_integer($package)) {
			$this->package=Package::model()->findByPk($package);
			if(is_null($this->package)) {
				throw new CException('Package not found');
			}
		} elseif($package instanceof Package) {
			$this->package=$package;
		} else {
			throw new CException('Package required');
		}
		$this->bCounted=false;
	}

	private function _recountSumm() {
		if($this->bCounted) return;
		$this->fClearSumm=0;
		$this->fFullSumm=0;
		$this->fExcluded=0;
		$arServices=$this->package->servPack;
		if(is_array($arServices)) {
			foreach($arServices as $obItem) {
				$this->fFullSumm+=$obItem->getSumm();
				if(in_array($obItem->serv_id,$this->arExcluded)) {
					$this->fExcluded+=$obItem->getSumm();
				} else {
					$this->fClearSumm+=$obItem->getSumm();
				}
			}
		}
		$this->bCounted=true;
	}

	private function _recountPaid() {

	}

	public function __toString() {
		$this->_recountSumm();
		return $this->fClearSumm;
	}

	public function getClear() {
		$this->_recountSumm();
		return $this->fClearSumm;
	}

	public function getExcluded() {
		$this->_recountSumm();
		return $this->fExcluded;
	}

	public function getFull() {
		$this->_recountSumm();
		return $this->fFullSumm;
	}

	public function getPaid() {

	}
}