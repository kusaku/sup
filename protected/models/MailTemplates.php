<?php 
class MailTemplates extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'mail_templates';
	}
	
	public function relations() {
		return array('owner'=>array(self::BELONGS_TO, 'People', 'people_id', 'joinType'=>'INNER JOIN'));
	}
	
	/**
	 *
	 * @param int $id
	 * @return MailTemplates
	 */
	public function getById($id) {
		return self::model()->findByPk($id);
	}
	
	/**
	 *
	 * @param People $client
	 * @param string $tpl
	 * @return string $body
	 */
	public function getTplFor($client, $tpl) {
		if ($client instanceof People) {
		
			preg_match_all('/%(\w+)%/', $tpl, $matches);
			
			foreach ($matches[0] as & $match) {
				$match = "/$match/";
			}
			
			foreach ($matches[1] as & $match) {
				if (isset($client->$match))
					$match = $client->$match;
				elseif (isset($client->attr[$match]->values[0]->value))
					$match = $client->attr[$match]->values[0]->value;
				else
					$match = '';
			}
			
			return preg_replace($matches[0], $matches[1], $tpl);
		}
	}
	
	/**
	 *
	 * @param People $client
	 * @return string $body
	 */
	public function getBodyFor($client) {
		return $this->getTplFor($client, $this->body);
	}
	
	/**
	 *
	 * @param People $client
	 * @return string $body
	 */
	public function getSubjectFor($client) {
		return $this->getTplFor($client, $this->subject);
	}
}
