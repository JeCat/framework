<?php
namespace org\jecat\framework\db\sql\compiler ;

use org\jecat\framework\lang\Object;

class NameMapper extends Object
{
	public function __construct($bRegister=true)
	{
		if($bRegister)
		{
			SqlNameCompiler::singleton()->registerTableNameTranslaters( array($this,'transTableName') ) ;
		}
	}
	
	public function mapTableName($sOriginTable,$sToTable)
	{
		$this->arrTableMapping[$sOriginTable] = $sToTable ;
		return $this ;
	}
	
	public function transTableName($sTable,$sAlias,array & $arrToken,array & $arrTokenTree)
	{
		// echo $sTable,'>>',@$this->arrTableMapping[$sTable], '<br />' ;
		if( isset($this->arrTableMapping[$sTable]) )
		{
			$sTable = $this->arrTableMapping[$sTable] ;
		}
		return array( $sTable, $sAlias ) ;
	}
	
	private $arrTableMapping = array() ;
}


