<?php
namespace org\jecat\framework\fs ;

use org\jecat\framework\lang\Type;
use org\jecat\framework\lang\Exception;
use org\jecat\framework\io\OutputStream;
use org\jecat\framework\io\InputStream;

class File extends FSO
{
	const CREATE_DEFAULT = 020664 ; 	// CREATE_RECURSE_DIR | 0664
	
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
		
		return InputStream::createInstance($hHandle) ;
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
		
		return OutputStream::createInstance($hHandle) ;
	}

	public function length()
	{
		return filesize($this->path()) ;
	}
	
	public function delete()
	{
		if( file_exists($sLocalPath=$this->path()) )
		{
			return unlink($sLocalPath) ;
		}
		
		else 
		{
			return false ;
		}
	}
	
	public function hash()
	{
		return md5($this->path()) ;
	}
	
	public function includeFile($bOnce=false,$bRequire=false)
	{
		if(!$bRequire)
		{
			if($bOnce)
			{
				return include_once $this->path() ;
			}
			else 
			{
				return include $this->path() ;
			}
		}
		
		else 
		{
			if($bOnce)
			{
				return require_once $this->path() ;
			}
			else 
			{
				return require $this->path() ;
			}
		}
	}
	

	public function create($nMode=Folder::CREATE_FOLDER_DEFAULT)
	{
		$sLocalPath = $this->path() ;
		
		$sLocalDirPath = dirname($sLocalPath) ;
		if( !is_dir($sLocalDirPath) )
		{
			if( $nMode & Folder::CREATE_RECURSE_DIR )
			{
				$nOldMark = umask(0) ;
				if( !mkdir($sLocalDirPath,Folder::CREATE_DEFAULT&0777,true) )
				{
					umask($nOldMark) ;
					return false ;
				}
				umask($nOldMark) ;
			}
			else 
			{
				return false ;
			}
		}
		
		if( !$hHandle=fopen($sLocalPath,'w') )
		{
			return false ;
		}
		
		fclose($hHandle) ;
		
		$nMask = $nMode&0777 ;
		if( $this->perms()!=$nMask and $this->canWrite() )
		{
			$nOldMark = umask(0) ;
			chmod( $sLocalPath, $nMask ) ;
			umask($nOldMark) ;
		}
		
		return true ;
	}

	public function exists()
	{
		return is_file($this->path());
	}
}
?>
