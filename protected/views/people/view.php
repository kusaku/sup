<!--		Возвращаем форму для создания/редактирования человека		-->

<div class="newClientWindow" style="margin-bottom: 5px;">
    <div class="clientHead">
        Информация о клиенте
    </div>
    <form action="/people/save" method="POST" name="megaform">
        <div style="float:left;width:275px;">
            <input type="hidden" name="id" value="<?=$people->id?>"><input type="hidden" name="parent_id" value="<?=$people->parent_id?>">
            <label>
                Имя<span class="orange">*</span>: 
            </label>
            <input type="text" value="<?=$people->fio?>" size="26" name="fio"/>
            <label>
                E-mail<span class="orange">*</span>: 
            </label>
            <input type="text" value="<?=$people->mail?>" size="26" name="mail"/>
            <label>
                Телефон: 
            </label>
            <input type="text" value="<?=$people->phone?>" size="26" name="phone"/>
            <label>
                Организация: 
            </label>
            <input type="text" value="<?=$people->firm?>" size="26" name="firm"/>
            <label>
                Город: 
            </label>
            <input type="text" value="<?=$people->state?>" size="26" name="state"/>
            <label>
                Примечание: 
            </label>
            <textarea name="descr" rows="3" cols="12"><?= $people->descr?></textarea>
        <?php if (isset($people->attr['bm_id']) and $bm_id = $people->attr['bm_id']->value[0]->value): ?>
        <a style="padding:5px 20px;display:block;" onclick="bmOpen(<?= $people->primaryKey; ?>)" href="#">Открыть в BM (id <?= $bm_id ?>)</a>
        <?php else : ?>
        <a style="padding:5px 20px;display:block;" onclick="bmRegister(<?= $people->primaryKey; ?>)" href="#">Зарегистрировать в BM</a>
        <?php endif; ?>			
        </div>
        <div style="float:right;width:275px;margin-top:10px;">
            <div class="accordion">
                <?php foreach (Attributes::model()->with('children')->getGroups() as $group): ?>
                <h3>
                    <?= $group->name?>
                </h3>
                <div style="display:none;">
                    <?php foreach ($group->children as $attr): ?>
                    <label for="<?= $attr->type ?>" class="column1">
                        <?= $attr->name?>
                    </label>
                    <input type="text" size="70" id="<?= $attr->type ?>" value="<?= isset($people->attr[$attr->type]) ? $people->attr[$attr->type]->value[0]->value : '' ?>" name="attr[<?= $attr->primaryKey ?>]">
                    <? endforeach; ?>
                </div>
                <? endforeach; ?>
            </div>
        </div>
    </form>
    <div class="buttons">
        <p>
            <span class="orange">* - обязательные поля</span>
        </p>
        <!-- <a href="javascript:alert('Пока не работает.');" class="plus" title="Сохранить клиента и добавить ему заказ"></a> --><a onClick="document.forms['megaform'].submit();" class="buttonSave">Сохранить</a>
        <a class="buttonCancel" onClick="hidePopUp()">Отмена</a>
    </div>
</div>