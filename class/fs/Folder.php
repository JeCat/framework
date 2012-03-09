<?php
namespace org\jecat\framework\fs ;


class Folder extends FSO
{
	const CREATE_RECURSE_DIR = 020000 ;		// 创建文件或目录时，递归创建所属的目录
	const CREATE_ONLY_OBJECT = 040000 ;		// 只创建IFSO对象，不创建文件/目录，如果文件/目录不存在
	
	const CREATE_DEFAULT = 020775 ; 			// CREATE_RECURSE_DIR | 0775
	
	const FIND_AUTO_CREATE = 1 ;				// 如果找不到文件，自动创建文件
	const FIND_AUTO_CREATE_OBJECT = 2 ;		// 如果找不到文件，自动创建一个文件对象（没有实际创建文件）
	
	/**
	 * @return FSO
	 * @retval $type参数		不存在	存在为File	存在为Folder
	 */
	public function find($sPath,$nFlag=self::unknow)
	{
		if( !($nFlag&FSO::CLEAN_PATH) )
		{
			$sPath = $this->path() . '/' . $sPath  ;
			// FSO::tidyPath($sPath,true) ;
		}
		
		$nType = $nFlag&FSO::type ;
		
		// 是一个文件
		if( is_file($sPath) and ($nType==self::unknow or $nType&self::file) )
		{
			$aInstance = new File($sPath,self::CLEAN_PATH) ;
		}

		// 是一个目录
		else if(is_dir($sPath) and ($nType==self::unknow or $nType&self::folder) )
		{
			$aInstance = new Folder($sPath,self::CLEAN_PATH) ;
		}
		
		// 路径不存在 或 路径的类型和指定的不一致
		else
		{
			return null;
		}
				
	
		return $aInstance ;
	}
	
	/**
	 * @return File
	 */
	public function findFile($sPath,$nFlag=0)
	{
		if( !($nFlag&FSO::CLEAN_PATH) )
		{
			$sPath = $this->path() . '/' . $sPath  ;
			// FSO::tidyPath($sPath,true) ;
		}
		
		$aFile =  $this->find($sPath,FSO::file|FSO::CLEAN_PATH) ;
		
		if( !$aFile and ($nFlag&self::FIND_AUTO_CREATE)==self::FIND_AUTO_CREATE )
		{
			return $this->createChildFile($sPath,File::CREATE_DEFAULT|FSO::CLEAN_PATH) ;
		}
		else if( !$aFile and ($nFlag&self::FIND_AUTO_CREATE_OBJECT)==self::FIND_AUTO_CREATE_OBJECT )
		{
			return $this->createChildFile($sPath,File::CREATE_DEFAULT|self::CREATE_ONLY_OBJECT) ;
		}
		else
		{
			return $aFile ;
		}
	}

	/**
	 * @return \Folder
	 */
	public function findFolder($sPath,$nFlag=0)
	{
		if( !($nFlag&FSO::CLEAN_PATH) )
		{
			$sPath = $this->path() . '/' . $sPath  ;
			// FSO::tidyPath($sPath,true) ;
		}
		
		$aFolder =  $this->find($sPath,FSO::folder|FSO::CLEAN_PATH) ;
		
		if( !$aFolder and ($nFlag&self::FIND_AUTO_CREATE) == self::FIND_AUTO_CREATE )
		{
			return $this->createChildFolder($sPath,Folder::CREATE_DEFAULT|FSO::CLEAN_PATH) ;
		}
		else if( !$aFolder and ($nFlag&self::FIND_AUTO_CREATE_OBJECT) == self::FIND_AUTO_CREATE_OBJECT )
		{
			return $this->createChildFolder($sPath,Folder::CREATE_DEFAULT|self::CREATE_ONLY_OBJECT|FSO::CLEAN_PATH) ;
		}
		else
		{
			return $aFolder ;
		}
	}

