<?php
namespace org\jecat\framework\fs\imp ;

use org\jecat\framework\fs\FileSystem;

class LocalFileSystem extends FileSystem
{
	public function __construct($sLocalPath)
	{
		$this->sLocalPath = self::formatPath($sLocalPath) ;
	}
	
	public function iterator($sPath)
	{
		
	}
	
	public function localPath()
	{
		return $this->sLocalPath ;
	}
	
	public function url()
	{
		return 'file://' . $this->localPath() ;
	}
	
	/////////////////////////////////////////////////////////////////////////
	
	protected function existsOperation(&$sPath)
	{
		return file_exists($this->sLocalPath.$sPath) ;		
	}
	
	protected function isFileOperation(&$sPath)
	{
		return is_file($this->sLocalPath.$sPath) ;		
	}
	
	protected function isFolderOperation(&$sPath)
	{
		return is_dir($this->sLocalPath.$sPath) ;
	}
	
	protected function deleteFileOperation(&$sPath)
	{
		return unlink($this->sLocalPath.$sPath) ;
	}
	
	protected function deleteDirOperation(&$sPath,$bRecurse=false,$bIgnoreError=false)
	{
		if(!$bRecurse)
		{
			return rmdir($this->sLocalPath.'/'.$sPath) ;
		}
		
		else 
		{
			$sDirPath = $this->sLocalPath.$sPath ;
			
			if( !$hDir = opendir($sDirPath) )
			{
				return false ;
			}
			
			$bReturn = true ;
			
			while( $sFilename=readdir($hDir) )
			{
				if( $sFilename=='.' or $sFilename=='..' )
				{
					continue ;
				}
				
				$sFilePath = $sDirPath.'/'.$sFilename ;
				
				if( is_dir($sFilePath) )
				{
					// 递归删除子目录
					$sSubPath = $sPath.'/'.$sFilename ;
					if( !$this->deleteDirOperation($sSubPath,$bRecurse,$bIgnoreError) )
					{
						if(!$bIgnoreError)
						{
							return false ;
						}
						$bReturn = true ;
					}
				}
				else
				{
					// 删除子文件
					if( !unlink($sFilePath) )
					{
						if(!$bIgnoreError)
						{
							return false ;
						}
						$bReturn = true ;
					}
				}
			}
			
			closedir($hDir) ;
				
			// 删除自己
			if( rmdir($sDirPath) )
			{
				return $bReturn ;
			}
			else 
			{
				return false ;
			}
		}
	}
	
	protected function createFileObject(&$sPath)
	{
		return LocalFile::createInstance( array($this,$sPath,$this->sLocalPath.$sPath), $this->application() ) ;
	}
	
	protected function createFolderObject(&$sPath)
	{
		return LocalFolder::createInstance( array($this,$sPath,$this->sLocalPath.$sPath), $this->application() ) ;
	}
	
	private $sLocalPath ;
}

?>
