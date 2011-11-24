<?php
namespace org\jecat\framework\fs\imp ;

use org\jecat\framework\fs\FSIterator;

class LocalFolderIterator extends FSIterator{
	public function __construct (LocalFolder $aFolder,$nFlags = self::FLAG_DEFAULT){
		$this->aLocalFolder = $aFolder;
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
		return $this->aLocalFolder->fileSystem()->find($str) ;
	}
	
	protected function FSOmoveNext(){
		do{
			$this->strReaddir = readdir($this->aDirSource);
		}while( $this->strReaddir === ".." );
	}
	
	protected function FSOrewind(){
		rewinddir( $this->aDirSource);
		$this->FSOmoveNext();
	}
	
	protected function FSOvalid(){
		$ret = (false !== $this->strReaddir);
		return $ret;
	}
}
