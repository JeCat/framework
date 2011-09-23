<?php
namespace jc\db\sql;

use jc\lang\Exception;
use jc\db\sql\IStatement;
use jc\db\sql\Restriction;
use jc\db\sql\Order;

class Criteria implements IStatement {
	/**
	 * 把所有条件拼接成字符串,相当于把这个对象字符串化
	 * 
	 * @param $bFormat 是否添加换行以便阅读
	 * @return string
	 */
	public function makeStatement($bFormat = false) {
		if($this->aRestriction)
		{
			$sStatement = ' WHERE ' . $this->aRestriction->makeStatement($bFormat);
		}
		if( $this->aOrder )
		{
			$sStatement .= ' ' . $this->aOrder->makeStatement($bFormat);
		}
		
		$sStatement .= ' ' . $this->makeStatementLimit() ;
		
		return $sStatement;
	}
	
	public function checkValid($bThrowException = true) {
		return true;
	}
	
	/**
	 * 参数为true时允许设置limit的开始值,比如:"LIMIT 5,10"
	 * 参数为false时只能设置limit的结束值,比如:"LIMIT 30" 
	 * @param boolen $bEnableLimitStart
	 */
	public function setEnableLimitStart($bEnableLimitStart) {
		$this->bEnableLimitStart = $bEnableLimitStart;
	}
	
	/**
	 *  设置limit条件
	 *  默认是只设置limit长度,如果需要在select语句中设置limit区间,就使用setEnableLimitStart()方法打开from锁定,
	 *  在通过本方法的第2个参数设置from值,未打开锁定就设置from值会报异常
	 * @param int $nLimitLen limit 长度
	 * @param int $nLimitFrom limit 开始
	 */
	public function setLimit($nLimitLen , $nLimitFrom = 0){
		$this->setLimitLen($nLimitLen);
		$this->setLimitFrom($nLimitFrom);
	}
	
	public function makeStatementLimit($bFormat = false){
		if($this->nLimitLen === -1){
			return '';
		}
		$sLimit = ' LIMIT ';
		if($this->bEnableLimitStart and $this->nLimitFrom != 0){
			$sLimit .= $this->nLimitFrom . ',';
		}
		$sLimit .= $this->nLimitLen;
		return $sLimit;
	}
	
	public function setLimitFrom($nLimitFrom) {
		if($this->bEnableLimitStart === true OR $nLimitFrom == 0){
			$this->nLimitFrom = (int)$nLimitFrom;
		}else{
			throw new Exception('在不允许使用limit from的情况下尝试设置from的值,请确保使用了合法的sql语句,或者检查是否忘记打开允许使用limit from的标记');
		}
	}
	
	public function setLimitLen($nLimitLen){
		$this->nLimitLen = $nLimitLen;
	}
	
	
	public function setRestriction(Restriction $aRestriction){
		$this->aRestriction = $aRestriction;
	}
	
	/**
	 * @return Restriction
	 */
	public function restriction($bCreate=true){
		if( !$this->aRestriction and $bCreate )
		{
			$this->aRestriction = new Restriction();
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
	public function order(){
		if($this->aOrder != null){
			return $this->aOrder;
		}else{
			return $this->aOrder = new Order();
		}
	}
	
	private $aRestriction = null;
	private $aOrder = null;
	private $nLimitFrom = 0;
	private $nLimitLen = 30;
	private $bEnableLimitStart = false;
}
?>