<?php

class SiteController extends Controller
{
	/*
	 * Возвращает список доменов (сайтов) выбранного клиента. 
	 * На входе ИД клиента и опционально ИД выбранного сайта (он будет выбран по умолчанию)
	 */
	public function actionGetList()
	{

		if ( Yii::app()->request->getParam('client_id') != null )
		{

			$sel = ( Yii::app()->request->getParam('selected') != null ) ? Yii::app()->request->getParam('selected') : $sel = 0;

			$sites = Site::getAllByClient( Yii::app()->request->getParam('client_id') );
			$res = 'Домен: <select name="pack_site_id"><option value="0">..</option>';
			if ( isset( $sites ) )
			foreach ($sites as $site)
			{
				$res = $res.'<option value="'.$site->id.'"';
				if ( $sel == $site->id)
					$res = $res." selected";
				$res = $res.'>'.$site->url.'</option>';
			}
			$res = $res.'</select>';
			$res = $res.'<a href="javascript:loadNewSite();" class="plus">+</a>';
			print $res;


		} else print "Ошибка - не передан ID клиента!";

	}

	public function actionView()
	{
		$id = Yii::app()->request->getParam('id');
		$client_id = Yii::app()->request->getParam('client_id');

		if ( !$client_id ) $client_id = 0;
		//if ( !$id and !$client_id ) die('Нужен ID клиента для нового сайта или ID сайта для существующего!');

		if ( $id ) // Если передан нулевой ID, создаём новый сайт
			$site = Site::getById( $id );
			else
			{
				$site = new Site();
				$site->client_id = $client_id;
			}

			$this->renderPartial('view',array('site'=>$site, 'no_button'=>Yii::app()->request->getParam('no_button') ));
	}

	/*
		Сохраняем данные, которые вернулись из формы.
	*/
	public function actionSave($data = null)
	{
		if ( !$data ) $data = $_POST; // Если нам не передали параметр $DATA, берём данные из $_POST
		if ( isset ($data['site_id']) )
		{
			if ( $data['site_id'] ) $site = Site::GetById($data['site_id']);
			else $site = new Site();
			
			$site->url = $data['site_url'];
			$site->client_id = $data['client_id'];
			$site->host = $data['site_host'];
			$site->ftp = $data['site_ftp'];
			$site->db = $data['site_db'];
			$site->save();
			$this->redirect(Yii::app()->homeUrl);
		}
		else throw new CHttpException('_00','Не указан идентификатор (ID) сайта!');
	}

	public function actionChecker()
	{
		if ( Site::getByUrl(Yii::app()->request->getParam('url')) )
			print Site::getByUrl(Yii::app()->request->getParam('url'))->id;
		else print 0;
	}

}