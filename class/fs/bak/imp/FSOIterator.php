<?php
namespace org\jecat\framework\fs ;

use org\jecat\framework\util\FilterMangeger;
use org\jecat\framework\lang\Exception;

class FSOIterator extends \FilterIterator
{
	const DOT = 1 ;
	const FILE = 2 ;
	const FOLDER = 4 ;
	const DIR = 5 ;				// DOT|FOLDER
	const FILES = 6 ;			// FILE|FOLDER
	
	const RETURN_FSO = 16 ;
	const RETURN_NAME = 32 ;
	const RETURN_SUBPATH = 64 ;
	const RETURN_PATH = 128 ;
	
	const RECURSIVE = 256 ;
	const END_SLASH_FOR_DIR = 512 ;
	
	const FLAG_DEFAULT = 902 ;	// FILES|RETURN_PATH|RECURSIVE|END_SLASH_FOR_DIR
	
	
	public function __construct($sFolderPath,$nFlags=self::FLAG_DEFAULT)
	{
		$this->nFlags = $nFlags ;
		$this->sFolderPath = $sFolderPath = FileSystem::formatPath($sFolderPath).'/' ;
		
		if( ($nFlags&self::RECURSIVE)==self::RECURSIVE )
		{
			parent::__construct(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sFolderPath),\RecursiveIteratorIterator::SELF_FIRST)) ;
		}
		else
		{
			parent::__construct(new \DirectoryIterator($sFolderPath)) ;
		}
	}

	static public function createFileIterator($sFolderPath)
	{
		return new self($sFolderPath,self::FILE|self::RETURN_PATH) ;
	}
	static public function createFileRecursiveIterator($sFolderPath)
	{
		return new self($sFolderPath,self::FILE|self::RETURN_PATH|self::RECURSIVE) ;
	}
	
	public function accept() 
	{
		if( in_array($this->filename(),$this->arrFilterFilenames) )
		{
			return false ;
		}
		
		if( !$this->filterByFlags() )
		{
			return false ;
		}
		
		if( $this->aFilters )
		{
			list($sPath) = $this->aFilters->handle( $this->path() ) ;
			
			if(!$sPath)
			{
				return false ;
			}
		}
		
		return true ;
	}
	
	public function current()
	{
			
		// 返回完整路径
		if(($this->nFlags&self::RETURN_PATH)==self::RETURN_PATH)
		{
			return $this->path() ;
		}
		
		// 返回文件名
		else if(($this->nFlags&self::RETURN_NAME)==self::RETURN_NAME)
		{
			return $this->filename() ;
		}
		
		else if(($this->nFlags&self::RETURN_SUBPATH)==self::RETURN_SUBPATH)
		{
			return $this->subpath() ;
		}
		
		else if($this->nFlags&self::RETURN_FSO)
		{
			return FSO::create($this->path()) ;
		}
		
		else
		{
			// what's wrong ?
		}
	}
	
	public function dir()
	{
		return dirname($this->path()) ;
	}
	
	public function filename()
	{
		return $this->getInnerIterator()->getFilename() ;
	}
	
	public function subpath()
	{
		$sPath = $this->path() ;
		$nStartPathLen = strlen($this->sFolderPath) ;
			
		if( substr($sPath,0,$nStartPathLen) == $this->sFolderPath )
		{
			return substr($sPath,$nStartPathLen) ;
		}
		else 
		{
			return $sPath ;
		}
	}
	
	public function path()
	{
		$sPath = $this->getInnerIterator()->getPathname() ;
		
		if( $this->nFlags&self::END_SLASH_FOR_DIR and is_dir($sPath) )
		{
			$sPath.= DIRECTORY_SEPARATOR ;
		}
		
		return $sPath ;
	}
	
	public function flags()
	{
		return $this->nFlags ;
	}
	
	/**
	 * @return FSOIterator
	 */
	public function addFilterFilename($sFilename)
	{
		$this->arrFilterFilenames[] = $sFilename ;
		
		return $this ;
	}
	
	public function filters()
	{
		if( !$this->aFilters )
		{
			$this->aFilters = new FilterMangeger() ;
		}
		
		return $this->aFilters ;
	}
	
	protected function filterByFlags()
	{
		if( $this->isFile() )
		{
			return ($this->nFlags&self::FILE)==self::FILE ;
		}
		else if( $this->isDir() )
		{
			if( $this->isDot() )
			{
				return ($this->nFlags&self::DOT)==self::DOT ;
			}

			else
			{
				return ($this->nFlags&self::FOLDER)==self::FOLDER ;
			}
		}
		
		else
		{
			throw new Exception("what this? %s",$sPath) ;
		}
	}
		
	public function atime ()
	{
		return $this->getInnerIterator()->getATime() ;
	}
	public function ctime ()
	{
		return $this->getInnerIterator()->getCTime() ;
	}
	public function group ()
	{
		return $this->getInnerIterator()->getGroup() ;
	}
	public function inode ()
	{
		return $this->getInnerIterator()->getInode() ;
	}
	public function mtime ()
	{
		return $this->getInnerIterator()->getMTime() ;
	}
	public function owner ()
	{
		return $this->getInnerIterator()->getOwner() ;
	}
	public function perms ()
	{
		return $this->getInnerIterator()->getPerms() ;
	}
	public function size ()
	{
		return $this->getInnerIterator()->getSize() ;
	}
	public function type ()
	{
		return $this->getInnerIterator()->getType() ;
	}
	public function isDir ()
	{
		return $this->getInnerIterator()->isDir() ;
	}
	public function isDot ()
	{
		return $this->getInnerIterator()->isDot() ;
	}
	public function isExecutable ()
	{
		return $this->getInnerIterator()->isExecutable() ;
	}
	public function isFile ()
	{
		return $this->getInnerIterator()->isFile() ;
	}
	public function isLink ()
	{
		return $this->getInnerIterator()->isLink() ;
	}
	public function isReadable ()
	{
		return $this->getInnerIterator()->isReadable() ;
	}
	public function isWritable ()
	{
		return $this->getInnerIterator()->isWritable() ;
	}

	
	private $aFilters ;
	private $arrFilterFilenames = array() ;
	
	private $nFlags ;
	private $sFolderPath ;
}

?>