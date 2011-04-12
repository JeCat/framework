<?php

namespace jc\fs ;

class FSOIterator extends \FilterIterator
{
	const FILE = 1 ;
	const DOT = 2 ;
	const FOLDER = 4 ;
	const DIR = 6 ;				// DOT|FOLDER
	
	const RETURN_FSO = 16 ;
	const RETURN_NAME = 32 ;
	const RETURN_DIR = 64 ;
	const RETURN_PATH = 96 ;	// RETURN_NAME|RETURN_DIR
	
	const RECURSIVE = 256 ;
	
	const FLAG_DEFAULT = 357 ;	// FILE|FOLDER|RETURN_PATH|RECURSIVE
	
	public function __construct($sFolderPath,$nFlags=self::FLAG_DEFAULT)
	{
		$this->nFlags = $nFlags ;
		
		if( ($nFlags&self::RECURSIVE)==self::RECURSIVE )
		{
			parent::__construct(new \DirectoryIterator($sFolderPath)) ;
		}
		else
		{
			parent::__construct(new \RecursiveDirectoryIterator($sFolderPath)) ;
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
		$sPath = parent::current() ;
		
		if( is_file($sPath) )
		{
			return ($this->nFlags&self::FILE)==self::FILE ;
		}
		else if( is_dir($sPath) )
		{
			$sFilename = basename($sPath) ;
			if( $sFilename=='.' or $sFilename=='..' )
			{
				return ($this->nFlags&self::DOT)==self::DOT ;
			}

			else
			{
				return ($this->nFlags&self::FOLDER)==self::FOLDER ;
			}
			
		}
	}
	
	public function current()
	{
		if(($this->nFlags&self::RETURN_PATH)==self::RETURN_PATH)
		{
			return parent::current() ;
		}
		
		else if(($this->nFlags&self::RETURN_NAME)==self::RETURN_NAME)
		{
			return basename(parent::current()) ;
		}
		
		else if(($this->nFlags&self::RETURN_DIR)==self::RETURN_DIR)
		{
			return dirname(parent::current()) ;
		}
		
		else if($this->nFlags&self::RETURN_FSO)
		{
			return FSO::create(parent::current()) ;
		}
		
		else
		{
			// what's wrong ?
		}
	}
	
	public function dir()
	{
		return dirname(parent::current()) ;
	}
	
	public function filename()
	{
		return basename(parent::current()) ;
	}
	
	public function flags()
	{
		return $this->nFlags ;
	}
	
	private $nFlags ;
}

?>