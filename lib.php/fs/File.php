<?php
namespace jc\fs ;

use jc\io\InputStream;

class File extends FSO
{
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function extname()
	{
		$sName = name() ;
		$nDotIdx = strrpos($sName,'.') ;
		return ($nDotIdx===false)? '': substr($sName,$nDotIdx+1) ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function titlename()
	{
		$sName = name() ;
		$nDotIdx = strrpos($sName,'.') ;
		return ($nDotIdx===false)? $sName: substr($sName,0,$nDotIdx) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return InputStream
	 */
	public function openReader()
	{
		$hHandle = fopen($this->path(),'r') ;
		if( !$hHandle )
		{
			return null ;
		}
		
		return $this->create('InputStream','jc\\io',array($hHandle)) ;		
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return OutputStream
	 */
	public function openWriter($bAppend=false)
	{
		$hHandle = fopen($this->path(),$bAppend?'w':'a') ;
		if( !$hHandle )
		{
			return null ;
		}
		
		return $this->create('InputStream','jc\\io',array($hHandle)) ;
	}
	
}
?>