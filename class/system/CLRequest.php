<?php
namespace jc\system ;

use jc\util\DataSrc;

class CLRequest extends Request
{
	public function __construct()
	{
		global $argv ;
		$this->addChild( new DataSrc($argv,true) ) ;
	}
	
	/**
	 * 定义命令行参数的名称
	 * 
	 * @param	$sName				string			参数名称
	 * @param	$bRequireValue	bool			
	 * @param	$aliases			string,array	参数别名
	 */
	public function defineParam($sName,$aliases=array(),$bRequireValue=true,$defaultValue=null)
	{
		$aliases  = (array)$aliases ;
		
		$this->arrParamNames[$sName] = array(
				$sName, $aliases, $bRequireValue, $defaultValue
		) ;
		
		$this->arrAliases[$sName] = & $this->arrParamNames[$sName] ;
		foreach($aliases as $sAlias)
		{
			$this->arrAliases[$sAlias] = & $this->arrParamNames[$sName] ;
		}
	}
	
	public function reparseParams()
	{
		if( empty($_SERVER['argv']) or !is_array($_SERVER['argv']) )
		{
			return ;
		}
		
		$this->clear() ;
		
		// set default value
		foreach($this->arrParamNames as $arrParam)
		{
			if($arrParam[3]!==null)
			{
				$this->set($arrParam[0], $arrParam[3]) ;
			}
		}
		
		// set input value
		$nUnnameParamIdx = 0 ;
		for( reset($_SERVER['argv']); $sParam=current($_SERVER['argv']); next($_SERVER['argv']) )
		{
			$sValue = null ;
			
			// like: --param=value
			if( preg_match('/^\-\-./',$sParam) )
			{
				if( strstr($sParam,'=')!==false )
				{
					list($sName,$sValue) = explode('=',$sParam,2) ;
				}
				
				else 
				{
					$sName = $sParam ;
						
					if( isset($this->arrAliases[$sName]) )
					{
						// 要求参数值
						if($this->arrAliases[$sName][2])
						{
							$sValue = next($_SERVER['argv']) ;
						}
						
						// 改参数不需要值
						else 
						{
							$sValue = $sName ; 
						}
					}
				}
				
				if( isset($this->arrAliases[$sName]) )
				{
					$this->set($this->arrAliases[$sName][0],$sValue) ;
				}
				else 
				{
					$this->set($sName,$sValue) ;
				}
			}
			
			// like: -p value
			else if( preg_match('/^\-./',$sParam) )
			{
				$sValue = $sParam ;
					
				if( isset($this->arrAliases[$sParam]) )
				{
					// 要求参数值
					if($this->arrAliases[$sParam][2])
					{
						$sValue = next($_SERVER['argv']) ;
					}
				}
				
				$this->set($sParam,$sValue) ;
			}
			
			// 普通
			else 
			{
				$this->set($nUnnameParamIdx++,$sParam) ;
			}
		}
	}
	
	
	private $arrParamNames = array() ; 
	
	private $arrAliases = array() ; 
}
?>