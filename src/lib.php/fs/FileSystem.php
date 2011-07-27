<?php
namespace jc\fs ;

use jc\lang\Object;

abstract class FileSystem extends Object
{
	/**
	 * @return IFSO
	 */
	public function find($sPath)
	{
		if(substr($sPath,0,1)!='/')
		{
			$sPath = '/'.$sPath ;
		}
		
		
		//////////////
		$sFlyweightKey = $this->fsoFlyweightKey($sPath) ;
		
		if( !isset($this->arrFSOFlyweights[$sFlyweightKey]) )
		{
			if( $this->isFile($sPath) )
			{
				$this->arrFSOFlyweights[$sFlyweightKey] = $this->createFileObject($this,$sPath) ;
			}
			
			else if( $this->isFolder($sPath) )
			{
				$this->arrFSOFlyweights[$sFlyweightKey] = $this->createFolderObject($this,$sPath) ;
			}
			
			else 
			{
				return null ;
			}
		}
		
		return $this->arrFSOFlyweights[$sFlyweightKey] ;
	}
	
	public function mount($sPath,self $aFileSystem)
	{
		$sPath = self::formatPath($sPath) ;
		
		$aFileSystem->beMount($this,$sPath) ;
		$this->arrMounteds[$sPath] = $aFileSystem ;
	}
	
	public function umount($sPath)
	{
		if( isset($this->arrMounteds[$sPath]) )
		{
			$this->arrMounteds[$sPath]->beMount(null,'/') ;
			unset($this->arrMounteds[$sPath]) ;
		}
	}
	
	abstract public function exists($sPath) ;
	
	abstract public function isFile($sPath) ;
	
	abstract public function isFolder($sPath) ;
	
	abstract public function copy($sFromPath,$sToPath) ;
	
	abstract public function move($sFromPath,$sToPath) ;

	abstract public function createFile($sPath) ;
	
	abstract public function createFolder($sPath) ;
	
	public function delete($sPath)
	{
		if( $this->isFile($sPath) )
		{
			if( $this->deleteFile($sPath) )
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
			if( $this->deleteFolder($sPath) )
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
	
	abstract protected function deleteFile($sPath) ;
	
	abstract protected function deleteDir($sPath) ;
	
	abstract public function iterator($sPath) ;
	
	abstract protected function createFileObject($sPath) ;
	
	abstract protected function createFolderObject($sPath) ;
	
	public function mountPath()
	{
		return $this->sMountPath ;
	}
	
	public function parentFileSystem()
	{
		return $this->aParentFileSystem ;
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
	
	private $bCaseSensitive = true ;
	private $sMountPath = '/' ;
	private $aParentFileSystem = null ;
	protected $arrFSOFlyweights = array() ;
}


?>