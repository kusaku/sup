<?php
/**
 * Класс добавляет поддержку метода проверки каким группам соответствует пользователь
 * @property People $obUser
 * @property ApiTokens $obToken
 */
class ApiUserActionDiffaccess extends ApiUserAction {
	protected $obToken;
	protected $obUser;
	/**
	 * Метод обеспечивает проверку прав доступа для текущего и обрабатываемого пользователя
	 */
	protected function prepareAndCheckUser($id=false) {
		$this->obToken=$this->getController()->getModule()->getApplicationTokens();
		if($this->obToken->getUserId()==0)
			throw new CHttpException(403,'Auth required',403);
		$iUserId=$this->obToken->getUserId();
		if($id)
			$iUserId=intval($id);
		$this->obUser=People::model()->findByPk($iUserId);
		if(!$this->obUser)
			throw new ApiException(1,'Get user error');
		//Надо убедиться, что есть права
		//При вненсении изменений, учитываем иерархию прав в БД
		switch (true) {
			case $iUserId==$this->obToken->getUserId():	//Если хотим себя, то все ок
				break;

			case $this->obToken->hasRole('SuperAdmin'): //Админы - наше всё!
				break;

			case $this->obToken->hasRole('Manager'):
				if($this->obUser->pgroup_id==7) { //Если пользователь - клиент, то все ок
					break;
				}

			case $this->obToken->hasRole('PartnerManager'):
				if($this->obUser->pgroup_id==6) { //Если пользователь - партнер, то все ок
					break;
				}

			case $this->obToken->hasRole('BasePartner'): //Партнеры тоже что-то могут.
				if($this->obUser->owner_partner) { //А у пользователья есть партнёр владелец?
					if($this->obUser->owner_partner->id_partner==$this->obToken->getUserId()) { //Владелец я - все ок
						break;
					}
				}

			default: //По умолчанию - все идут лесом. (:
				throw new ApiException(2,'Not alowed');
		}
	}
}
