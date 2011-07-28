<?php
namespace jc\fs ;

use jc\lang\Exception;

use jc\lang\Type;

use jc\lang\Object;

abstract class FileSystem extends Object
{
	/**
	 * 定位一个路径具体所属的文件系统
	 * 返回所属的文件系统对象 和 在该文件系统对象内部的路径
	 */
	protected function localeFileSystem($sPath,$bRecurse=false)
	{		
		// 统一为绝对路径
		if(substr($sPath,0,1)!='/')
		{
			$sPath = '/'.$sPath ;
		}
		
		foreach($this->arrMounteds as $sMountPoint=>$aFileSystem)
		{
			$nMountPointLen = strlen($sMountPoint) ;
			if( substr($sPath,0,$nMountPointLen)==$sMountPoint and (strlen($sPath)==$nMountPointLen or substr($sPath,$nMountPointLen,1)=='/') )
			{
				if($bRecurse)
				{
					return $this->localeFileSystem($aFileSystem,substr($sPath,$nMountPointLen)) ;
				}
				else 
				{
					return array($aFileSystem,substr($sPath,$nMountPointLen)) ;
				}
			}
		}
		
		return array($this,$sPath) ;
	}
	
	/**
	 * @return IFSO
	 */
	public function findFile($sPath)
	{
		// 是否在挂载的文件系统中
		list($aFileSystem,$sInnerPath) = $this->localeFileSystem($sPath) ;
		if($aFileSystem!==$this)
		{
			return $aFileSystem->findFile($sInnerPath) ;
		}
		
		//////////////
		$sFlyweightKey = $this->fsoFlyweightKey($sPath) ;
		
		if( !isset($this->arrFSOFlyweights[$sFlyweightKey]) )
		{
			$this->arrFSOFlyweights[$sFlyweightKey] = $this->createFileObject($sPath) ;
		}
		
		return $this->arrFSOFlyweights[$sFlyweightKey] ;
	}
	
	/**
	 * @return IFSO
	 */
	public function findFolder($sPath)
	{
		// 是否在挂载的文件系统中
		list($aMountFS,$sInnerPath) = $this->localeFileSystem($sPath) ;
		if($aMountFS!==$this)
		{
			return $aMountFS->findFolder($sInnerPath) ;
		}
		
		//////////////
		$sFlyweightKey = $this->fsoFlyweightKey($sPath) ;
		
		if( !isset($this->arrFSOFlyweights[$sFlyweightKey]) )
		{
			$this->arrFSOFlyweights[$sFlyweightKey] = $this->createFolderObject($sPath) ;
		}
		
		return $this->arrFSOFlyweights[$sFlyweightKey] ;
	}

	public function setFSOFlyweight($sPath,IFSO $aFSO=null)
	{	
		// 是否在挂载的文件系统中
		list($aFileSystem,$sInnerPath) = $this->localeFileSystem($sPath) ;
		if($aFileSystem!==$this)
		{
			return $aFileSystem->findFile($sInnerPath) ;
		}
		
		else 
		{
			$sFlyweightKey = $this->fsoFlyweightKey($sPath) ;
			
			if(!$aFSO)
			{
				unset($this->arrFSOFlyweights[$sFlyweightKey]) ;
			}
			else 
			{
				$this->arrFSOFlyweights[$sFlyweightKey] = $aFSO ;
				
				$aFSO->setInnerPath($sPath) ;
				$aFSO->setFileSystem($this) ;
			}
		}
	}
	
	public function mount($sPath,self $aFileSystem)
	{
		$sPath = self::formatPath($sPath) ;
		
		$aFileSystem->beMounted($this,$sPath) ;
		$this->arrMounteds[$sPath] = $aFileSystem ;
	}
	
	public function umount($sPath)
	{
		if( isset($this->arrMounteds[$sPath]) )
		{
			$this->arrMounteds[$sPath]->beMounted(null,'/') ;
			unset($this->arrMounteds[$sPath]) ;
		}
	}
	
	public function exists($sPath)
	{
		// 是否在挂载的文件系统中
		list($aMountFS,$sInnerPath) = $this->localeFileSystem($sPath) ;
		if($aMountFS!==$this)
		{
			return $aMountFS->exists($sInnerPath) ;
		}
		
		//////////////
		return $this->existsOperation($sPath) ;
	}
	
	public function isFile($sPath)
	{
		// 是否在挂载的文件系统中
		list($aMountFS,$sInnerPath) = $this->localeFileSystem($sPath) ;
		if($aMountFS!==$this)
		{
			return $aMountFS->isFile($sInnerPath) ;
		}
		
		//////////////
		return $this->isFileOperation($sPath) ;
	}
	
	public function isFolder($sPath)
	{
		// 是否在挂载的文件系统中
		list($aMountFS,$sInnerPath) = $this->localeFileSystem($sPath) ;
		if($aMountFS!==$this)
		{
			return $aMountFS->isFolder($sInnerPath) ;
		}
		
		//////////////
		return $this->isFolderOperation($sPath) ;
	}
	
	/**
	 * 在文件系统内复制文件对象
	 * @param string,IFSO 		$from		被复制的源文件或目录，可以是表示路径的字符串或IFSO对象
	 * @param string 			$sToPath	复制目标路径
	 */
	public function copy($from,$sToPath)
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
		
		list($aFromFS,$sFromInnerPath) = $this->localeFileSystem($sFromPath,true) ;
		list($aTOFS,$sToInnerPath) = $this->localeFileSystem($sToPath,true) ;

