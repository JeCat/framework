<?php
namespace jc\db ;

use jc\lang\Exception as JcException ;

class ExecuteException extends JcException
{
	public function __construct( IDriver $aDevice, $sSql, $nDeviceErrorNo, $nDeviceErrorMsg, \Exception $aCause=null )
	{
		$this->aDevice = $aDevice ;
		$this->sSql = $sSql ;
		$this->nDeviceErrorNo = $nDeviceErrorNo ;
		$this->nDeviceErrorMsg = $nDeviceErrorMsg ;
		
		$sMessage = "数据库在执行SQL语句时发生了错误(code %d): %s ; 正在执行的 sql 是: %s" ;
		$Argvs = array($nDeviceErrorNo,$nDeviceErrorMsg,$sSql) ;
		
		parent::__construct($sMessage,$Argvs,$aCause) ;
	}

	public function device()
	{
		return $this->aDevice ;
	}
	public function sql()
	{
		return $this->$sSql ;
	}
	public function deviceErrorNo()
	{
		return $this->$nDeviceErrorNo ;
	}
	public function deviceErrorMsg()
	{
		return $this->$nDeviceErrorMsg ;
	}
	
	
	private $aDevice ;
	private $sSql ;
	private $nDeviceErrorNo ;
	private $nDeviceErrorMsg ;
}

?>