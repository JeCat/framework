<?php
namespace jc\fs\imp ;

use jc\fs\FSIterator;

class LocalFolderIterator extends FSIterator{
	public function __construct (LocalFolder $aFolder,$nFlags = self::FLAG_DEFAULT){
		$this->aLocalFolder = $aFolder;
		echo "LFI::__construct:".$this->aLocalFolder->localPath()."\n";
		$this->aDirSource = opendir( $this->aLocalFolder -> localPath () );
		parent::__construct($aFolder,$nFlags);
	}
	
	////////////////
	private $aDirSource;
	private $strReaddir="";
	private $aLocalFolder;
	
	////////////////
	protected function FSOcurrent(){
		if( $this->aLocalFolder->innerPath() == '/' ){
			$str = $this->aLocalFolder->innerPath().$this->strReaddir;
		}else{
			$str = $this->aLocalFolder->innerPath().'/'.$this->strReaddir;
		}
		echo "FSOcurrent=".$this->aLocalFolder->path().$str."\n";
		return $this->aLocalFolder->fileSystem()->find($str) ;
	}
	
	protected function FSOmoveNext(){
		do{
			$this->strReaddir = readdir($this->aDirSource);
			echo "FSOmoveNext:".$this->strReaddir."\n";
		}while( $this->strReaddir === ".." );
	}
	
	protected function FSOrewind(){
		echo "FSOrewind()\n";
		rewinddir( $this->aDirSource);
		$this->FSOmoveNext();
	}
	
	protected function FSOvalid(){
		$ret = (false !== $this->strReaddir);
		echo "FSOvalid=$ret\n";
		return $ret;
	}
}
