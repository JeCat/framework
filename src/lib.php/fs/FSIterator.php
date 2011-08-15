<?php

namespace jc\fs ;

abstract class FSIterator implements \Iterator{
	const DOT = 0x01 ;
	const FILE = 0x02 ;
	const FOLDER = 0x04 ;
	const DIR_ALL = 0x05 ; // DOT | FOLDER 
	const FILE_AND_FOLDER = 0x06 ; // FILE|FOLDER
	const RETURN_FSO = 0x08 ; // 如果不置此位，返回路径名
	const RETURN_ABSOLUTE_PATH = 0x10 ; // 如果不置此位，返回相对路径
	const RECURSIVE_SEARCH = 0x20 ; // 如果不置此位，只搜索当前目录下
	const RECURSIVE_BREADTH_FIRST = 0x40 ; //如果不置此位，默认深度优先
	const FLAG_DEFAULT = 0x06 ; // FILE | FOLDER
	
	public function __construct(IFolder $sFolderPath,$nFlags=self::FLAG_DEFAULT){
		$this->sFolderPath = $sFolderPath;
		$this->rewind();
	}
	
	public function current (){
		if ( $this == $this->stackTop() ){
			if ( $this->nFlags & self::RETURN_FSO ){
				return $this->currentFSO();
			}else{
				if( $this->nFlags & self::RETURN_ABSOLUTE_PATH ){
					return $this->currentFSO()->path();
				}else{
					return FileSystem::relativePath( $this->$sFolderPath , $this->currentFSO()->path() );
				}
			}
		}else{
			$this->stackTop()->current();
		}
	}
	
	public function key (){
		return $this->nKey;
	}
	
	public function next (){
		if ( $this->isStackEmpty() ) return ;
		do{
			if ( $this->FSOcurrent() instanceof IFolder 
					and ( $this->nFlags & self::RECURSIVE_SEARCH ) ){
				$aIterator = $this->FSOcurrent()->iterator($this->nFlags);
				$aIterator -> rewind();
				$this->stackPush( $aIterator );
			}else{
				do{
					if( $this == $this->getStackTop() ){
						$this->FSOmoveNext();
					}else{
						$this->stackTop()->next();
					}
					if( !$this->stackTop()->valid() ) break;
					else $this->stackPop() ;
				}while( !$this->isStackEmpty());
			}
		}while( ! ( $this->isStackEmpty() or $this->getStackTop()->satisfyFlags() ) );
		$this->nKey ++;
	}
	
	public function rewind (){
		$this->$nKey = 0;
		$this->FSOrewind();
		$this->arrIteratorStack=array($this);
	}
	public function valid (){
		return $this->FSOvalid();
	}
	
	protected $nFlags = self::FLAG_DEFAULT;
	protected $sFolderPath = "";
	protected $nKey = 0;
	
	private function satisfyFlags(){
		if( $this == $this->stackTop() ){
			if( $this->FSOcurrent() instanceof IFolder ){
				if ( ! ( $this->nFlags & self::FOLDER ) ){
					return false;
				}else{
					if( $this->FSOcurrent()->name() === '.' ){
						return ($this->nFlags & self::DOT );
					}else{
						return true;
					}
				}
			}else if( $this->FSOcurrent() instanceof IFile ){
				return ( $this->nFlags & self::File );
			}else{
				throw new \jc\lang\Exception('即不是IFile也不是IFolder');
			}
		}else{
			return $this->stackTop()->satisfyFlags();
		}
	}
	
	private $arrIteratorStack = array () ;
	protected function stackTop(){
		return $this->arrIteratorStack[0];
	}
	
	protected function stackPop(){
		return array_shift($this->arrIteratorStack);
	}
	
	protected function stackPush(FSIterator $aIterator){
		if( $this->nFlags & self::RECURSIVE_BREADTH_FIRST ){
			array_push($this->arrIteratorStack , $aIterator );
		}else{
			array_unshift($this->arrIteratorStack,$aIterator);
		}
	}
	
	protected function isStackEmpty(){
		return 0===count($this->arrIteratorStack) ;
	}
		
	//////////////////
	abstract protected function FSOcurrent();
	abstract protected function FSOmoveNext();
	abstract protected function FSOrewind();
	abstract protected function FSOvalid();
}

?>
