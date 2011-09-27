<?php
namespace jc\db\sql\name ;

use jc\util\FilterMangeger;

class NameTransfer 
{
	public function transColumn($sName)
	{
		if( $this->aColumnNameFilter )
		{
			list($sName) = $this->aColumnNameFilter->handle($sName) ;
		}
		
		return $sName ;
	}
	
	public function transTable($sName)
	{
		if( $this->aTableNameFilter )
		{
			list($sName) = $this->aTableNameFilter->handle($sName) ;
		}
		
		return $sName ;
	}
	
	public function addColumnNameHandle($fnCallback,$arrArgvs=array())
	{
		$this->filter('Column',true)->add($fnCallback,$arrArgvs) ;
	}
	
	public function addTableNameHandle($fnCallback,$arrArgvs=array())
	{
		$this->filter('Table',true)->add($fnCallback,$arrArgvs) ;
	}
	
	/**
	 * @return jc\util\FilterMangeger
	 */
	public function filter($sType='Column',$bCreate=true)
	{
		$sType = ucfirst($sType) ;		
		$sProperty = "a{$sType}NameFilter" ;
		
		if( !$this->$sProperty and $bCreate )
		{
			$this->$sProperty = new FilterMangeger() ;
		}
		
		return $this->$sProperty ;
	}
	
	private $aColumnNameFilter ;
	
	private $aTableNameFilter ;
}

?>