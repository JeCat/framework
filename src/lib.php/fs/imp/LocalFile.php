<?php
namespace jc\fs\imp ;

use jc\lang\Type;

use jc\lang\Exception;

use jc\fs\IFolder;

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
		$hHandle = fopen($this->localPath(),'r') ;
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
		$hHandle = fopen($this->localPath(),$bAppend?'a':'w') ;
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
	
	public function hash()
	{
		return md5($this->localPath()) ;
	}
	
	public function includeFile($bOnce=false,$bRequire=false)
	{
		if($bRequire)
		{
			if($bOnce)
			{
				return include_once $this->localPath() ;
			}
			else 
			{
				return include $this->localPath() ;
			}
		}
		
		else 
		{
			if($bOnce)
			{
				return require_once $this->localPath() ;
			}
			else 
			{
				return require $this->localPath() ;
			}
		}
	}
	
	public function create($nMode=0644)
	{
		$sLocalPath = $this->localPath() ;
		
		if( !$hHandle=fopen($sLocalPath,'w') )
		{
			return false ;
		}
		
		fclose($hHandle) ;
		
		chmod($sLocalPath, $nMode) ;
		
		return true ;
	}

}
?>