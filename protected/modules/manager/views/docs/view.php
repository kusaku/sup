<html>
	<head>
		<title>SUP.DOCS</title>
		<link href="/css/docs.css" rel="stylesheet" type="text/css"></head>
	<body>
		<a onclick="return confirm('Этим действием Вы подтверджаете, что ознакомились с документами.');" style="float:right;" href="/manager/docs/seen/<?=$id?>?hash=<?=$hash?>" class="button fix">С документами ознакомлен</a>
		<h2>Комплект документов по заказу № <?= $package->getNumber(); ?></h2>
		<div>
			<a href="/manager/docs/contract/<?=$id?>?hash=<?=$hash?>" class="button bt-left fix">Договор</a>
			<a href="/manager/docs/bill/<?=$id?>?hash=<?=$hash?>" class="button bt-center fix">Счёт</a>
			<a href="/manager/docs/qiwi/<?=$id?>?hash=<?=$hash?>" class="button bt-center fix">QIWI</a>
			<a href="/manager/docs/receipt/<?=$id?>?hash=<?=$hash?>" class="button bt-center fix">Квитанция</a>
			<a href="/manager/docs/act/<?=$id?>?hash=<?=$hash?>" class="button bt-right fix">Акт</a>
		</div>
		<div class="content">
			<strong><?= $package->client->fio?></strong>, Вас приветствует система автоматической подготовки документов.
			<p>
				Вероятнее всего Вы попалю сюда, получив письмо, отправленное на ваш почтовый ящик (<?= $package->client->mail?>).       
				Здесь Вы можете самостоятельно получить требуемые документы (выписать счёт, получить экземпляр договора или распечатать акт выполненных работ), связанные с заказом № <?= $package->getNumber(); ?>.        
				Эту страницу можно посетить в любое удобное для Вас время.
			</p>
			<sup>* - В случае изменений в заказе документы автоматически обновятся.</sup>
		</div>
	</body>
</html>
