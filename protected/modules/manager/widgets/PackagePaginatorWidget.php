<?php 
/**
 * Указанный виджет строит пагинацию
 */
class PackagePaginatorWidget extends CWidget {
	public $search;
	public $current;
	public  $total;
	public $start;
	public $finish;

	public function run() {
		$this->start = max(0, $this->current - 10);
		$this->finish = min($this->total, $this->current + 10);
		$this->render('PackagePaginator');
	}
}
