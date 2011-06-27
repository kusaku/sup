<?php 
# (c) 2011 aks - FabricaSaitov.ru

/**
 *  Класс для работы с ISPManager
 *  описание работы с API ISPManager через ISPRequest:
 *  https://redmine.fabricasaitov.ru/projects/hostsite/wiki/
 */
 
// URL BILLManager'а:
define('BM_URL', 'https://host.fabricasaitov.ru/manager/billmgr');

class ISPRequest {

    private $ssl;
    private $url;
    private $post;
    private $headers;
    private $uagent;
    private $result;
    private $errcode;
    
    private function parse_xml($xml) {
        $sxmle = new SimpleXMLElement($xml);
        return $sxmle;
    }
    
    public function setHeaders($headers) {
        $this->headers = $headers;
        return $this;
    }
    public function setPost($post) {
        $this->post = array_merge($post, array('out'=>'xml'));
        return $this;
    }
    public function setSsl($ssl) {
        $this->ssl = $ssl;
        return $this;
    }
    public function setUagent($uagent) {
        $this->uagent = $uagent;
        return $this;
    }
    public function setUrl($url) {
        $this->url = $url;
        return $this;
    }
    public function result() {
        return $this->result;
    }
    public function errcode() {
        return $this->errcode;
    }
    
    public function exec() {
        if ( empty($this->url)) {
            return false;
        }
        
        $post = array();
        
        if (is_array($this->post)) {
            foreach ($this->post as $name=>$value) {
                $post[] = $name.'='.urlencode($value);
            }
        }
        
        $post = join('&', $post);
        
        if (function_exists('curl_init')) {
            $ch = curl_init($this->url);
            
            if ($this->ssl) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            }
            
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            
            if (is_array($this->post)) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            }
            
            if (is_array($this->headers)) {
                curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
            }
            
            if (! empty($this->uagent)) {
                curl_setopt($ch, CURLOPT_USERAGENT, $this->uagent);
            }
            
            $result = curl_exec($ch);
            $this->errcode = curl_errno($ch);
            curl_close($ch);
        } else {
            $domain = substr($this->url, strpos($this->url, '//') + 2);
            $path = substr($domain, strpos($domain, '/'));
            $domain = substr($domain, 0, strpos($domain, '/'));
            
            $errno = 0;
            
            if ($this->ssl) {
                $fp = fsockopen("ssl://$domain", 443, $errno);
            } else {
                $fp = fsockopen("http://$domain", 80, $errno);
            }

            
            $send = "";
            $send .= "POST $path HTTP/1.1\r\n";
            $send .= "Host: $domain\r\n";
            $send .= "Content-length: ".strlen($post)."\r\n";
            
            if (is_array($this->headers))
                foreach ($this->headers as $header) {
                    $send .= "$header\r\n";
                }
                
            if (! empty($this->uagent)) {
                $send .= "User-Agent: $this->uagent\r\n";
            }
            
            $send .= "Connection: close\r\n";
            $send .= "\r\n";
            $send .= "$post\r\n\r\n";
            
            fwrite($fp, $send);
            $headers = fread($fp, 1024 * 1024);
            $result = fread($fp, 1024 * 1024 * 1024); // max 1Mb
            $this->errcode = $errno;
            fclose($fp);
        }
        
        if ($this->errcode != 0 && empty($result)) {
            $this->result = false;
        } else {
            $this->result = $this->parse_xml($result);
        }
        
        return $this;
    }
    
    function __construct($url, $post = array(), $ssl = false, $headers = array(), $uagent = '') {
        $this->setUrl($url);
        $this->setPost($post);
        $this->setSsl($ssl);
        $this->setHeaders($headers);
        $this->setUagent($uagent);
        $this->result = false;
        $this->errcode = 0;
    }
}
/**
 *  Класс для работы с BillManager через ISPRequest:
 *  https://redmine.fabricasaitov.ru/projects/hostsite/wiki/
 */
 
class BMRequest extends ISPRequest {
	/**
	 * Генерация случайного ключа
	 * @param int $len [optional] длина ключа
	 * @return string $key
	 */
    static function generateKey($len = 16) {
        $set = '1234567890abcdef';
        $key = '';
        for ($i = 0; $i < $len; $i++) {
            $key .= $set[(rand() % strlen($set))];
        }
        return $key;
    }
    
