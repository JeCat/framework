<?php
namespace jc\fs ;

use jc\io\OutputStream;
use jc\io\InputStream;

class File extends FSObject
{
	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function extname()
	{
		return FSO::getExtname($this->name()) ;
	}

	/**
	 * Enter description here ...
	 * 
	 * @return string
	 */
	public function titlename()
	{
		return FSO::getTitlename($this->name()) ;
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
		
		return InputStream::createInstance($hHandle,$this->application()) ;
	}
	
	/**
	 * Enter description here ...
	 * 
	 * @return OutputStream
	 */
	public function openWriter($bAppend=false)
	{
		$hHandle = fopen($this->path(),$bAppend?'a':'w') ;
		if( !$hHandle )
		{
			return null ;
		}
		
		return OutputStream::createInstance($hHandle,$this->application()) ;
	}
	
}
?>