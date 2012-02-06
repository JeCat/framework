<?php
namespace org\jecat\framework\mvc\controller ;

use org\jecat\framework\util\IFilterMangeger;
use org\jecat\framework\util\DataSrc ;

class Request extends DataSrc
{
	public function get($sName,$default=null)
	{
		$value = parent::get($sName) ;
		
		// 尝试转换 xxx.ooo 为 xxx_ooo
		if( $value===null and strpos($sName,'.')!==false )
		{
			$value = parent::get(str_replace('.','_',$sName)) ;
		}
		
		// 使用默认值
		if( $value===null and $default!==null )
		{
			$value = $default ;
			$this->set($sName,$value) ;
		}
		
		return $value ;
	}
	
	public function has($sName)
	{
		if( !parent::has($sName) )
		{
			// 尝试转换 xxx.ooo 为 xxx_ooo
			if( strpos($sName,'.')===false or !parent::has(str_replace('.','_',$sName)) )
			{
				return false ;
			}
		}
		return true ;
	}
	
	/**
	 * @return IFilterMangeger
	 */
	public function filters()
	{
		return $this->aFilters ;
	}
	
	public function setFilters(IFilterMangeger $aFilters)
	{
		$this->aFilters = $aFilters ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @var org\jecat\framework\io\PrintSteam
	 */
	private $aPrinter ;
}

