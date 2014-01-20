<?php
/**
 * Класс родитель для всех действий в рамках контроллеров модуля API
 */
class ApiUserWaveAction extends ApiUserAction {
    private $arPackageCache;

    public function init() {
        $this->arPackageCache=array();
    }

	/**
	 * Метод возвращает пакет привязанный к обсуждению по ключу обсуждения
	 * @param $key
	 *
	 * @return Package|bool
	 */
    public function getPackageByKey($key) {
        if(preg_match('#^package_(\d+).*$#',$key,$matches)) {
            if(!isset($this->arPackageCache[$key])) {
                $this->arPackageCache[$key]=Package::model()->findByPk(intval($matches[1]));
            }
            return $this->arPackageCache[$key];
        }
        return false;
    }

	/**
	 * Метод проверяет уровень доступа по информации из ключа
	 */
	public function checkAccessByKey($key) {
		if($obPackage=$this->getPackageByKey($key)) {
			$obToken=$this->getController()->getModule()->getApplicationTokens();
			if($obToken->getUserId()==0)
				return false;
			//Если хотим не свой пакет, надо убедиться, что есть права
			if($obPackage->client_id!=$obToken->getUserId()) {
				if(!$obToken->hasRole('SuperAdmin')) { //Не суперадмин
					if(!$obToken->hasRole('Manager')) {  //Не менеджер
						if($obToken->hasRole('BasePartner')) { // Партнёр?
							if($obPackage->client->owner_partner) { //А у пользователья есть партнёр владелец?
								if($obPackage->client->owner_partner->id_partner!=$obToken->getUserId()) { //Владелец не я
									return false;
								}
							} else { //Нет владельца
								return false;
							}
						} else { //Не партнёр и не манагер, значит лесом
							return false;
						}
					} else { //Манагер
						$obUser=People::model()->findByPk($obToken->getUserId());
						if($obUser->pgroup_id!=7) { //Ух ты пользователь то не клиент
							return false;
						}
					}
				}
			}
			return true;
		}
		return false;
	}
}
