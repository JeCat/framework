<?php
namespace jc\fs\imp ;

class MockLocalFSO extends LocalFSO{
	public function __construct(LocalFileSystem $aFileSystem,$sPath,$sLocalPath){
		parent::__construct($aFileSystem,$sPath,$sLocalPath);
	}
	public function exists(){
		return true;
	}
	public function create(){
	}
};

?>

