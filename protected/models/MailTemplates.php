<?php 
class MailTemplates extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'mail_templates';
	}
	
	public function relations() {
		return array(
			'owner'=>array(
				self::BELONGS_TO,
				'People',
				'people_id',
				'joinType'=>'INNER JOIN'
			)
		);
	}
	
	public function getById($id) {
		return self::model()->findByPk($id);
	}
	
	public function getTplFor($object, $tpl) {
		if ($object instanceof People) {
		
			preg_match_all('/%([\w\$->]+)%/', $tpl, $matches);
			
			foreach ($matches[1] as & $match) {
				if (isset($object->$match))
					$match = $object->$match;
				elseif (isset($object->attr[$match]->values[0]->value))
					$match = $object->attr[$match]->values[0]->value;
				else {
					try {
						$match = eval("return $match;");
					}
					catch(Exception $e) {
						$match = 'error';
					}
				}
			}
			
			return str_replace($matches[0], $matches[1], $tpl);
			
		} elseif ($object instanceof Package) {
		
			preg_match_all('/%([\w\$->]+)%/', $tpl, $matches);
			
			foreach ($matches[1] as & $match) {
				$match = html_entity_decode($match);
				if (isset($object->$match))
					$match = $object->$match;
				else {
					try {
						$match = eval("return $match;");
					}
					catch(Exception $e) {
						$match = 'error';
					}
				}
			}
			
			return str_replace($matches[0], $matches[1], $tpl);
		}
	}
	
	public function getBodyFor($object) {
		return $this->getTplFor($object, $this->body);
	}
	
	public function getSubjectFor($client) {
		return $this->getTplFor($client, $this->subject);
	}
}
