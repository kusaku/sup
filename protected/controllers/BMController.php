<?php 
/**
 * BILLManager controller
 */
class BMController extends Controller {
    /**
     * Регистрирует клиента в биллинге
     * @return
     */
    public function actionRegister() {
        // клиент
        $client_id = (int) Yii::app()->request->getParam('client_id');
        $client = People::getById($client_id);
        
        // XXX кое-что надо всё-таки брать из таблицы people!
        isset($client->attr['username']) and $username = $client->attr['username']->value[0]->value or $username = '';
        isset($client->attr['password']) and $password = $client->attr['password']->value[0]->value or $password = '';
        isset($client->attr['email']) and $email = $client->attr['email']->value[0]->value or $email = '';
        isset($client->attr['person']) and $person = $client->attr['person']->value[0]->value or $person = '';
        isset($client->attr['name']) and $name = $client->attr['name']->value[0]->value or $name = '';
        isset($client->attr['country']) and $country = $client->attr['country']->value[0]->value or $country = 182;
        
        // передаем данные
        $data = array('username'=>$username,
                         'passwd'=>$password,
                         'confirm'=>$password,
                         'email'=>$email,
                         'person'=>$person,
                         'name'=>$name,
                         'country'=>$country);
        
        // если у клиента указана фирма - значит он юрик, иначе физик
        if (! empty($name)) {
            $data['ptype'] = 'pcompany';
        } else {
            $data['ptype'] = 'pperson';
        }
        
        // новый BMRequest
        $bmr = new BMRequest();
        $result = $bmr->register($data);
        
        // при успешной регистрации сохряняем ID учетки в атрибутах
        if ($result['success']) {
            // 5003 == attribute_id для атрибута bm_id
            isset($client->value[5003]) and $attr = $client->value[5003] or $attr = new PeopleAttr();
            $attr->attribute_id = 5003;
            $attr->people_id = $client->primaryKey;
            $attr->value = $result['userid'];
            $attr->save();
        }
        
        print(json_encode($result));
    }
    
    /**
     * Генерирует и возвращает ключ для входа в биллинг
     * @return
     */
    public function actionOpen() {
        // клиент
        $client_id = (int) Yii::app()->request->getParam('client_id');
        $client = People::getById($client_id);
        
        isset($client->attr['username']) and $username = $client->attr['username']->value[0]->value or $username = '';
        isset($client->attr['password']) and $password = $client->attr['password']->value[0]->value or $password = '';
        
        // передаем данные
        $data = array('username'=>$username,
                         'passwd'=>$password);
        
        // новый BMRequest
        $bmr = new BMRequest();
        $result = $bmr->getAuthKey($data);
        
        if ($result['success']) {
        }
        
        print(json_encode($result));
    }
    
    /**
     * Заказывает витруальный хостинг
     * @return
     */
    public function actionOrderVhost() {
        // сайт
        $site_id = (int) Yii::app()->request->getParam('site_id');
        $site = Site::getById($site_id);
        
        // ID пакета
        $package_id = (int) Yii::app()->request->getParam('package_id');
        // ID услуги
        $service_id = (int) Yii::app()->request->getParam('service_id');
        
        // клиент и его учетка
        $client = $site->client;
        isset($client->attr['username']) and $username = $client->attr['username']->value[0]->value or $username = '';
        isset($client->attr['password']) and $password = $client->attr['password']->value[0]->value or $password = '';
        
        // данные для конкретного тарифа - с периодом и дополнениями
        $prices = array(68=>array('price'=>39,
                         'period'=>21,
                         'addon_40'=>1000,
                         'addon_43'=>1,
                         'addon_44'=>1),
                         69=>array('price'=>39,
                         'period'=>22,
                         'addon_40'=>1000,
                         'addon_43'=>1,
                         'addon_44'=>1),
                         70=>array('price'=>39,
                         'period'=>23,
                         'addon_40'=>1000,
                         'addon_43'=>1,
                         'addon_44'=>1));
        
        // передаем данные
        $data = array_merge($prices[$service_id], array('username'=>$username, 'passwd'=>$password, 'domain'=>$site->url));
        $serv2pack = Serv2pack::getByIds($service_id, $package_id);
        
        // новый BMRequest
        $bmr = new BMRequest();
        $result = $bmr->orderVhost($data);
        
        // период хостинга относительно now
        $next = array(68=>'+3 month',
                         69=>'+6 month',
                         70=>'+12 month');
        
        // при успехе обновим дату создания и истечения услуги
        if ($result['success']) {
            $serv2pack = Serv2pack::getByIds($service_id, $package_id);
            $serv2pack->dt_beg = date('Y-m-d H:i:s');
            $serv2pack->dt_end = date('Y-m-d H:i:s', strtotime('now '.$next[$service_id]));
            $serv2pack->save();
        }
        
        print(json_encode($result));
    }
    
    /**
     * Заказывает доменное имя
     * @return
     */
    public function actionOrderDomain() {
        // сайт
        $site_id = (int) Yii::app()->request->getParam('site_id');
        $site = Site::getById($site_id);
        
        // ID пакета
        $package_id = (int) Yii::app()->request->getParam('package_id');
        // ID услуги
        $service_id = (int) Yii::app()->request->getParam('service_id');
        
        // клиент и его учетка
        $client = $site->client;
        isset($client->attr['username']) and $username = $client->attr['username']->value[0]->value or $username = '';
        isset($client->attr['password']) and $password = $client->attr['password']->value[0]->value or $password = '';

        
        $data = array('username'=>$username,
                         'passwd'=>$password);
        $bmr = new BMRequest();

        
        $result = $bmr->queryDomainContacts($data);
        
        // выход при ошибке
        if (!$result['success'])
            exit(json_encode($result));
            
        // если есть контакты домена, используем последний, иначе - создаем новый
        if (! empty($result['elids'])) {
            $lastdcid = array_pop($result['elids']);
        } else {
            $data['name'] = 'AutoContact from SUP';
            foreach ($client->attr as $name=>$value)
                $data[$name] = $value;
            $result = $bmr->domainContactEdit($data);
            // выход при ошибке
            if (!$result['success'])
                exit(json_encode($result));
            $lastdcid = $result['elid'];
        }
        
        // данные для конкретного тарифа - с периодом и дополнениями
        $prices = array(72=>array('price'=>38,
                         'period'=>16,
                         'autoprolong'=>30));
        
        $data = array_merge($prices[$service_id], array('username'=>$username, 'passwd'=>$password, 'customer'=>$lastdcid, 'subjnic'=>$lastdcid, 'domain'=>$site->url, 'elid'=>$lastdcid, 'customertype'=>'person'));
        
        $result = $bmr->orderDomain($data);
        
        // при успехе обновим дату создания и истечения услуги
        if ($result['success']) {
            $serv2pack = Serv2pack::getByIds($service_id, $package_id);
            $serv2pack->dt_beg = date('Y-m-d H:i:s');
            $serv2pack->dt_end = date('Y-m-d H:i:s', strtotime('now +12 month'));
            $serv2pack->save();
        }
        
        print(json_encode($result));
    }
}
?>
