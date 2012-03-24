<?php
namespace org\jecat\framework\fs ;

use org\jecat\framework\lang\Exception ;

class Folder extends FSO
{
	const CREATE_RECURSE_DIR = 020000 ;		// 创建文件或目录时，递归创建所属的目录
	const CREATE_ONLY_OBJECT = 040000 ;		// 只创建IFSO对象，不创建文件/目录，如果文件/目录不存在
	
	const CREATE_DEFAULT = 020775 ; 			// CREATE_RECURSE_DIR | 0775
	
	const FIND_RETURN_PATH = 4 ;				// find() 文件时，返回路径字符串，而不是FSO对像
	const FIND_AUTO_CREATE = 1 ;				// 如果找不到文件，自动创建文件
	const FIND_AUTO_CREATE_OBJECT = 2 ;		// 如果找不到文件，自动创建一个文件对象（没有实际创建文件）
	const FIND_DEFAULT = FSO::unknow ;			// FSO::unknow
		
	/**
	 * @return FSO
	 * @retval $type参数		不存在	存在为File	存在为Folder
	 */
	public function find($sPath,$nFlag=self::FIND_DEFAULT)
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
			return ($nFlag&self::FIND_RETURN_PATH)? $sPath: new File($sPath,self::CLEAN_PATH) ;
		}

		// 是一个目录
		else if(is_dir($sPath) and ($nType==self::unknow or $nType&self::folder) )
		{
			return ($nFlag&self::FIND_RETURN_PATH)? $sPath: new Folder($sPath,self::CLEAN_PATH) ;
		}
		
		// 路径不存在 或 路径的类型和指定的不一致
		else
		{
			return null;
		}
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
		
		$file =  $this->find($sPath,$nFlag&(~FSO::type)|FSO::file|FSO::CLEAN_PATH) ;
		
		if( !$file and ($nFlag&self::FIND_AUTO_CREATE)==self::FIND_AUTO_CREATE )
		{
			$aFile = $this->createChildFile($sPath,File::CREATE_DEFAULT|FSO::CLEAN_PATH) ;
			return (Folder::FIND_RETURN_PATH & $nFlag)? $aFile->path(): $aFile ;			
		}
		else if( !$file and ($nFlag&self::FIND_AUTO_CREATE_OBJECT)==self::FIND_AUTO_CREATE_OBJECT )
		{
			$aFile = $this->createChildFile($sPath,File::CREATE_DEFAULT|self::CREATE_ONLY_OBJECT|FSO::CLEAN_PATH) ;
			return (Folder::FIND_RETURN_PATH & $nFlag)? $aFile->path(): $aFile ;
		}
		else
		{
			return $file ;
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
		
		$folder =  $this->find($sPath,$nFlag&(~FSO::type)|FSO::folder|FSO::CLEAN_PATH) ;
		
		if( !$folder and ($nFlag&self::FIND_AUTO_CREATE) == self::FIND_AUTO_CREATE )
		{
			$aFolder = $this->createChildFolder($sPath,Folder::CREATE_DEFAULT|FSO::CLEAN_PATH) ;
			return (Folder::FIND_RETURN_PATH & $nFlag)? $aFolder->path(): $aFolder ;
		}
		else if( !$folder and ($nFlag&self::FIND_AUTO_CREATE_OBJECT) == self::FIND_AUTO_CREATE_OBJECT )
		{
			$aFolder = $this->createChildFolder($sPath,Folder::CREATE_DEFAULT|self::CREATE_ONLY_OBJECT|FSO::CLEAN_PATH) ;
			return (Folder::FIND_RETURN_PATH & $nFlag)? $aFolder->path(): $aFolder ;
		}
		else
		{
			return $folder ;
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
	
	public function createChildFolder($sPath,$nFlag=Folder::CREATE_DEFAULT)
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
	
	public function delete($bRecurse=false,$bIgnoreError=false)
	{
		/**
		 * @todo clean
		 * 此函数用于删除自己，
		 * 但以前的接口用于删除自己的子目录／子文件
		 * 现在删除自己的子目录／子文件改用deleteChild方法
		 * 为了将不兼容的代码及时发现出来，此处做类型检查并抛出异常
		 */
		if(!is_bool($bRecurse)){
			throw new Exception(
				'%s 的第一个参数必须是boolean : %s',
				array(
					__METHOD__,
					$bRecurse
				)
			);
		}
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
		return true;
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
				$sSubPath = $sFilePath ;
				if( !$this->deleteAllChildren($sSubPath,$bIgnoreError) )
				{
					if(!$bIgnoreError)
					{
						return false ;
					}
					$bReturn = true ;
				}
				rmdir($sSubPath);
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
		
		return $bReturn;
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

