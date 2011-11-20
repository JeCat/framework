<?php
namespace jc\db\sql;

use jc\lang\Exception;

class Criteria extends SubStatement
{
	public function __construct(Restriction $aRestriction = null){
		if($aRestriction !== null){
			$this->setRestriction($aRestriction);
		}
	}
	
	/**
	 * 把所有条件拼接成字符串,相当于把这个对象字符串化
	 * 
	 * @param $bFormat 是否添加换行以便阅读
	 * @return string
	 */
	public function makeStatement(StatementState $aState) {
		
		$sStatement = '' ;
		
		if($this->aRestriction)
		{
			$sStatement = ' WHERE ' . $this->aRestriction->makeStatement($aState);
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
			$sLimit .= $this->transColumn($this->sLimitFrom,$aState) . ', ';
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
	
	public function setRestriction(Restriction $aRestriction){
		$this->aRestriction = $aRestriction;
		return $this ;
	}
	
	/**
	 * @return Restriction
	 */
	public function restriction($bAutoCreate=true){
		if( !$this->aRestriction and $bAutoCreate )
		{
			$this->aRestriction = $this->statementFactory()->createRestriction();
		}
		return $this->aRestriction ;
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
	    if($this->aRestriction !== null) $this->aRestriction = clone $this->aRestriction;
	}
	
	// -- group by --
	public function addGroupBy($columns=null)
	{
		if( empty($columns) )
		{
			$this->arrGroupByClms = null ;
			return ;
		}
		
		else
		{
			$this->arrGroupByClms = (array) $columns ;
		}
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

	
	private $aRestriction = null;
	private $aOrder = null;
	private $sLimitFrom = 0;
	private $nLimitLen = 30;
	private $arrGroupByClms ;
}
?>
