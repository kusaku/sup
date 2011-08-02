<?php 
/**
 * Класс таблицы
 * XXX Вообще-то из модели надо убрать весь ХТМЛ!!!
 */
class Package extends CActiveRecord {
	public static function model($className = __CLASS__) {
		return parent::model($className);
	}
	
	public function tableName() {
		return 'package';
	}
	
	public function relations() {
		return array(
			// Связка с менеджером
			'manager'=>array(
				self::BELONGS_TO, 'People', 'manager_id'
			),
			// Связка с клиентом

			'client'=>array(self::BELONGS_TO, 'People', 'client_id'), 'services'=>array(self::MANY_MANY,
			
			// Связка с сервисами. Возврящает все сервися по этму пакету (заказу)
			'Service', 'serv2pack(pack_id, serv_id)'),
				
			// Связка с сервисами. Возвращает все сервисы вместе с данными из serv2pack (blablabla->quant, blablabla->service->name)
			'servPack'=>array(self::HAS_MANY, 'Serv2pack', 'pack_id', 'with'=>'service'),

			// Оплыты по заказу
			'payments'=>array(self::HAS_MANY, 'Payment', 'package_id'),

			// Связка с сайтом
			'site'=>array(
				self::BELONGS_TO, 'Site', 'site_id'
			),
			// Связка со статусами
			'status'=>array(
				self::BELONGS_TO, 'Status', 'status_id'
			)
		);
	}
	
	public static function updateById($id) {
		if ($id) {
			$pack = Package::getById($id);
			$pack->dt_change = date('Y-m-d H:i:s');
			$pack->save();
			return true;
		} else {
			return false;
		}
	}
	
	public static function getById($id) {
		return self::model()->find(array(
			'condition'=>"id=$id", 'limit'=>1
		));
	}
	
	public static function getTop($count = 100) {
		return self::model()->findAll(array(
			'condition'=>'manager_id=0 OR manager_id='.Yii::app()->user->id,
			'group'=>'client_id',
			'order'=>'dt_change DESC, dt_beg DESC', 
			'limit'=>$count
		));
	}
	
	/**
	 * Возвращает проекты менеджера
	 * @param int $manager_id [optional]
	 * @return Package
	 */
	public static function getProjects($manager_id = null) {
		isset($manager_id) or $manager_id = Yii::app()->user->id;
		return self::model()->findAll(array(
			'condition'=>"manager_id=$manager_id", 'order'=>'dt_change DESC, dt_beg DESC'
		));
	}
	
	/**
	 * ограничение области запроса и порядка
	 * @return array
	 */
	public function scopes() {
		return array(
			'byclient'=>array(
				'order'=>'client_id ASC'
			), 'bychanged'=>array(
				'order'=>'dt_change ASC'
			), 'lastmonth'=>array(
				'condition'=>'dt_change > SUBDATE(NOW(), INTERVAL 1 MONTH)'
			), 'lastyear'=>array(
				'condition'=>'dt_change > SUBDATE(NOW(), INTERVAL 1 YEAR)'
			),		
		);
	}
	
