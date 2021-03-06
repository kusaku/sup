<?php if ($no_button): ?>
<!-- Возвращаем форму для создания сайта в рамках заказа -->
<input type="hidden" name="site_add_new" value="true"><input type="hidden" name="site_id" value="<?=$site->id;?>">URL: <input type="text" name="site_url" value="<?=$site->url;?>" onKeyUp="checkDomain()" id="site_url">
<table>
    <tr>
        <td style="padding-left: 5px;">
            Хостинг
            <br>
            <textarea name="site_host" cols="16" rows="5"><?= $site->host; ?></textarea>
        </td>
        <td style="padding-left: 5px;">
            FTP сервер
            <br>
            <textarea name="site_ftp" cols="16" rows="5"><?= $site->ftp; ?></textarea>
        </td>
        <td style="padding-left: 5px;">
            База данных
            <br>
            <textarea name="site_db" cols="16" rows="5"><?= $site->db; ?></textarea>
        </td>
    </tr>
</table>
<?php else : ?>
<!-- Возвращаем форму для создания/редактирования домена (сайта) -->
<div class="newClientWindow" style="margin-bottom: 5px; width:300px">
    <div class="clientHead">
        Информация о сайте
    </div>
    <form action="/manager/site/save" method="POST" name="site">
        <input type="hidden" name="site_id" value="<?= $site->id; ?>"><input type="hidden" name="client_id" value="<?= $site->client_id; ?>">
        <label>
            URL<span class="orange">*</span>: 
        </label>
        <input type="text" value="<?= $site->url;?>" size="26" name="site_url" onKeyUp="checkDomain()" id="site_url"/>
        <label>
            Host: 
        </label>
        <textarea name="site_host" rows="3" cols="12"><?= $site->host; ?></textarea>
        <label>
            FTP: 
        </label>
        <textarea name="site_ftp" rows="3" cols="12"><?= $site->ftp; ?></textarea>
        <label>
            Database: 
        </label>
        <textarea name="site_db" rows="3" cols="12"><?= $site->db; ?></textarea>
    </form>
    <div class="buttons">
        <p>
            <span class="orange">* - обязательные поля</span>
        </p>
        <a onClick="document.forms['site'].submit();" class="buttonSave">Сохранить</a>
        <a class="buttonCancel" onClick="hidePopUp();">Отмена</a>
    </div>
</div>
<?php endif; ?>