    /**
     * открытие сессии по ключу
     * @param array $data ассоциативный массив с данными
     * @return array $ret
     */
    public function getAuthKey($data) {
        $required = array('username', 'passwd');
        
        foreach ($required as $field) {
            if ( empty($data[$field]))
                return array('success'=>false, 'code'=>4, 'msg'=>'', 'val'=>$field);
        }
        
        $key = BMRequest::generateKey(32);
        
        $post = array('func'=>'session.newkey', 'authinfo'=>$data['username'].':'.$data['passwd'], 'username'=>$data['username'], 'key'=>$key);
        
        $result = $this->setPost($post)->exec()->result();
        
        if (!$result) {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'Network error', 'val'=>'');
        } elseif ($result->error) {
            $code = (int) $result->error['code'];
            $msg = (string) $result->error['msg'] or $msg = (string) $result->error;
            $val = (string) $result->error['val'];
            return array('success'=>false, 'code'=>$code, 'msg'=>$msg, 'val'=>$val);
        } elseif ($result->ok) {
            return array('success'=>true, 'code'=>$this->errcode(), 'msg'=>'', 'val'=>'', 'username'=>$data['username'], 'key'=>$key);
        } else {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'BILLManager error', 'val'=>'');
        }
    }
    
    /**
     * авторизация пользователя
     * @param array $data ассоциативный массив с данными
     
     * @return array $ret
     */
    public function login($data) {
        $required = array('username', 'passwd');
        
        foreach ($required as $field) {
            if ( empty($data[$field]))
                return array('success'=>false, 'code'=>4, 'msg'=>'', 'val'=>$field);
        }
        
        $post = array('func'=>'auth', 'sok'=>'ok');
        $post = array_merge($data, $post);
        
        $result = $this->setPost($post)->exec()->result();
        
        if (!$result) {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'Network error', 'val'=>'');
        } elseif ($result->authfail) {
            return array('success'=>false, 'code'=>100, 'msg'=>'access deny', 'val'=>'');
        } elseif ($result->auth) {
            $authid = (int) $result->auth;
            return array('success'=>true, 'code'=>$this->errcode(), 'msg'=>'', 'val'=>'', 'authid'=>$authid);
        } else {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'BILLManager error', 'val'=>'');
        }
    }
    
    /**
     * регистрация пользователя
     * @param array $data ассоциативный массив с данными
     * @return array $ret
     */
    public function register($data) {
        $required = array('username', 'passwd', 'confirm', 'email', 'person');
        $data['ptype'] == 'pcompany' and $required[] = 'name';
        
        foreach ($required as $field) {
            if ( empty($data[$field]))
                return array('success'=>false, 'code'=>4, 'msg'=>'', 'val'=>$field);
        }
        
        if ($data['passwd'] != $data['confirm'])
            return array('success'=>false, 'code'=>4, 'msg'=>'', 'val'=>'confirm');
            
        $post = array('func'=>'register', 'sok'=>'ok');
        $post = array_merge($data, $post);
        
        $result = $this->setPost($post)->exec()->result();
        
        if (!$result) {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'Network error', 'val'=>'');
        } elseif ($result->error) {
            $code = (int) $result->error['code'];
            $msg = (string) $result->error['msg'] or $msg = (string) $result->error;
            $val = (string) $result->error['val'];
            return array('success'=>false, 'code'=>$code, 'msg'=>$msg, 'val'=>$val);
        } elseif ($result->ok) {
            $accountid = (int) $result-> {'account.id'} ;
            $userid = (int) $result-> {'user.id'} ;
            $profileid = (int) $result-> {'profile.id'} ;
            return array('success'=>true, 'code'=>$this->errcode(), 'msg'=>'', 'val'=>'', 'accountid'=>$accountid, 'userid'=>$userid, 'profileid'=>$profileid);
        } else {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'BILLManager error', 'val'=>'');
        }
    }
    
    /**
     * запрос профиля пользователя
     * @param array $data ассоциативный массив с данными
     * @return array $ret
     */
    public function queryProfiles($data) {
        $required = array('username', 'passwd');
        
        foreach ($required as $field) {
            if ( empty($data[$field]))
                return array('success'=>false, 'code'=>4, 'msg'=>'', 'val'=>$field);
        }
        
        $post = array('func'=>'profile', 'authinfo'=>$data['username'].':'.$data['passwd']);
        $post = array_merge($data, $post);
        
        $result = $this->setPost($post)->exec()->result();
        
        if ($result === false) {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'Network error', 'val'=>'');
        } elseif ($result->error) {
            $code = (int) $result->error['code'];
            $msg = (string) $result->error['msg'] or $msg = (string) $result->error;
            $val = (string) $result->error['val'];
            return array('success'=>false, 'code'=>$code, 'msg'=>$msg, 'val'=>$val);
        } else {
            $elids = array();
            foreach ($result->elem as $elem) {
                $elids[] = (int) $elem->id;
            }
            return array('success'=>true, 'code'=>$this->errcode(), 'msg'=>'', 'val'=>'', 'elids'=>$elids);
        }
    }
    
    /**
     * создание профиля, а если установлен elid=>profileid
     * в аргументах - редактирование профиля
     * @param array $data ассоциативный массив с данными
     * @return array $ret
     */
    public function profileEdit($data) {
        $required = array('username', 'passwd', 'person');
        
        $data['ptypeval'] == 'pcompany' and $required[] = 'name';
        
        foreach ($required as $field) {
            if ( empty($data[$field]))
                return array('success'=>false, 'code'=>4, 'msg'=>'', 'val'=>$field);
        }
        
        $post = array('func'=>'profile.edit', 'sok'=>'ok', 'authinfo'=>$data['username'].':'.$data['passwd']);
        $post = array_merge($data, $post);
        
        $result = $this->setPost($post)->exec()->result();
        
        if (!$result) {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'Network error', 'val'=>'');
        } elseif ($result->error) {
            $code = (int) $result->error['code'];
            $msg = (string) $result->error['msg'] or $msg = (string) $result->error;
            $val = (string) $result->error['val'];
            return array('success'=>false, 'code'=>$code, 'msg'=>$msg, 'val'=>$val);
        } elseif ($result->ok) {
            return array('success'=>true, 'code'=>$this->errcode(), 'msg'=>'', 'val'=>'');
        } else {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'BILLManager error', 'val'=>'');
        }
    }
    
    /**
     * запрос контактов доменов пользователя (для заказа домена)
     * @param array $data ассоциативный массив с данными
     * @return array $ret
     */
    public function queryComainContacts($data) {
        $required = array('username', 'passwd');
        foreach ($required as $field) {
            if ( empty($data[$field]))
                return array('success'=>false, 'code'=>4, 'msg'=>'', 'val'=>$field);
        }
        $post = array('func'=>'domaincontact', 'sok'=>'ok', 'authinfo'=>$data['username'].':'.$data['passwd']);
        $post = array_merge($data, $post);
        
        $result = $this->setPost($post)->exec()->result();
        
        if ($result === false) {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'Network error', 'val'=>'');
        } elseif ($result->error) {
            $code = (int) $result->error['code'];
            $msg = (string) $result->error['msg'] or $msg = (string) $result->error;
            $val = (string) $result->error['val'];
            return array('success'=>false, 'code'=>$code, 'msg'=>$msg, 'val'=>$val);
        } else {
            $elids = array();
            foreach ($result->elem as $elem) {
                $elids[] = (int) $elem->id;
            }
            return array('success'=>true, 'code'=>$this->errcode(), 'msg'=>'', 'val'=>'', 'elids'=>$elids);
        }
    }
    
    /**
     * создание контакта домена, а если установлен elid=>profileid
     * в аргументах - редактирование контакта домена
     * @param array $data ассоциативный массив с данными
     * @return array $ret
     */
    public function domainContactEdit($data) {
        $data['email'] = $data['domemail'];
        
        $required = array('username', 'passwd');
        
        foreach ($required as $field) {
            if ( empty($data[$field]))
                return array('success'=>false, 'code'=>4, 'msg'=>'', 'val'=>$field);
        }
        
        $post = array('func'=>'domaincontact.edit', 'sok'=>'ok', 'authinfo'=>$data['username'].':'.$data['passwd']);
        $post = array_merge($data, $post);
        
        $result = $this->setPost($post)->exec()->result();
        
        if (!$result) {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'Network error', 'val'=>'');
        } elseif ($result->error) {
            $code = (int) $result->error['code'];
            $msg = (string) $result->error['msg'] or $msg = (string) $result->error;
            $val = (string) $result->error['val'];
            $val == 'email' and $val = 'domemail';
            return array('success'=>false, 'code'=>$code, 'msg'=>$msg, 'val'=>$val);
        } elseif ($result->ok) {
            $elid = (int) $result-> {'domaincontact.id'} ;
            return array('success'=>true, 'code'=>$this->errcode(), 'msg'=>'', 'val'=>'', 'elid'=>$elid);
        } else {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'BILLManager error', 'val'=>'');
        }
    }
    
    /**
     * заказ домена, customer и subjnic - id контакта домена,
     * price - id тарифа
     * @param array $data ассоциативный массив с данными
     * @return array $ret
     */
    public function orderDomain($data) {
        $required = array('username', 'passwd', 'customertype', 'customer', 'subjnic', 'domain', 'period', 'price', 'autoprolong');
        
        foreach ($required as $field) {
            if ( empty($data[$field]))
                return array('success'=>false, 'code'=>4, 'msg'=>'', 'val'=>$field);
        }
        
        $post = array('func'=>'domain.order.4', 'sok'=>'ok', 'authinfo'=>$data['username'].':'.$data['passwd'], 'operation'=>'register', 'payfrom'=>'neworder');
        // XXX регистратор должен быть установле 1 для spb.ru и msk.ru
        $post['registrar'] = 2;
        $post = array_merge($data, $post);
        
        $result = $this->setPost($post)->exec()->result();
        
        if (!$result) {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'BILLManager error', 'val'=>'');
        } elseif ($result->error) {
            $code = (int) $result->error['code'];
            $msg = (string) $result->error['msg'] or $msg = (string) $result->error;
            $val = (string) $result->error['val'];
            return array('success'=>false, 'code'=>$code, 'msg'=>$msg, 'val'=>$val);
        } elseif ($result->ok) {
            $itemid = (int) $result-> {'item.id'} ;
            return array('success'=>true, 'code'=>$this->errcode(), 'msg'=>'', 'val'=>'', 'itemid'=>$itemid);
        } else {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'BILLManager error', 'val'=>'');
        }
    }
    
    /**
     * заказ хостинга, price - id тарифа
     * @param array $data ассоциативный массив с данными
     * @return array $ret
     */
    public function orderVhost($data) {
        $required = array('username', 'passwd', 'domain', 'period', 'price');
        
        foreach ($required as $field) {
            if ( empty($data[$field]))
                return array('success'=>false, 'code'=>4, 'msg'=>'', 'val'=>$field);
        }
        
        $post = array('func'=>'vhost.order.5', 'sok'=>'ok', 'authinfo'=>$data['username'].':'.$data['passwd'], 'payfrom'=>'neworder');
        $post = array_merge($data, $post);
        
        $result = $this->setPost($post)->exec()->result();
        
        if (!$result) {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'BILLManager error', 'val'=>'');
        } elseif ($result->error) {
            $code = (int) $result->error['code'];
            $msg = (string) $result->error['msg'] or $msg = (string) $result->error;
            $val = (string) $result->error['val'];
            return array('success'=>false, 'code'=>$code, 'msg'=>$msg, 'val'=>$val);
        } else {
            $itemid = (int) $result-> {'item.id'} ;
            return array('success'=>true, 'code'=>$this->errcode(), 'msg'=>'', 'val'=>'', 'itemid'=>$itemid);
        }
    }
    
    /**
     * запрос заказов
     * @param array $data ассоциативный массив с данными
     * @return array $ret
     */
    public function queryOrders($data) {
        $required = array('username', 'passwd');
        foreach ($required as $field) {
            if ( empty($data[$field]))
                return array('success'=>false, 'code'=>4, 'msg'=>'', 'val'=>$field);
        }
        $post = array('func'=>'order', 'authinfo'=>$data['username'].':'.$data['passwd']);
        $post = array_merge($data, $post);
        
        $result = $this->setPost($post)->exec()->result();
        
        if ($result === false) {
            return array('success'=>false, 'code'=>$this->errcode(), 'msg'=>'Network error', 'val'=>'');
        } elseif ($result->error) {
            $code = (int) $result->error['code'];
            $msg = (string) $result->error['msg'] or $msg = (string) $result->error;
            $val = (string) $result->error['val'];
            return array('success'=>false, 'code'=>$code, 'msg'=>$msg, 'val'=>$val);
        } else {
            $elids = array();
            foreach ($result->elem as $elem) {
                $elids[] = (int) $elem->id;
            }
            return array('success'=>true, 'code'=>$this->errcode(), 'msg'=>'', 'val'=>'', 'elids'=>$elids);
        }
    }
    
    public function __construct() {
        $headers = array('Content-type: application/x-www-form-urlencoded');
        parent::__construct(BM_URL, array(), true, $headers);
    }
}
?>