		return $aFromFS->copyOperation($aFromFS,$aTOFS,$sToInnerPath) ;
	}
	
	/**
	 * 在文件系统内移动文件对象
	 * @param string,IFSO 		$from		被移动的文件或目录，可以是表示路径的字符串或IFSO对象
	 * @param string 			$sToPath	移动目标路径
	 */
	public function move($from,$sToPath)
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
		
		list($aFromFS,$sFromInnerPath) = $this->localeFileSystem($sFromPath,true) ;
		list($aTOFS,$sToInnerPath) = $this->localeFileSystem($sToPath,true) ;

		return $aFromFS->moveOperation($aFromFS,$aTOFS,$sToInnerPath) ;
	}

	public function createFile($sPath,$nMode=0644)
	{
		// 是否在挂载的文件系统中
		list($aMountFS,$sInnerPath) = $this->localeFileSystem($sPath) ;
		if($aMountFS!==$this)
		{
			return $aMountFS->createFile($sInnerPath,$nMode) ;
		}
		
		//////////////
		return $this->createFileOperation($sPath,$nMode) ;
	}
	
	public function createFolder($sPath,$nMode=0755,$bRecursive=true)
	{
		// 是否在挂载的文件系统中
		list($aMountFS,$sInnerPath) = $this->localeFileSystem($sPath) ;
		if($aMountFS!==$this)
		{
			return $aMountFS->createFolder($sInnerPath,$nMode,$bRecursive) ;
		}
		
		//////////////
		return $this->createFolderOperation($sPath,$nMode,$bRecursive) ;
	}
	
	public function delete($sPath)
	{		
		// 是否在挂载的文件系统中
		list($aMountFS,$sInnerPath) = $this->localeFileSystem($sPath) ;
		if($aMountFS!==$this)
		{
			return $aMountFS->delete($sInnerPath) ;
		}
		
		// 
		if( $this->isFile($sPath) )
		{
			if( $this->deleteFileOperation($sPath) )
			{
				$sFlyweightKey = $this->fsoFlyweightKey($sPath) ;
				unset($this->arrFSOFlyweights[$sFlyweightKey]) ;
				
				return true ;
			} 
			else 
			{
				return false ;
			}
		}
		else if( $this->isFolder($sPath) )
		{
			if( $this->deleteFolderOperation($sPath) )
			{
				$sFlyweightKey = $this->fsoFlyweightKey($sPath) ;
				unset($this->arrFSOFlyweights[$sFlyweightKey]) ;
				
				return true ;
			}
			else 
			{
				return false ;
			}
		}
		
		return true ;
	}
		
	abstract public function iterator($sPath) ;
	
	
	////////////////////////////////////////////////////////////////////////////
	abstract protected function deleteFileOperation(&$sPath) ;
	
	abstract protected function deleteDirOperation(&$sPath) ;
	
	abstract protected function createFileOperation(&$sPath,&$nMode) ;
	
	abstract protected function createFolderOperation(&$sPath,&$nMode,&$bRecursive) ;
	
	abstract protected function createFileObject(&$sPath) ;
	
	abstract protected function createFolderObject(&$sPath) ;
	
	abstract protected function existsOperation(&$sPath) ;
	
	abstract protected function isFileOperation(&$sPath) ;
	
	abstract protected function isFolderOperation(&$sPath) ;
	
	abstract protected function copyOperation(&$sPath,FileSystem $aToFs,&$sToPath) ;
	
	abstract protected function moveOperation(&$sPath,FileSystem $aToFs,&$sToPath) ;
	
	public function mountPath()
	{
		return $this->sMountPath ;
	}
	
	/**
	 * @return FileSystem
	 */
	public function parentFileSystem()
	{
		return $this->aParentFileSystem ;
	}

	/**
	 * @return FileSystem
	 */
	public function rootFileSystem()
	{
		$aFileSystem = $this ;
		
		while( $aParent=$aFileSystem->parentFileSystem() )
		{
			$aFileSystem = $aParent ;
		}
		
		return $aFileSystem ;
	}
	
	public function beMounted(self $aParentFileSystem=null,$sPath)
	{
		$this->sMountPath = $sPath ;
		$this->aParentFileSystem = $aParentFileSystem ;
	}

	function isCaseSensitive()
	{
		return $this->bCaseSensitive ;
	}
	
	function setCaseSensitive($bCaseSensitive=true)
	{
		return $this->bCaseSensitive = $bCaseSensitive ;
	}

	static public function formatPath($sPath,$sPathSeparator=DIRECTORY_SEPARATOR)
	{
		// 统一、合并斜线
		$sPath = preg_replace('|[/\\\\]+|', '/', $sPath) ;
		
		$arrFolders = explode('/', $sPath) ;
		
		$arrFoldersStack = array() ;
		foreach($arrFolders as $nIdx=>$sFolderName)
		{
			if( $sFolderName=='.' )
			{
				continue ;
			}
			
			if($sFolderName=='..')
			{
				$sParentFoldre = array_pop($arrFoldersStack) ;
				
				// windows 盘符
				if( preg_match("|^[a-z]:$|i",$sParentFoldre) )
				{
					// 放回去
					array_push($arrFoldersStack,$sFolderName) ;
				}
				
				continue ;
			}
			
			array_push($arrFoldersStack,$sFolderName) ;
		}
		
		return implode($sPathSeparator, $arrFoldersStack) ;
	}
	
	protected function fsoFlyweightKey($sPath)
	{
		return $this->isCaseSensitive()? strtolower($sPath): $sPath ;
	}
	
	private $arrMounteds = array() ;
	private $bCaseSensitive = true ;
	private $sMountPath = '/' ;
	private $aParentFileSystem = null ;
	private $arrFSOFlyweights = array() ;
}


?>