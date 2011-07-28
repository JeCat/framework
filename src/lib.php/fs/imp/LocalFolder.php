<?php
namespace jc\fs\imp ;

use jc\fs\IFolder;

class LocalFolder extends LocalFSO implements IFolder
{
	/**
	 * @return \IFSO
	 */
	public function findFile($sPath)
	{
		return $this->fileSystem()->findFile(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
		) ;	
	}

	/**
	 * @return \IFSO
	 */
	public function findFolder($sPath)
	{
		return $this->fileSystem()->findFolder(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
		) ;	
	}
	
	public function createFile($sPath)
	{
		return $this->fileSystem()->createFile(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
		) ;
	}
	
	public function createFolder($sPath)
	{
		return $this->fileSystem()->createFolder(
				(substr($sPath,0,1)=='/')? $sPath: ($this->path().'/'.$sPath)
		) ;
	}
	
	/**
	 * 在文件系统内复制文件对象
	 * @param string,IFSO 		$from		被复制的源文件或目录，可以是表示路径的字符串或IFSO对象
	 * @param string,IFolder	$to			复制目标路径，可以是表示路径的字符串或IFolder对象; 当 $to 为 IFolder 对象时，源文件会被复制到 $to 内，而不会替换 $to
	 */
	public function copy($from,$to)
	{
		if( $from instanceof IFSO )
		{
			$sFromPath = $from->path() ;
		}
		else if( is_string($from) )
		{
			$sFromPath = $from ;
		}
		else 
		{
			throw new Exception('参数$from必须为 jc\\fs\\IFSO 或 表示路径的字符串格式，传入的参数格式为 %s',Type::detectType($from)) ;
		}
	
		if( $to instanceof IFolder )
		{
			$sToPath = $to->path().'/'.basename($sFromPath) ;
		}
		else if( is_string($to) )
		{
			$sToPath = $to ;
		}
		else 
		{
			throw new Exception('参数$to必须为 jc\\fs\\IFolder 或 表示路径的字符串格式，传入的参数格式为 %s',Type::detectType($from)) ;
		}
		
		list($aFromFS,$sFromInnerPath) = $this->localeFileSystem($sFromPath,true) ;
		list($aTOFS,$sToInnerPath) = $this->localeFileSystem($sToPath,true) ;

		return $aFromFS->copyOperation($aFromFS,$aTOFS,$sToInnerPath) ;
	}
	
	/**
	 * 在文件系统内移动文件对象
	 * @param string,IFSO 		$from		被移动的文件或目录，可以是表示路径的字符串或IFSO对象
	 * @param string,IFolder	$to			移动目标路径，可以是表示路径的字符串或IFolder对象; 当 $to 为 IFolder 对象时，源文件会被移动到 $to 内，而不会替换 $to
	 */
	public function move($from,$to)
	{
		if( $from instanceof IFSO )
		{
			$sFromPath = $from->path() ;
		}
		else if( is_string($from) )
		{
			$sFromPath = $from ;
		}
		else 
		{
			throw new Exception('参数$from必须为 jc\\fs\\IFSO 或 表示路径的字符串格式，传入的参数格式为 %s',Type::detectType($from)) ;
		}
	
		if( $to instanceof IFolder )
		{
			$sToPath = $to->path().'/'.basename($sFromPath) ;
		}
		else if( is_string($to) )
		{
			$sToPath = $to ;
		}
		else 
		{
			throw new Exception('参数$to必须为 jc\\fs\\IFolder 或 表示路径的字符串格式，传入的参数格式为 %s',Type::detectType($from)) ;
		}
		
		list($aFromFS,$sFromInnerPath) = $this->localeFileSystem($sFromPath,true) ;
		list($aTOFS,$sToInnerPath) = $this->localeFileSystem($sToPath,true) ;

		return $aFromFS->moveOperation($aFromFS,$aTOFS,$sToInnerPath) ;
	}
	
	
	/**
	 * @return \Iterator
	 */
	public function iterator()
	{
		
	}
} 



?>