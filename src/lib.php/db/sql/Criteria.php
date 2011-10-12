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
	public function makeStatement($bFormat = false,$bEnableLimitStart=false) {
		
		$sStatement = '' ;
		
		if($this->aRestriction)
		{
			$sStatement = ' WHERE ' . $this->aRestriction->makeStatement($bFormat);
		}
		if( $this->arrGroupByClms )
		{
			$sStatement.= ' GROUP BY ' . implode(', ',$this->arrGroupByClms) ;
		}
		if( $this->aOrder )
		{
			$sStatement .= ' ' . $this->aOrder->makeStatement($bFormat);
		}
		
		$sStatement .= ' ' . $this->makeStatementLimit($bFormat,$bEnableLimitStart) ;
		
		return $sStatement;
	}
	
	public function checkValid($bThrowException = true) {
		return true;
	}
	
	/**
	 *  设置limit条件
	 * @param int $nLimitLen limit 长度
	 * @param int $nLimitFrom limit 开始
	 */
	public function setLimit($nLimitLen , $nLimitFrom = 0){
		$this->setLimitLen($nLimitLen);
		$this->setLimitFrom($nLimitFrom);
	}
	
	public function makeStatementLimit($bFormat = false,$bEnableLimitStart=false){
		$nLimitLen = $this->limitLen();
		$nLimitFrom = $this->limitFrom();
		if($nLimitLen === -1){
			return '';
		}
		$sLimit = ' LIMIT ';
		if($bEnableLimitStart and $nLimitFrom != 0){
			$sLimit .= $nLimitFrom . ',';
		}
		$sLimit .= $nLimitLen;
		return $sLimit;
	}
	
	public function setLimitFrom($nLimitFrom) {
		$this->nLimitFrom = (int)$nLimitFrom;
	}
	public function limitFrom()
	{
		return $this->nLimitFrom;
	}
	
	public function setLimitLen($nLimitLen){
		$this->nLimitLen = $nLimitLen;
	}
	
	public function limitLen()
	{
		return $this->nLimitLen;
	}
	
	public function setRestriction(Restriction $aRestriction){
		$this->aRestriction = $aRestriction;
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
	
	public function setOrder(Order $aOrder){
		$this->aOrder = $aOrder;
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

	function __clone(){
	    if($this->aOrder !== null) $this->aOrder = clone $this->aOrder;
	    if($this->aRestriction !== null) $this->aRestriction = clone $this->aRestriction;
	}
	
	public function setGroupBy($columns=null)
	{
		if( empty($columns) )
		{
			$this->arrGroupByClms = null ;
			return ;
		}
		
		else
		{
			$this->arrGroupByClms = (array) $columns ;
			foreach($this->arrGroupByClms as &$sColumn)
			{
				$sColumn = $this->transColumn($sColumn) ;
			}
		}
	}
	
	public function groupBy()
	{
		return $this->arrGroupByClms ;
	}
	
	private $aRestriction = null;
	private $aOrder = null;
	private $nLimitFrom = 0;
	private $nLimitLen = 30;
	private $arrGroupByClms = array() ;
}
?>
