<?php
namespace jc\fs\imp ;

use jc\fs\IFile;
use jc\io\OutputStream;
use jc\io\InputStream;

class LocalFile extends LocalFSO implements IFile
{	
	/**
	 * @return io\IInputStream
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
	 * @return io\IOutputStream
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

	public function length()
	{
		return filesize($this->localPath()) ;
	}
	
	public function delete()
	{
		return unlink($this->localPath()) ;
	}
	
	public function copy($sToPath)
	{
		return $this->fileSystem()->copy($this->path(),$sToPath) ;
	}
	
	public function move($sToPath)
	{
		return $this->fileSystem()->move($this->path(),$sToPath) ;
	}
}
?>