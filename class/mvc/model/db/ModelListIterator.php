<?php
namespace org\jecat\framework\mvc\model\db ;

class ModelListIterator implements \Iterator
{
	public function __construct(ModelList $aModelList,array $arrModelIndexes=null,$bShareChild=false)
	{
		$this->aModelList = $aModelList ;
		if($arrModelIndexes===null)
		{
			$nModelCount = $this->aModelList->childrenCount() ;
			$this->arrModelIndexes = $nModelCount>0? range( 0, $nModelCount-1 ): array() ;
		}
		else
		{
			$this->arrModelIndexes = $arrModelIndexes ;
		}
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

