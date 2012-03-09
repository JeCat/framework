<?php
namespace org\jecat\framework\fs\imp ;

use org\jecat\framework\fs\Folder;

use org\jecat\framework\lang\Type;

use org\jecat\framework\lang\Exception;

use org\jecat\framework\fs\IFolder;

use org\jecat\framework\fs\IFile;
use org\jecat\framework\io\OutputStream;
use org\jecat\framework\io\InputStream;

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
		if( file_exists($sLocalPath=$this->localPath()) )
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
		return md5($this->localPath()) ;
	}
	
	public function includeFile($bOnce=false,$bRequire=false)
	{
		if(!$bRequire)
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
	
	public function create($nMode=FileSystem::CREATE_FOLDER_DEFAULT)
	{
		$sLocalPath = $this->localPath() ;
		
		$sLocalDirPath = dirname($sLocalPath) ;
		if( !is_dir($sLocalDirPath) )
		{
			if( $nMode & FileSystem::CREATE_RECURSE_DIR )
			{
				$nOldMark = umask(0) ;
				if( !mkdir($sLocalDirPath,FileSystem::CREATE_FOLDER_DEFAULT&FileSystem::CREATE_PERM_BITS,true) )
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
		
		if( $this->perms()!=$nMode&FileSystem::CREATE_PERM_BITS and $this->canWrite() )
		{
			$nOldMark = umask(0) ;
			chmod( $sLocalPath, $nMode&FileSystem::CREATE_PERM_BITS ) ;
			umask($nOldMark) ;
		}
		
		return true ;
	}

	public function exists()
	{
		return is_file($this->localPath());
	}
}
?>
