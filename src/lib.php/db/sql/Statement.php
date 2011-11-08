<?php 
namespace jc\db\sql ;

use jc\db\sql\name\NameTransferFactory;
use jc\db\sql\name\NameTransfer;
use jc\lang\Object;

abstract class Statement extends Object
{
	abstract public function makeStatement($bFormat=false) ;
	
	abstract public function checkValid($bThrowException=true) ;
	
	/**
	 * @return jc\db\sql\name\NameTransfer
	 */
	public function nameTransfer($bAutoCreate=true)
	{
		if( !$this->aNameTransfer and $bAutoCreate )
		{
			$this->aNameTransfer = NameTransferFactory::singleton()->create() ;
		}
		
		return $this->aNameTransfer ;
	}
	
	public function setNameTransfer(NameTransfer $aNameTransfer=null)
	{
		$this->aNameTransfer = $aNameTransfer ;
	}
	
	/**
	 * @return jc\db\sql\StatementFactory
	 */
	public function statementFactory($bAutoCreate=true)
	{
		if(!$this->aStatementFactory and $bAutoCreate)
		{
			$this->aStatementFactory = StatementFactory::singleton() ;
		}
		return $this->aStatementFactory ;
	}
	
	public function setStatementFactory(StatementFactory $aStatementFactory=null)
	{
		$this->aStatementFactory = $aStatementFactory ;
	}

	
	/**
	 * 
	 * 对字段名进行转化,使其在组合后的sql语句中合法.
	 * @param string $sColumn 字段名
	 * @return string 转化后的合法sql语句成分
	 */
	protected function transColumn($sColumn) {
		if( $aNamer = $this->nameTransfer() )
		{
			$sColumn = $aNamer->transColumn($sColumn,$this) ;
		}
		return NameTransfer::makeSureBackQuote ( strval($sColumn) );
	}
	
	/**
	 * 
	 * 对直接量进行转化,使其在组合后的sql语句中合法.
	 * @param mix $value 条件语句中的直接量
	 * @return string 
	 */
	protected function tranValue($value) {
		if (is_string ( $value )) {
			$sValue = "'" . addslashes ( $value ) . "'";
		}else if (is_numeric ( $value )) {
			$sValue = "'" . strval ( $value ) . "'";
		} else if (is_bool ( $value )) {
			$sValue = $value ? "'1'" : "'0'";
		} else if ($value === null) {
			$sValue = "null";
		} else {
			$sValue = "'" . strval ( $value ) . "'";
		}
		return $sValue;
	}
	
	private $aNameTransfer ;
	
	private $aStatementFactory ;
}


?>
