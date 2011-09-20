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
		$sStatement = $this->restriction()->makeStatement($bFormat);
		
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
	
	public function setLimit($nLimitLen , $nLimitFrom = 0){
		$this->nLimitLen = $nLimitLen;
		$this->nLimitFrom = $nLimitFrom;
	}
	
	public function limit(){
		$sLimit = 'Limit ';
		if($this->bEnableLimitStart and $this->nLimitFrom != 0){
			$sLimit .= $this->nLimitFrom . ',';
		}
		$sLimit .= $this->nLimitLen;
		return $sLimit;
	}
	
	public function setLimitFrom($nLimitFrom) {
		if($this->bEnableLimitStart === true){
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
	
	public function restriction(){
		if($this->aRestriction != null){
			return $this->aRestriction;
		}else{
			return $this->aRestriction = new Restriction();
		}
	}
	
	public function setOrder(Order $aOrder){
		$this->aOrder = $aOrder;
	}
	
	public function order(){
		if($this->aOrder != null){
			return $this->aOrder;
		}else{
			return $this->aOrder = new Order($sColumn);
		}
	}
	
	private $aRestriction = null;
	private $aOrder = null;
	private $nLimitFrom = 0;
	private $nLimitLen = 0;
	private $bEnableLimitStart = false;
}
?>