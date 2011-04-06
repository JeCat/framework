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
		return FSO::extname(name()) ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function titlename()
	{
		return FSO::titlename(name()) ;
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