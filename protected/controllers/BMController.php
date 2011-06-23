<?php 
/**
 * BILLManager  controller
 */
class BMController extends Controller {
    /**
     * Регистрирует клиента в биллинге
     * @return
     */
    public function actionRegister() {
        // данные для регистрации
        $username = Yii::app()->request->getParam('username');
        $password = Yii::app()->request->getParam('password');
        
        // сайт
        $site_id = (int) Yii::app()->request->getParam('site_id');		
        $site = Site::getById($site_id);
        // клиент
        $client = $site->client;
        // атрибуты клиента
        $email = $client->mail;
        $fio = $client->fio;
        $firm = $client->firm;
        
        // передаем данные
        $data = array('username'=>$username, 'passwd'=>$password, 'confirm'=>$password, 'email'=>$email, 'person'=>$fio, 'name'=>$firm, 'country'=>182);
        
        // если у клиента указана фирма - значит он юрик, иначе физик
        if ($firm) {
            $data['ptype'] = 'pcompany';
        } else {
            $data['ptype'] = 'pperson';
        }
        
        // новый BMRequest
        $bmr = new BMRequest();
        $result = $bmr->register($data);
        
        // при успешной регистрации сохряняем учетку
        if ($result['success']) {
            $site->bm_id = $result['userid'];
            $site->bm_login = $username;
            $site->bm_password = $password;
            $site->save();
        }
        
        print(json_encode($result));
    }
    
    /**
     * Генерирует и возвращает ключ для входа в биллинг
     * @return
     */
    public function actionOpen() {
        // данные для входа
        $username = Yii::app()->request->getParam('username');
        $password = Yii::app()->request->getParam('password');
        
        // передаем данные
        $data = array('username'=>$username, 'passwd'=>$password);
        
        // новый BMRequest
        $bmr = new BMRequest();
        $result = $bmr->getAuthKey($data);
        
        print(json_encode($result));
    }
}
?>
