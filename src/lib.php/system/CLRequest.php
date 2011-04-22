<?php
namespace jc\system ;

use jc\util\DataSrc ;

class CLRequest extends DataSrc implements IRequest
{
	public function __construct()
	{
		global $argv ;
		$this->addChild( new DataSrc($argv,true) ) ;
	}
	
	public function defineParam($sParam,$arrCLNames,$DefaultValue=null)
	{
		$arrCLNames = (array) $arrCLNames ;
		foreach($arrCLNames as $sCLName)
		{
			$this->arrCLNames[$sCLName] = $sParam ;
		}

		if($DefaultValue!==null)
		{
			$this->set($sParam,$DefaultValue) ;
		}
	}
	
	public function reparseParams()
	{
		global $argv ;
		
		if( empty($argv) or !is_array($argv) )
		{
			return ;
		}
		
		for($i=0;$i<count($argv);$i++)
		{
			if( array_key_exists($argv[$i],$this->arrCLNames) )
			{
				$sParamName = $this->arrCLNames[ $argv[$i] ] ;
				if(isset($argv[++$i]))
				{
					$this->set( $sParamName, $argv[$i] ) ;
				}			
			}
		}
	}
	
	
	
	private $arrCLNames = array() ; 
}
?>