<?php
namespace jc\fs ;

use jc\lang\Type;

use jc\lang\Exception;
use jc\lang\Object;

abstract class FileSystem extends Object
{
	const file = 'jc\\fs\\IFile' ;
	const folder = 'jc\\fs\\IFolder' ;
	const unknow = 0 ;
	
	const CREATE_RECURSE_DIR = 020000 ;		// 创建文件或目录时，递归创建所属的目录
	const CREATE_ONLY_OBJECT = 040000 ;		// 只创建IFSO对象，不创建文件/目录，如果文件/目录不存在
	
	const CREATE_FILE_DEFAULT = 020664 ; 	// CREATE_RECURSE_DIR | 0664
	const CREATE_FOLDER_DEFAULT = 020775 ; 	// CREATE_RECURSE_DIR | 0775
	const CREATE_PERM_BITS = 0777 ;
	
	/**
	 * @return IFSO
	 * @retval $type参数		不存在	存在为File	存在为Folder
	 * @retval file			null	fileObject	null
	 * @retval folder		null	null		folderObject
	 * @retval unknow		null	fileObject	folderObject
	 */
	public function find($sPath,$type=self::unknow)
	{
		// 是否在挂载的文件系统中
		list($aFileSystem,$sInnerPath) = $this->localeFileSystem($sPath) ;
		if($aFileSystem!==$this)
		{
			return $aFileSystem->find($sInnerPath,$type) ;
		}

		$sFlyweightKey = $this->fsoFlyweightKey($sPath) ;
		if( !isset($this->arrFSOFlyweights[$sFlyweightKey]) )
		{
			if( $this->exists($sPath) )
			{
				if( $this->isFile($sPath) and ($type===self::file or $type===self::unknow) )
				{
					$this->arrFSOFlyweights[$sFlyweightKey] = $this->createFileObject($sPath) ; 
				}
				
				else if($this->isFolder($sPath) and ($type===self::folder or $type===self::unknow) )
				{
					$this->arrFSOFlyweights[$sFlyweightKey] = $this->createFolderObject($sPath) ; 
				}else{
					return null;
				}
			}else{
				return null;
			}
		}

		return $this->arrFSOFlyweights[$sFlyweightKey] ;
	}

	/**
	 * @return IFile
	 * @see find()
	 */
	public function findFile($sPath)
	{
		return $this->find($sPath,self::file) ;
	}

	/**
	 * @return IFolder
	 * @see find()
	 */
	public function findFolder($sPath)
	{
		return $this->find($sPath,self::folder) ;
	}
	
	/**
	 * 返回$aFSO所在的目录
	 * @return IFolder 。$aFSO在这个目录下。
	 */
	public function directory(IFSO $aFSO)
	{
		if( $aFSO->fileSystem()!==$this )
		{
			throw new Exception(
					"传入的 \$aFSO（%s） 对象不属于此文件系统。"
					, array($aFSO->path(),$this->url())
			) ;
		}
		
		$sInnerPath = $aFSO->innerPath() ;
		
		if($sInnerPath=='/')
		{
			$aFSO = $this->mounted() ;
			if( $aFSO )
			{
				return $aFSO->directory() ;
			}
			else 
			{
				return null ;
			}
		}
		
		$sInnerDirPath = dirname($sInnerPath) ;
		
		// 如果当前 FSO 对象是通过 FileSystem::CREATE_ONLY_OBJECT 标记创建的，上层目录可能并不存在，
		// 在这种情况下，也返回一个 FileSystem::CREATE_ONLY_OBJECT 创建的 IFolder 对象
		if( !$aDirectory=$this->findFolder($sInnerDirPath) )
		{
			$aDirectory = $aRootFs->createFolder($sInnerDirPath,FileSystem::CREATE_ONLY_OBJECT|FileSystem::CREATE_FOLDER_DEFAULT) ;
		}
		
		return $aDirectory ;
	}

