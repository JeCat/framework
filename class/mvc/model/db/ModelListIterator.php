<?php
namespace org\jecat\framework\mvc\model\db ;

class ModelListIterator implements \Iterator
{
	public function __construct(ModelList $aModelList,array $arrModelIndexes=null)
	{
		$this->aModelList = $aModelList ;
		$this->arrModelIndexes = $arrModelIndexes===null? range( 0, $this->aModelList->childrenCount()-1 ): $arrModelIndexes ;
	}
	
	public function current ()
	{
		$nIndex = current($this->arrModelIndexes) ;
		return $this->aModelList->child($nIndex) ;
	}
	
	public function next ()
	{
		next($this->arrModelIndexes) ;
	}
	
	public function key ()
	{
		return key($this->arrModelIndexes) ;
	}
	
	public function valid ()
	{
		return current($this->arrModelIndexes) ;
	}
	
	public function rewind ()
	{
		reset($this->arrModelIndexes) ;
	}
	
	private $arrModelIndexes ;
}

