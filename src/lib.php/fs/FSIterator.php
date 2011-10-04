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
	const RECURSIVE_BREADTH_FIRST = 0x40 ; //如果不置此位，则按照深度优先进行搜索
//	const FLAG_DEFAULT = 0x76 ; // FILE | FOLDER
	const FLAG_DEFAULT = 0x36 ; // FILE | FOLDER
	
	public function __construct(IFolder $aParentFolder,$nFlags){
		$this->nFlags=$nFlags;
		$this->aParentFolder = $aParentFolder;
		$this->rewind();
	}
	
	public function current (){
		if ( $this == $this->stackTop() ){
			if ( $this->nFlags & self::RETURN_FSO ){
				return $this->FSOcurrent();
			}else{
				if( $this->nFlags & self::RETURN_ABSOLUTE_PATH ){
					return $this->FSOcurrent()->path();
				}else{
					return FileSystem::relativePath( $this->aParentFolder , $this->FSOcurrent()->path() );
				}
			}
		}else{
			return $this->stackTop()->current();
		}
	}
	
	public function key (){
		return $this->nKey;
	}
	
	public function next (){
		if ( $this->isStackEmpty() ){
			throw new \jc\lang\Exception('已经没有了，不能再next了');
			return ;
		}
		
		if( $this->nFlags & self :: RECURSIVE_BREADTH_FIRST ){
			do{
				$this->stackTop()->FSOmoveNext();
				while( !$this->stackTop() ->valid())
				{
					$this->stackPop();
					if( $this->isStackEmpty() ){
						break 2;
					}
				}
				$aFSO=$this->stackTop()->FSOcurrent();
				if ( $aFSO instanceof IFolder 
						and $aFSO->name() != '.' 
						and $aFSO->name() != '..'
						and ( $this->nFlags & self::RECURSIVE_SEARCH ) ){
					$aIterator = $aFSO->iterator($this->nFlags);
					if( $aIterator ->valid() ){
						$this->stackPush( $aIterator );
					}
				}
			}while(! $this->stackTop()->satisfyFlags() );
		}else{
			if( $this != $this->stackTop () ){
				$this->stackTop()->next();
				if( !$this->stackTop() ->valid()){
					$this->stackPop();
					do{
						$this->stackTop()->FSOmoveNext();
					}while( $this->stackTop() ->valid() and ! $this->stackTop()->satisfyFlags() );
				}
			}else if( $this === $this->stackTop() ){
				do{
					$aFSO=$this->stackTop()->FSOcurrent();
					if ( $aFSO instanceof IFolder 
							and $aFSO->name() != '.' 
							and $aFSO->name() != '..'
							and ( $this->nFlags & self::RECURSIVE_SEARCH ) ){
						$aIterator = $aFSO->iterator($this->nFlags);
						if( $aIterator ->valid() ){
							$this->stackPush( $aIterator );
							break;
						}else{
							$this->FSOmoveNext();
						}
					}else{
						$this->FSOmoveNext();
					}
				}while( ! ( $this->isStackEmpty() or $this->stackTop()->satisfyFlags() ) );
			}
		}
		$this->nKey ++;
	}
	
	public function rewind (){
		$this->	nKey = 0;
		$this->FSOrewind();
		$this->arrIteratorStack=array($this);
		if( ! $this->satisfyFlags() ){
			$this->next();
		}
	}
	public function valid (){
		if( $this->isStackEmpty() ){
			$ret = false;
		}else{
			$ret=$this->stackTop()->FSOvalid();
		}
		return $ret;
	}
	
	////////////////
	protected $nFlags ;
	protected $aParentFolder = "";
	protected $nKey = 0;
	
	private function satisfyFlags(){
		if( $this == $this->stackTop() ){
			if( $this->FSOcurrent() instanceof IFolder ){
				if ( ! ( $this->nFlags & self::FOLDER ) ){
					return false;
				}else{
					if( $this->FSOcurrent()->name() === '.' ){
						return ( $this->nFlags & self::DOT );
					}else{
						return true;
					}
				}
			}else if( $this->FSOcurrent() instanceof IFile ){
				return ( $this->nFlags & self::FILE );
			}else{
				throw new \jc\lang\Exception($this->FSOcurrent()->path().'即不是IFile也不是IFolder');
			}
		}else{
			return $this->stackTop()->satisfyFlags();
		}
	}
	
	private $arrIteratorStack = array () ;
	protected function stackTop(){
		return ($this->isStackEmpty() ? null : $this->arrIteratorStack[0] );
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
