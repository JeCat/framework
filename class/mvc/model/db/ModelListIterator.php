<?php
namespace org\jecat\framework\mvc\model\db ;

class ModelListIterator implements \Iterator
{
	public function __construct(ModelList $aModelList,array $arrModelIndexes=null,$bShareChild=false)
	{
		$this->aModelList = $aModelList ;
		$this->arrModelIndexes = $arrModelIndexes===null? range( 0, $this->aModelList->childrenCount()-1 ): $arrModelIndexes ;
		$this->bShareChild = $bShareChild ;
	}
	
	public function current ()
	{
		$nIndex = current($this->arrModelIndexes) ;
		return $this->aModelList->child($nIndex,$this->bShareChild) ;
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
		return current($this->arrModelIndexes)!==false ;
	}
	
	public function rewind ()
	{
		reset($this->arrModelIndexes) ;
	}
	
	private $arrModelIndexes ;
	
	private $bShareChild ;
}

