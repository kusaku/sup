<?php
/**
 * @var Package $package
 */
switch($package->source_id) {
	case 0:
		$title='Заказ создан менеджером по заявке по телефону';
		echo CHtml::image('/images/icons/phone_vintage.png',$title,array('style'=>'margin:0 0 3px 3px;','title'=>$title));
	break;
	case 1:
		$title='Заказ создан клиентом через ЛКК';
		echo CHtml::image('/images/icons/user.png',$title,array('style'=>'margin:0 0 3px 3px;','title'=>$title));
	break;
	case 2:
		$title='Заказ создан клиентом через сайт фабрикасайтов.рф';
		echo CHtml::image('/images/icons/rf.png',$title,array('style'=>'margin:0 0 3px 3px;','title'=>$title));
	break;
	case 3:
		$title='Заказ создан партнёром через ЛКП';
		echo CHtml::image('/images/icons/user_red.png',$title,array('style'=>'margin:0 0 3px 3px;','title'=>$title));
	break;
	case 4:
		$title='Заказ создан менеджером партнёрского отдела через ЛКМПО';
		echo CHtml::image('/images/icons/user_queen.png',$title,array('style'=>'margin:0 0 3px 3px;','title'=>$title));
	break;
	case 5:
		$title='Заказ создан клиентом через сайт fabricasaitov.ru';
		echo CHtml::image('/images/icons/ru.png',$title,array('style'=>'margin:0 0 3px 3px;','title'=>$title));
	break;
	default:
		$title='Система несмогла определить как создан заказ';
		echo CHtml::image('/images/icons/user_zorro.png',$title,array('style'=>'margin:0 0 3px 3px;','title'=>$title));
	break;
}