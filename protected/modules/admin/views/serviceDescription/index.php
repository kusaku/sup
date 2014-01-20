<?php foreach($models as $obServiceParent):?>
	<h2><?php echo $obServiceParent->name;?></h2>
	<table width="100%" class="services">
		<col width="40"/>
		<col width="150"/>
		<thead>
			<tr>
				<th colspan="2" class="devider">Основные данные</th>
				<th colspan="8">Для пользователя</th>
			</tr>
			<tr>
				<th>ID</th>
				<th class="devider">Название</th>
				<th>Название</th>
				<th>Категория</th>
				<th>Описание</th>
				<th>Содержимое</th>
				<th>Название для документов</th>
				<th>Ссылка</th>
				<th>Иконка</th>
				<th>Срок</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($obServiceParent->childs as $obService):?>
				<tr>
					<td><?php echo $obService->id?></td>
					<td class="devider"><a href="/admin/serviceDescription/edit/<?php echo $obService->id?>"><?php echo $obService->name?></a></td>
					<?php if($obDescription=$obService->description):?>
						<td class="short_text"><?php echo $obDescription->title?></td>
						<td class="short_text"><?php echo $obDescription->category?></td>
						<td class="short_text"><?php echo LangUtils::truncate(strip_tags($obDescription->description),100)?></td>
						<td class="short_text"><?php echo LangUtils::truncate(strip_tags($obDescription->content),100)?></td>
						<td class="short_text"><?php echo $obDescription->document_title?></td>
						<td class="short_text"><a href="<?php echo $obDescription->link?>" targer="_blank"><?php echo LangUtils::truncate($obDescription->link,20)?></a></td>
						<td><?php echo $obDescription->icon?></td>
						<td><?php echo $obDescription->days?></td>
					<?php else:?>
					<td colspan="8"></td>
					<?php endif;?>
				</tr>
			<?php endforeach?>
		</tbody>
	</table>
<?php endforeach?>