	public function setFSOFlyweight($sPath,IFSO $aFSO=null)
	{	
		// 是否在挂载的文件系统中
		list($aFileSystem,$sInnerPath) = $this->localeFileSystem($sPath) ;
		if($aFileSystem!==$this)
		{
			return $aFileSystem->setFSOFlyweight($sInnerPath,$aFSO) ;
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
		
		if(!$aToFSO = $this->find($sPath))
		{
			$aToFSO = $this->createFolder($sPath,self::CREATE_ONLY_OBJECT) ;	
		}
		
		$aFileSystem->mountTo($aToFSO) ;
		
		$this->arrMounteds[$sPath] = $aFileSystem ;
	}

	public function umount($sPath)
	{
		if( isset($this->arrMounteds[$sPath]) )
		{
			$this->arrMounteds[$sPath]->mountTo(null) ;
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
	 * @param string,IFSO		$from	被复制的源文件或目录，可以是表示路径的字符串或IFSO对象
	 * @param string			$to		复制目标路径
	 */
	public function copy($from,$to)
	{
		if( $from instanceof IFSO )
		{
			$aFromFSO = $from ;
		}
		else if( is_string($from) )
		{
			$aFromFSO = $this->find($from) ;
			if($aFromFSO === null )
			{
				throw new Exception('参数$from对应源文件或目录不存在');
			}
		}
		else 
		{
			throw new Exception('参数$from必须为 jc\\fs\\IFSO 或 表示路径的字符串格式，传入的参数格式为 %s',Type::detectType($from)) ;
		}
		
		return $aFromFSO->copy($to) ;
	}

	/**
	 * 在文件系统内移动文件对象
	 * @param string,IFSO		$from	移动的文件或目录，可以是表示路径的字符串或IFSO对象
	 * @param string			$to		移动目标路径
	 */
	public function move($from,$to)
	{
		if( $from instanceof IFSO )
		{
			$aFromFSO = $from ;
		}
		else if( is_string($from) )
		{
			$aFromFSO = $this->find($from) ;
			if($aFromFSO === null )
			{
				throw new Exception('参数$from对应源文件或目录不存在');
			}
		}
		else 
		{
			throw new Exception('参数$from必须为 jc\\fs\\IFSO 或 表示路径的字符串格式，传入的参数格式为 %s',Type::detectType($from)) ;
		}
		
		return $aFromFSO->move($to) ;
	}

	/**
	 * @return IFile
	 * 如果存在同名Folder，会抛出异常
	 * 如果存在同名File，会直接返回；否则会创建此File后返回。
	 */
	public function createFile($sPath,$nMode=self::CREATE_FILE_DEFAULT)
	{
		// 是否在挂载的文件系统中
		list($aMountFS,$sInnerPath) = $this->localeFileSystem($sPath) ;
		if($aMountFS!==$this){
			return $aMountFS->createFile($sInnerPath,$nMode) ;
		}

		// 检查享元对象及类型是否匹配
		$sFlyweightKey = $this->fsoFlyweightKey($sPath) ;
		if( !isset($this->arrFSOFlyweights[$sFlyweightKey]) 
				or ! $this->arrFSOFlyweights[$sFlyweightKey] instanceof IFile )
		{
			if( $this->isFolder($sPath) )
			{
				throw new Exception('试图创建File，但由于存在同名Folder无法创建');
			}
			else
			{
				$aFile = $this->createFileObject($sPath);
				
				// 如果文件不存在，且没有要求 self::CREATE_ONLY_OBJECT ，则创建之
				if( !($nMode&self::CREATE_ONLY_OBJECT) and !$aFile->exists())
				{
					$aFile->create( $nMode );
				}
				$this -> arrFSOFlyweights[$sFlyweightKey] =$aFile;
			}
		}

		return $this->arrFSOFlyweights[$sFlyweightKey] ;
	}

	/**
	 * @return IFolder
	 * 如果存在同名File，会抛出异常
	 * 如果存在同名Folder，会直接返回；否则会创建此Folder后返回。
	 */
	public function createFolder($sPath,$nMode=self::CREATE_FOLDER_DEFAULT)
	{
		// 是否在挂载的文件系统中
		list($aMountFS,$sInnerPath) = $this->localeFileSystem($sPath) ;
		if($aMountFS!==$this){
			return $aMountFS->createFolder($sInnerPath,$nMode) ;
		}

		// 检查享元对象及类型是否匹配
		$sFlyweightKey = $this->fsoFlyweightKey($sPath) ;
		if( !isset($this->arrFSOFlyweights[$sFlyweightKey]) 
				or ! $this->arrFSOFlyweights[$sFlyweightKey] instanceof IFolder )
		{
			if( $this->isFile($sPath) )
			{
				throw new Exception('试图创建Folder，但由于存在同名File无法创建');
			}
			else
			{
				$aFolder = $this->createFolderObject($sPath);
				
				if( !($nMode&self::CREATE_ONLY_OBJECT) and !$aFolder->exists())
				{
					if( !$aFolder->create($nMode) )
					{
						return null;
					}
				}
				
				$this -> arrFSOFlyweights[$sFlyweightKey] =$aFolder;
			}
		}

		return $this->arrFSOFlyweights[$sFlyweightKey] ;
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
			if( $this->deleteDirOperation($sPath) )
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
	
	/**
	 * @return IFSO
	 */
	public function mounted()
	{
		return $this->aMounted ;
	}
	
	public function mountedPath()
	{
		return $this->aMounted? $this->aMounted->path(): null ;
	}

	/**
	 * @return FileSystem
	 */
	public function rootFileSystem()
	{
		$aFileSystem = $this ;
		
		while( $aMounted=$aFileSystem->mounted() )
		{
			$aFileSystem = $aMounted->fileSystem() ;
		}
		
		return $aFileSystem ;
	}

	function isCaseSensitive()
	{
		return $this->bCaseSensitive ;
	}
	
	function setCaseSensitive($bCaseSensitive=true)
	{
		return $this->bCaseSensitive = $bCaseSensitive ;
	}
		
	abstract public function iterator($sPath) ;
	
	abstract public function url() ;
		
	
	
	////////////////////////////////////////////////////////////////////////////

	/**
	 * 定位一个路径具体所属的文件系统
	 * 返回所属的文件系统对象 和 在该文件系统对象内部的路径
	 */
	protected function localeFileSystem($sPath,$bRecurse=false)
	{
		if( !is_string($sPath) )
		{
			throw new Exception("参数\$sPath必须为string格式，传入的格式为：%s",Type::detectType($sPath)) ;
		}
		
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
				$str=substr($sPath,$nMountPointLen);
				if(!$str) $str='/';
				if($bRecurse)
				{
					return $this->localeFileSystem($aFileSystem,$str) ;
				}
				else 
				{
					return array($aFileSystem,$str);
				}
			}
		}
		
		return array($this,$sPath) ;
	}
	
	protected function mountTo(IFSO $aMounted=null)
	{
		$this->aMounted = $aMounted ;
	}
	
	abstract protected function deleteFileOperation(&$sPath) ;
	
	abstract protected function deleteDirOperation(&$sPath) ;
	
	abstract protected function createFileObject(&$sPath) ;
	
	abstract protected function createFolderObject(&$sPath) ;
	
	abstract protected function existsOperation(&$sPath) ;
	
	abstract protected function isFileOperation(&$sPath) ;
	
	abstract protected function isFolderOperation(&$sPath) ;

	static public function formatPath($sPath)
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
		
		return implode('/', $arrFoldersStack) ;
	}
	
	/**
	 * 计算两个路径之间的相对路径
	 */
	static public function relativePath($sFromPath,$sToPath)
	{
		
	}
	
	protected function fsoFlyweightKey($sPath)
	{
		return $this->isCaseSensitive()? strtolower($sPath): $sPath ;
	}
	
	private $arrMounteds = array() ;
	private $bCaseSensitive = true ;
	private $sMountPath = '/' ;
	private $aMounted = null ;
	private $arrFSOFlyweights = array() ;
}


?>
