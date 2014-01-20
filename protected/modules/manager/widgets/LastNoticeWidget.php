<?php
/**
 * Указанный виджет отображает текущую информацию связанную с пользователем
 */

class LastNoticeWidget extends CWidget {
	public function run() {
		$obPeople=People::getById(Yii::app()->user->id);
		if(is_null($obPeople)) {
			throw new CException('User not found');
		}
		$obCriteria=new CDbCriteria();
		$obCriteria->addCondition('people_id='.$obPeople->id);
		$obCriteria->addCondition("date<='".date('Y-m-d H:i:s')."'");
		$obCriteria->order='status DESC, date DESC';
		$obCriteria->limit=2;
		$arNotices=Calendar::model()->findAll($obCriteria);
		$sHash='';
		if(is_array($arNotices) && count($arNotices)>0) {
			foreach($arNotices as $obNotice) {
				$sHash.=$obNotice->id.'|';
			}
		}
		$obCriteria->addCondition('status=1');
		$iMode=0;
		if(Calendar::model()->count($obCriteria)) {
			$iMode=1;
		}
		$this->render('LastNotice',array('notices'=>$arNotices,'mode'=>$iMode,'listHash'=>$sHash));
	}
}