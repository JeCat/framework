<?php
namespace org\jecat\framework\mvc\view\widget\paginator;
use org\jecat\framework\mvc\model\IPaginal;

class PaginaltorTester implements IPaginal{

	public function totalCount(){
		return $this->nTotalCount;	
	}
	
	public function setPagination($nPerPage, $nPageNum){
		
	}
	
	public function setTotalCount($n){
		$this->nTotalCount = $n;
	}
	
	private $nTotalCount = 10;
	
}