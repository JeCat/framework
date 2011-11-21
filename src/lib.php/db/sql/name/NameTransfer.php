<?php
namespace jc\db\sql\name ;

use jc\db\sql\StatementState;
use jc\db\sql\Statement;
use jc\util\FilterMangeger;

class NameTransfer 
{
	public function transColumn($sName,Statement $aStatement,StatementState $aState)
	{
		if( $this->aColumnNameFilter )
		{
			list($sName) = $this->aColumnNameFilter->handle($sName,$aStatement,$aState) ;
		}
		
		return $sName ;
	}
	
	public function transTable($sName,Statement $aStatement,StatementState $aState)
	{
		if( $this->aTableNameFilter )
		{
			list($sName) = $this->aTableNameFilter->handle($sName,$aStatement,$aState) ;
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

	/**
	 * 确保字符串被反引号包围 (如果字符串没有反引号包围 , 用反引号包围字符串)
	 * @param string 需要加上反引号的字符串
	 * @return string 加上反引号后的字符串
	 */
	static public function makeSureBackQuote($sName) {
		if (substr ( $sName, 0, 1 ) == "`" and substr ( $sName, - 1, 1 ) == "`") {
			return $sName;
		}else{
			return "`" . $sName . "`";
		}
	}
	
	private $aColumnNameFilter ;
	
	private $aTableNameFilter ;
}

?>
