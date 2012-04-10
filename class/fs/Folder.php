<?php
////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  这个文件是 JeCat PHP框架的一部分，该项目和此文件 均遵循 GNU 自由软件协议
// 
//  Copyleft 2008-2012 JeCat.cn(http://team.JeCat.cn)
//
//
//  JeCat PHP框架 的正式全名是：Jellicle Cat PHP Framework。
//  “Jellicle Cat”出自 Andrew Lloyd Webber的音乐剧《猫》（《Prologue:Jellicle Songs for Jellicle Cats》）。
//  JeCat 是一个开源项目，它像音乐剧中的猫一样自由，你可以毫无顾忌地使用JCAT PHP框架。JCAT 由中国团队开发维护。
//  正在使用的这个版本是：0.7.1
//
//
//
//  相关的链接：
//    [主页]			http://www.JeCat.cn
//    [源代码]		https://github.com/JeCat/framework
//    [下载(http)]	https://nodeload.github.com/JeCat/framework/zipball/master
//    [下载(git)]	git clone git://github.com/JeCat/framework.git jecat
//  不很相关：
//    [MP3]			http://www.google.com/search?q=jellicle+songs+for+jellicle+cats+Andrew+Lloyd+Webber
//    [VCD/DVD]		http://www.google.com/search?q=CAT+Andrew+Lloyd+Webber+video
//
////////////////////////////////////////////////////////////////////////////////////////////////////////////
/*-- Project Introduce --*/
namespace org\jecat\framework\fs ;

use org\jecat\framework\lang\Exception;

class Folder extends FSO
{
	const CREATE_RECURSE_DIR = 020000 ;		// 创建文件或目录时，递归创建所属的目录
	const CREATE_ONLY_OBJECT = 040000 ;		// 只创建IFSO对象，不创建文件/目录，如果文件/目录不存在
	
	const CREATE_DEFAULT = 020775 ; 			// CREATE_RECURSE_DIR | 0775
	
	const FIND_RETURN_PATH = 4 ;				// find() 文件时，返回路径字符串，而不是FSO对像
	const FIND_AUTO_CREATE = 1 ;				// 如果找不到文件，自动创建文件
	const FIND_AUTO_CREATE_OBJECT = 2 ;		// 如果找不到文件，自动创建一个文件对象（没有实际创建文件）
	const FIND_DEFAULT = FSO::unknow ;			// FSO::unknow
		
	static public function createFolder($sPath,$nFlag=self::CREATE_DEFAULT)
	{
		$aFolder = new Folder($sPath,$nFlag) ;
		if(!$aFolder->exists())
		{
			$aFolder->create($nFlag) ;
		}
		return $aFolder ;
	}
	
	/**
	 * @return FSO
	 * @retval $type参数		不存在	存在为File	存在为Folder
	 */
	public function find($sInputPath,$nFlag=self::FIND_DEFAULT)
	{
		if( !($nFlag&FSO::CLEAN_PATH) )
		{
			if( $sInputPath and $sInputPath[0]!=='/' )
			{
				$sInputPath = '/' . $sInputPath  ;
			}
			// FSO::tidyPath($sPath,true) ;
		}
		$sPath = $this->path() . $sInputPath  ;
		
		$nType = $nFlag&FSO::type ;
		
		// 是一个文件
		if( is_file($sPath) and ($nType==self::unknow or $nType&self::file) )
		{
			if($nFlag&self::FIND_RETURN_PATH)
			{
				return $sPath ;
			}
			$aFSO = new File($sPath,self::CLEAN_PATH) ;
		}

		// 是一个目录
		else if(is_dir($sPath) and ($nType==self::unknow or $nType&self::folder) )
		{
			if($nFlag&self::FIND_RETURN_PATH)
			{
				return $sPath ;
			}
			$aFSO = new Folder($sPath,self::CLEAN_PATH) ;
		}
		
		// 路径不存在 或 路径的类型和指定的不一致
		else
		{
			return null;
		}

		if( $this->httpUrl() )
		{
			$aFSO->setHttpUrl($this->httpUrl().$sInputPath) ;
		}
		return $aFSO ;
	}
	
	/**
	 * @return File
	 */
	public function findFile($sInputPath,$nFlag=0)
	{
		if( !($nFlag&FSO::CLEAN_PATH) )
		{
			if( $sInputPath and $sInputPath[0]!=='/' )
			{
				$sInputPath = '/' . $sInputPath  ;
			}
		}
		
		if( $file =  $this->find($sInputPath,$nFlag&(~FSO::type)|FSO::file|FSO::CLEAN_PATH) )
		{
			return $file ;
		}

		$sPath = $this->path() . $sInputPath  ;
		if( ($nFlag&self::FIND_AUTO_CREATE)==self::FIND_AUTO_CREATE )
		{
			$aFile = $this->createChildFile($sPath,File::CREATE_DEFAULT|FSO::CLEAN_PATH) ;
			if( $this->httpUrl() )
			{
				$aFile->setHttpUrl($this->httpUrl().$sInputPath) ;
			}
			return (Folder::FIND_RETURN_PATH & $nFlag)? $aFile->path(): $aFile ;			
		}
		else if( ($nFlag&self::FIND_AUTO_CREATE_OBJECT)==self::FIND_AUTO_CREATE_OBJECT )
		{
			$aFile = $this->createChildFile($sPath,File::CREATE_DEFAULT|self::CREATE_ONLY_OBJECT|FSO::CLEAN_PATH) ;
			if( $this->httpUrl() )
			{
				$aFile->setHttpUrl($this->httpUrl().$sInputPath) ;
			}
			return (Folder::FIND_RETURN_PATH & $nFlag)? $aFile->path(): $aFile ;
		}
		
		return null ;
	}

	/**
	 * @return \Folder
	 */
	public function findFolder($sInputPath,$nFlag=0)
	{
		if( !($nFlag&FSO::CLEAN_PATH) )
		{
			if( $sInputPath and $sInputPath[0]!=='/' )
			{
				$sInputPath = '/' . $sInputPath  ;
			}
		}
		
		if( $folder =  $this->find($sInputPath,$nFlag&(~FSO::type)|FSO::folder|FSO::CLEAN_PATH) )
		{
			return $folder ;
		}

		$sPath = $this->path() . $sInputPath  ;
		
		if( ($nFlag&self::FIND_AUTO_CREATE) == self::FIND_AUTO_CREATE )
		{
			$aFolder = $this->createChildFolder($sPath,Folder::CREATE_DEFAULT|FSO::CLEAN_PATH) ;
			if( $this->httpUrl() )
			{
				$aFolder->setHttpUrl($this->httpUrl().$sInputPath) ;
			}
			return (Folder::FIND_RETURN_PATH & $nFlag)? $aFolder->path(): $aFolder ;
		}
		else if( ($nFlag&self::FIND_AUTO_CREATE_OBJECT) == self::FIND_AUTO_CREATE_OBJECT )
		{
			$aFolder = $this->createChildFolder($sPath,Folder::CREATE_DEFAULT|self::CREATE_ONLY_OBJECT|FSO::CLEAN_PATH) ;
			if( $this->httpUrl() )
			{
				$aFolder->setHttpUrl($this->httpUrl().$sInputPath) ;
			}
			return (Folder::FIND_RETURN_PATH & $nFlag)? $aFolder->path(): $aFolder ;
		}
		
		return null ;
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


