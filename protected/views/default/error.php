<div style="position:absolute;top:50%;left:50%;width:600px;height:400px;margin:-200px -300px;border:1px dashed #000;">
	<div style="margin: 20px;">
		<img class="login_form_img" src="/images/logo.png"/><h2>Ошибка <?php echo $code; ?></h2>
		<pre class="error"><?php echo CHtml::encode($message); ?></pre>
	</div>
</div>
