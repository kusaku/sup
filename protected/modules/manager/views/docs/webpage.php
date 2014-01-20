<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>SUP.DOCS</title>
		<link href="/css/docs.css" rel="stylesheet" type="text/css" />
		<style type='text/css' media='all'>
			.content {
			    width: 800px;
			    padding: 50px 30px 150px 30px;
			    border-width: 1px 2px 2px 1px;
			    border-color: #000000;
			    border-style: solid;
			    margin: 60px 0px;
			}
		</style>
		<style type='text/css' media='print'>
			.header {
			    display: none;
			}
			
			.content {
			    padding: 0;
			    border: 0px;
			    padding: 0;
			    margin: 0;
			}
		</style>
	</head>
	<body>
		<div class="header">
			<h2 style="float: left;">Счёт на оплату по заказу № <?= $package->getNumber(); ?></h2>
			<hr><a href="/manager/docs/<?=$id;?>?hash=<?=$hash;?>" class="button">&larr;</a>
			<a href="javascript:window.print()" class="button"><img src="/images/printer.png"></a><a href="/<?=$this->getRoute();?>/<?=$id;?>?hash=<?=$hash;?>&pdf=1" class="button bt-left">PDF</a>
			<a onclick="return confirm('Действительно отправить по почте? Документ сейчас будет оправлена на <?= $package->client->mail;?>!');" href="/<?=$this->getRoute();?>/<?=$id;?>?hash=<?=$hash;?>&pdf=1&mail=1" class="button bt-right">@</a>
		</div>
		<div class="content">
			<?= $content; ?>
		</div>
	</body>
</html>
