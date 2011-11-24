<?php
namespace org\jecat\framework\db\recordset ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Object;

class PDORecordSet extends Object implements IRecordSet
{
	public function __construct(\PDOStatement $aPDOStatement)
	{
		$this->nRowCnt = $aPDOStatement->rowCount() ;
		$this->aPDOStatement = $aPDOStatement ;
		
		parent::__construct() ;
	}

	public function rowCount()
	{
		return $this->nRowCnt ;
	}

	public function rewind()
	{
		$this->nRowIdx = 0 ;
	}
	
	public function seek($nRow)
	{
		if( $nRow>=$this->nRowCnt )
		{
			throw new Exception(__METHOD__.'() 参数 $nRow 值超出了数据集的范围(%d)。',$this->nRowCnt) ;
		}
		
		$this->nRowIdx = $nRow ;
	}
	
	public function next()
	{
		$this->nRowIdx ++ ;
	}
	
	public function valid()
	{
		return $this->nRowIdx<$this->nRowCnt ;
	}
	
	public function current()
	{
		return $this->row( IRecordSet::currentRow ) ;
	}
	
	public function row( $nRow=IRecordSet::currentRow )
	{
		$nRow = intval($nRow) ;
		
		if( $nRow==IRecordSet::currentRow )
		{
			$nRow = $this->nRowIdx ;
		}
		
		if( $nRow>=$this->nRowCnt )
		{
			return null ;
		}
	
		$nValidIndex = count($this->arrRecordSet)-1 ;
		while( $nValidIndex<$nRow )
		{
			$this->arrRecordSet[++$nValidIndex] = $this->aPDOStatement->fetch() ;
		}
		 
		return $this->arrRecordSet[$nRow] ;
	}
	
	public function field($sFieldName,$nRow=IRecordSet::currentRow)
	{
		if( $nRow==IRecordSet::currentRow )
		{
			$nRow = $this->nRowIdx ;
		}
		
		if( $nRow>=$this->nRowCnt )
		{
			return null ;
		}
		
		$this->row($nRow) ;
		
		return isset($this->arrRecordSet[$nRow][$sFieldName])?
					$this->arrRecordSet[$nRow][$sFieldName]: null ;
	}
	
	public function iterator() 
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrRecordSet) ;
	}

	public function fieldIterator()
	{
		$arrRow = $this->current() ;
		return new \org\jecat\framework\pattern\iterate\ArrayIterator( $arrRow===null? array(): array_keys($arrRow) ) ;
	}
	
	public function key ()
	{
		return $this->nRowIdx ;
	}
	
	private $arrRecordSet = array() ;
	
	private $nRowCnt = 0 ;
	
	private $nRowIdx = 0 ;
	
	private $aPDOStatement = null ;
}

?>