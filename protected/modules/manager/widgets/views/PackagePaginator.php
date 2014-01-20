<?php
/**
 * @var PackagePaginatorWidget $this
 */
echo CHtml::openTag('div',array('class'=>'pages'));
for ($page = $this->start; $page < $this->finish; $page++) {
	if($page==$this->current) {
		echo CHtml::link($page+1,'#',array('class'=>'paginator current','onclick'=>"loadData('".$this->search."', '".$page."')"));
	} else {
		echo CHtml::link($page+1,'#',array('class'=>'paginator','onclick'=>"loadData('".$this->search."', '".$page."')"));
	}
}
echo CHtml::closeTag('div');
