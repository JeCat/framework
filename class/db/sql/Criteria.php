<?php
namespace org\jecat\framework\db\sql;

use org\jecat\framework\lang\Type;

use org\jecat\framework\lang\Exception;

class Criteria extends SubStatement
{
	public function __construct(Restriction $aWhere = null){
		if($aWhere !== null){
			$this->setRestriction($aWhere);
		}
	}
	
	/**
	 * 把所有条件拼接成字符串,相当于把这个对象字符串化
	 * 
	 * @param $aState 
	 * @return string
	 */
	public function makeStatement(StatementState $aState) {
		
		$sStatement = '' ;
		
		if($this->aWhere)
		{
			$sStatement = ' WHERE ' . $this->aWhere->makeStatement($aState);
		}
		if( $this->arrGroupByClms )
		{
			$sStatement.= ' GROUP BY ' ;
			foreach($this->arrGroupByClms as $i=>$sColumn)
			{
				if($i>0)
				{
					$sStatement.= ', ' ;
				}
				$sStatement.= $this->transColumn($sColumn,$aState) ;
			}
		}
		if( $this->aOrder )
		{
			$sStatement .= ' ' . $this->aOrder->makeStatement($aState);
		}
		
		$sStatement .= ' ' . $this->makeStatementLimit($aState) ;
		
		return $sStatement;
	}
	
	public function checkValid($bThrowException = true) {
		return true;
	}
	
	/**
	 *  设置limit条件
	 * @param int $nLimitLen limit 长度
	 * @param int $sLimitFrom limit 开始
	 */
	public function setLimit($nLimitLen , $sLimitFrom = 0){
		$this->setLimitLen($nLimitLen);
		$this->setLimitFrom($sLimitFrom);
		return $this ;
	}
	
	public function makeStatementLimit(StatementState $aState){
		if($this->nLimitLen===-1)
		{
			return '';
		}		
		$sLimit = ' LIMIT ';
		if($aState->supportLimitStart() and $this->sLimitFrom != 0)
		{
			$sLimit .= $this->sLimitFrom . ', ';
		}
		$sLimit .= $this->nLimitLen ;
		return $sLimit;
	}
	
	public function setLimitFrom($sLimitFrom) {
		$this->sLimitFrom = (int)$sLimitFrom;
		return $this ;
	}
	public function limitFrom()
	{
		return $this->sLimitFrom;
	}
	
	public function setLimitLen($nLimitLen){
		$this->nLimitLen = $nLimitLen;
		return $this ;
	}
	
	public function limitLen()
	{
		return $this->nLimitLen;
	}
	
	public function setWhere(Restriction $aWhere){
		$this->aWhere = $aWhere;
		return $this ;
	}
	
	/**
	 * @return Restriction
	 */
	public function where($bAutoCreate=true){
		if( !$this->aWhere and $bAutoCreate )
		{
			$this->aWhere = $this->statementFactory()->createRestriction();
		}
		return $this->aWhere ;
	}
	
	public function addOrderBy($sColumn,$bDesc=true)
	{
		$this->orders()->add($sColumn,$bDesc) ;
		return $this ;
	}
	
	public function setOrders(Order $aOrder){
		$this->aOrder = $aOrder;
		return $this ;
	}
	
	/**
	 * 
	 * @return Order 
	 */
	public function orders($bAutoCreate=true){
		if(!$this->aOrder and $bAutoCreate)
		{
			$this->aOrder = $this->statementFactory()->createOrder();
		}

		return $this->aOrder;
	}

	function __clone()
	{
	    if($this->aOrder !== null) $this->aOrder = clone $this->aOrder;
	    if($this->aWhere !== null) $this->aWhere = clone $this->aWhere;
	}
	
	// -- group by --
	public function addGroupBy($columns)
	{
		$this->arrGroupByClms = array_merge(Type::toArray($columns,Type::toArray_emptyForNull) ,Type::toArray($this->arrGroupByClms,Type::toArray_emptyForNull)) ;
		return $this ;
	}
	
	public function groupBy()
	{
		return $this->arrGroupByClms?: array() ;
	}
	
	public function clearGroupBy()
	{
		$this->arrGroupByClms = null ;
		return $this ;
	}

	
	private $aWhere = null;
	private $aOrder = null;
	private $sLimitFrom = 0;
	private $nLimitLen = 30;
	private $arrGroupByClms;
}
?>