	/*
	 * Возвращаем блок заказов клиента
	 * Выводится на главной странице при входе и при изменении заказа (аяксом)
	 *
	 * Мне не нравится, что часть вёрстки пришлось расположить в моделе, но так удобнее!
	 * Предвосхищая вопросы: Да, мне не стыдно за это.
	 *
	 * Планы: Перенести всё это безобразие во View
	 */
	public static function genClientBlock($client_id) {
		$client = People::getById($client_id);
		if ($client) {
			if (sizeof($client->packages) > 1)
				print '<li id="li'.$client_id.'" class="more"> <a class="lessClick" onClick="ShowHide(\'li'.$client_id.'\');"></a>';
			else
				print '<li id="li'.$client_id.'">';
			
?>
<div class="clientInfo">
	<span class="clientName">
		<a onClick="addEditClient(<?=$client->id?>)"><?= $client->fio?></a>
		<div class="tips">
			<div class="tipsTop"></div>
			<div class="tipsBody">
				<b>E-mail</b>: <a href="mailto:<?=$client->mail?>">&lt;<?= $client->mail?>&gt;</a>
				<br>
				<b>Телефон</b>: <?= $client->phone?>
				<br>
				<?php foreach ($client->attr as $attr): ?>
				<?php if (! empty($attr->values[0]->value)): ?>
				<b><?= $attr->name?></b>: <?= $attr->values[0]->value?>
				<br>
				<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<div class="tipsBottom"></div>
		</div>
	</span>
	<a onClick="Package(0, <?=$client->id?>)" title="Создать новый заказ">Новый заказ</a>&nbsp;&nbsp;
	<a onClick="clientCard(<?=$client->id?>)" title="Просмотреть заказы клиента">Карточка клиента</a>&nbsp;&nbsp;
	<a onClick="loggerForm(<?=$client->id?>)" title="Просмотреть журнал">Журнал</a>
</div>
<?php 
$packs = $client->packages;
$forhide = '';
if ($packs)
	foreach ($packs as $key=>$package)
	//if ( ($package->status_id != 999)and($package->status_id != 15) ) // Не выводим законченные и отклонённые проекты
	{
		switch (@$package->status_id) {
			case 0:
				print '<div class="projectBox red'.$forhide.'">';
				break;
			case 1:
				print '<div class="projectBox red'.$forhide.'">';
				break;
			case 15:
				print '<div class="projectBox orange'.$forhide.'">';
				break;
			case 17:
				print '<div class="projectBox grey'.$forhide.'">';
				break;
			case 50:
				print '<div class="projectBox lightgreen'.$forhide.'">';
				break;
			case 70:
				print '<div class="projectBox green'.$forhide.'">';
				break;
			default:
				print '<div class="projectBox grey'.$forhide.'">';
				break;
		}
		
?>
<div class="projectType">
	<a onClick="Package(<?=$package->id?>, 0)" class="type">Заказ №<?= $package->id?>:&nbsp;<?= $package->name?></a>
	<a onClick="Package(<?=$package->id?>, 0)" class="more">
		<?php 
		if ($package->summa)
			print $package->summa.'руб.&nbsp;&nbsp;&nbsp;&nbsp;';
		?>
		Подробно...</a>
	<br>
	<?php 
	if ($package->manager_id != Yii::app()->user->id and $package->manager_id != 0)
		print '('.@$package->manager->fio.')';
	?>
</div>
<?php 
if ($package->redmine_proj)
	$percent = Redmine::getIssuePercent($package->redmine_proj);
else
	$percent = '0';
	
switch ($package->status_id):
	case 0:
		print '<div class="projectState new"><a onClick="takePack('.$package->id.', '.$client_id.');"><strong>Взять заказ</strong></a><br><a onClick="decline('.$package->id.', '.$client_id.')" class="icon">Отклонить</a></div>';
		break;
	case 1:
		print '<div class="projectState new"><a onClick="takePack('.$package->id.', '.$client_id.');"><strong>Взять заказ</strong></a><br><a onClick="decline('.$package->id.', '.$client_id.')" class="icon">Отклонить</a></div>';
		break;
	case 15:
		print '<div class="projectState"><br/>'.@$package->status->name.'</div>';
		break;
	case 17:
		print '<div class="projectState">
					<strong class="uppper">Не оплачен!</strong>
					<a onClick="addPay('.$package->id.', '.$client_id.', '.$package->summa.');" class="icon"><img src="images/icon04.png" title="Поставить оплату ('.$package->summa.' руб.)"/></a>
					<a onClick="selectMailTemplate('.$client->id.')" class="icon"><img src="images/icon02.png" title="Отправить письмо клиенту"/></a>
					<a onClick="decline('.$package->id.', '.$client_id.')" class="icon"><img src="images/icon03.png" title="Отклонить"/></a>
			</div>';
		break;
	case 20:
		print '<div class="projectState">
					<strong class="uppper">Част. опл.</strong>
					<a onClick="addPay('.$package->id.', '.$client_id.', '.($package->summa - $package->paid).');" class="icon"><img src="images/icon04.png" title="Поставить оплату ('.($package->summa - $package->paid).' руб.)"/></a>
					<a onClick="selectMailTemplate('.$client->id.')" class="icon"><img src="images/icon02.png" title="Отправить письмо клиенту"/></a>
					<a onClick="decline('.$package->id.', '.$client_id.')" class="icon"><img src="images/icon03.png" title="Отклонить"/></a>
			</div>';
		break;

	case 30:
		print '<div class="projectState">
					<div class="progressBar">
						<div class="progressStat" style="width:'.$percent.'%">'.$percent.'%</div>
					</div>
					<a onClick="alert(123123);" class="icon"><img src="images/towork.png" title="Отдать в работу все заказанные услуги"/></a>
					<a onClick="selectMailTemplate('.$client->id.')" class="icon"><img src="images/icon02.png" title="Отправить письмо клиенту"/></a>
					<a onClick="decline('.$package->id.', '.$client_id.')" class="icon"><img src="images/icon03.png" title="Отклонить"/></a>
				</div>';
		break;
	case 50:
		print '<div class="projectState">
					<div class="progressBar">
						<div class="progressStat" style="width:'.$percent.'%">'.$percent.'%</div>
					</div>
					<a onClick="alert(123123);" class="icon"><img src="images/complete.png" title="Все работы выполнены"/></a>
					<a onClick="selectMailTemplate('.$client->id.')" class="icon"><img src="images/icon02.png" title="Отправить письмо клиенту"/></a>
					<a onClick="decline('.$package->id.', '.$client_id.')" class="icon"><img src="images/icon03.png" title="Отклонить"/></a>
				</div>';
		break;
	case 70:
		print '<div class="projectState"><strong class="done">Выполнен!</strong><br/>
					<a href="#" class="icon"><img src="images/icon01.png" title="Подготовить документы к отправке"/></a>
					<a onClick="selectMailTemplate('.$client->id.')" class="icon"><img src="images/icon02.png" title="Отправить письмо клиенту"/></a>
					<a href="#" class="icon"><img src="images/icon03.png" title="В архив"/></a></div>';
		break;
	default:
		print '<div class="projectState"><br/>'.@$package->status->name.'</div>';
		break;
endswitch;
?>
<div class="projectDomain">
	<a onClick='editDomain(<?=@$package->site->id?>)' title="<?=@$package->site->url?>"><?= @$package->site->url?></a>
</div>
<div class="projectDate act">
	<?= $package->dt_beg?>
</div>
</div>
<?php 
$forhide = ' forhide';
}
print "</li>";
} else
	return null;
}

}
?>
