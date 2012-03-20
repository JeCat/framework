<?php
namespace org\jecat\framework\db ;

use org\jecat\framework\db\driver\IDriver;
use org\jecat\framework\lang\Exception as JcException ;

class ExecuteException extends JcException
{
	public function __construct( DB $aDB, $sSql, $nDeviceErrorNo, $sDeviceErrorMsg, \Exception $aCause=null )
	{
		$this->aDevice = $aDB ;
		$this->sSql = $sSql ;
		$this->nDeviceErrorNo = $nDeviceErrorNo ;
		$this->sDeviceErrorMsg = $sDeviceErrorMsg ;
		
		$sMessage = "数据库在执行SQL语句时发生了错误(code %d): \r\n" ;
		$sMessage.= "\t%s ;\r\n" ;
		$sMessage.= "正在执行的 SQL 是: \r\n" ;
		$sMessage.= "\t%s" ;
		
		$Argvs = array($nDeviceErrorNo,$sDeviceErrorMsg,$sSql) ;
		
		parent::__construct($sMessage,$Argvs,$aCause) ;
	}

	public function device()
	{
		return $this->aDevice ;
	}
	public function sql()
	{
		return $this->sSql ;
	}
	public function deviceErrorNo()
	{
		return $this->nDeviceErrorNo ;
	}
	public function deviceErrorMsg()
	{
		return $this->sDeviceErrorMsg ;
	}

	public function isDuplicate()
	{
		// just for mysql
		return $this->deviceErrorNo()==1062 and strpos($this->deviceErrorMsg(),'Duplicate entry')===0 ;
	}
	
	public function duplicateKey()
	{
		// just for mysql
		if( preg_match("/Duplicate entry '.+?' for key '(.+?)'/i", $this->deviceErrorMsg(),$arrRes) )
		{
			return $arrRes[1] ;
		}
		else 
		{
			return null ;
		}
	}
	
	private $aDevice ;
	private $sSql ;
	private $nDeviceErrorNo ;
	private $sDeviceErrorMsg ;
}

?>