	public function create($nMode=self::CREATE_DEFAULT)
	{
		$nOldMark = umask(0) ;
		$bRes = mkdir(
			$this->path()
			, ($nMode&0777)
			, ($nMode&self::CREATE_RECURSE_DIR)?true: false
		) ;
		umask($nOldMark) ;
		
		return $bRes ;
	}
	
	public function createChildFile($sPath,$nFlag=File::CREATE_DEFAULT)
	{
		if( !($nFlag&FSO::CLEAN_PATH) )
		{
			$sPath = $this->path() . '/' . $sPath  ;
			// FSO::tidyPath($sPath,true) ;
		}
		
		if( is_dir($sPath) )
		{
			throw new Exception('试图创建File，但由于存在同名folder无法创建: %s',$sPath);
		}
		else
		{
			$aFile = new File($sPath,self::CLEAN_PATH) ;
			
			// 如果文件不存在，且没有要求 self::CREATE_ONLY_OBJECT ，则创建之
			if( !($nFlag&self::CREATE_ONLY_OBJECT) and !$aFile->exists())
			{
				if( !$aFile->create( $nFlag ) )
				{
					throw new Exception('无法创建文件:%s',$sPath) ;
				}
			}
		}
		
		return $aFile ;
	}
	
	public function createChildFolder($sPath,$nFlag=Folder::CREATE_FOLDER_DEFAULT)
	{
		if( !($nFlag&FSO::CLEAN_PATH) )
		{
			$sPath = $this->path() . '/' . $sPath  ;
			// FSO::tidyPath($sPath,true) ;
		}
		
		if( is_file($sPath) )
		{
			throw new Exception('试图创建folder，但由于存在同名file无法创建: %s',$sPath);
		}
		else
		{
			$aFolder = new Folder($sPath,self::CLEAN_PATH) ;
			
			// 如果文件不存在，且没有要求 self::CREATE_ONLY_OBJECT ，则创建之
			if( !($nFlag&self::CREATE_ONLY_OBJECT) and !$aFolder->exists())
			{
				if( !$aFolder->create( $nFlag ) )
				{
					throw new Exception('无法创建目录:%s',$sPath) ;
				}
			}
		}
		
		return $aFolder ;
	}
	
	public function delete($sPath,$bRecurse=false,$bIgnoreError=false)
	{		
		// 删除下级
		if($bRecurse)
		{
			if( !$this->deleteAllChildren($this->path(),$bIgnoreError) )
			{
				return false ;
			}
		}
		
		// 删除自己
		return rmdir($this->path()) ;
	}
	
	public function deleteChild($sPath,$bRecurse=false,$bIgnoreError=false)
	{
		if($sPath=='*')
		{
			return $this->deleteAllChildren($this->path(),$bIgnoreError) ;
		}
		else 
		{
			$sPath = $this->path() . '/' . $sPath  ;
			
			// 删除文件
			if( is_file($sPath) )
			{
				return unlink($sPath) ;
			}
			
			// 删除目录
			else if( is_dir($sPath) )
			{
				// 递归删除下级内容
				if( $bRecurse )
				{
					if( !$this->deleteAllChildren($sPath,$bIgnoreError) )
					{
						return false ;
					}
				}
				
				// 删除自己
				return rmdir($sPath) ;
			}
		}
	}
	
	private function deleteAllChildren($sDirPath,$bIgnoreError)
	{
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
				if( !$this->deleteAllChildren($sSubPath,$bIgnoreError) )
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
	}
	
	/**
	 * @return \Iterator
	 */
	public function iterator($nFlag=FSIterator::FLAG_DEFAULT)
	{
		return new LocalFolderIterator($this,$nFlag);
	}
	
	public function exists()
	{
		return is_dir($this->path());
	}
	
	
	/**
	 * @return Folder
	 */
	static public function singleton($bCreateNew=true,$createArgvs=null,$sClass=null)
	{
		return parent::singleton($bCreateNew,$createArgvs,$sClass?:__CLASS__) ;
	}
} 

