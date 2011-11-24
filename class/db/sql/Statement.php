<?php 
namespace jc\db\sql ;

use jc\db\sql\name\NameTransferFactory;
use jc\db\sql\name\NameTransfer;
use jc\lang\Object;

abstract class Statement extends Object
{
	abstract public function makeStatement(StatementState $aState) ;
	
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
	protected function transColumn($sColumn,StatementState $aState)
	{
		// 自由输入的字段名，省略关系片段中第一个prototype的名字
		if( substr($sColumn,0,1)=='`' )
		{
			return $sColumn ;
		}
		else 
		{
			return ($aNamer=$this->nameTransfer())?
						$aNamer->transColumn($sColumn,$this,$aState):
						'`'.$sColumn.'`' ;
		}
	}
	
	/**
	 * 
	 * 对直接量进行转化,使其在组合后的sql语句中合法.
	 * @param mix $value 条件语句中的直接量
	 * @return string 
	 */
	protected function transValue($value)
	{
		if (is_string ( $value ))
		{
			return "'" . addslashes ( $value ) . "'";
		}
		else if (is_numeric ( $value ))
		{
			return "'" .$value. "'";
		}
		else if (is_bool ( $value ))
		{
			return $value ? "'1'" : "'0'";
		}
		else if ($value === null)
		{
			return "NULL";
		}
		else
		{
			return "'" . strval ( $value ) . "'";
		}
	}
	
	private $aNameTransfer ;
	
	private $aStatementFactory ;
}


?>