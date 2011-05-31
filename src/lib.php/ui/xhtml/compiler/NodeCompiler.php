<?php
namespace jc\ui\xhtml\compiler ;

use jc\ui\xhtml\Tag;
use jc\ui\xhtml\Node;
use jc\lang\Type;
use jc\io\IOutputStream;
use jc\ui\CompilerManager;
use jc\ui\IObject;

class NodeCompiler extends BaseCompiler
{
	public function compile(IObject $aObject,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		Type::assert("jc\\ui\\xhtml\\Node",$aObject,'aObject') ;
		
		if( $aCompiler=$this->subCompiler($aObject) )
		{
			$aCompiler->compile($aObject,$aDev,$aCompilerManager) ;
		}
		
		else 
		{
			$this->compileTag($aObject->headTag(), $aDev, $aCompilerManager) ;
			
			if( $aTailTag = $aObject->tailTag() )
			{
				$this->compileChildren($aObject, $aDev, $aCompilerManager) ;
				
				$this->compileTag($aTailTag, $aDev, $aCompilerManager) ;
			}
		}
	}

	protected function compileTag(Tag $aTag,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		$aDev->write('<') ;
		if( $aTag->isTail() )
		{
			$aDev->write('/') ;
		}
		
		$aDev->write($aTag->name()) ;
		
		// 属性
		$aAttrs = $aTag->attributes() ;
		foreach ($aAttrs->nameIterator() as $sName)
		{
			$aDev->write(" ") ;
			$aDev->write($sName) ;
			$aDev->write('="') ;
			
			$aValue = $aAttrs->object($sName) ;
			if( $aAttrCompiler = $aCompilerManager->compiler($aValue) )
			{
				$aAttrCompiler->compile($aValue,$aDev,$aCompilerManager) ;
			}
			else 
			{
				$aDev->write(addslashes($aAttrs->get($sName))) ;
			}
		
			$aDev->write('"') ;
		}
		
		if( $aTag->isSingle() )
		{
			$aDev->write(' /') ;
		}
		
		$aDev->write('>') ;
	}
	
	public function compileChildren(Node $aNode,IOutputStream $aDev,CompilerManager $aCompilerManager)
	{
		foreach($aNode->childElementsIterator() as $aObject)
		{
			if( $aCompiler = $aCompilerManager->compiler($aObject) )
			{
				$aCompiler->compile($aObject,$aDev,$aCompilerManager) ;
			}
		}
	}
	
	// sub compiler ---------------------------------------------------------------
	public function addSubCompiler($sTagName,$sCompilerClass) 
	{
		$this->arrCompilers[ strtolower($sTagName) ] = $sCompilerClass ;
	}
	public function removeSubCompiler($sTagName)
	{
		unset($this->arrCompilers[ strtolower($sTagName) ]) ;
	}
	public function clearSubCompiler()
	{
		$this->arrCompilers = array() ;
	}

	/**
	 * @return ICompiler
	 */
	public function subCompiler(Node $aNode)
	{
		$sTagName = strtolower($aNode->tagName()) ;
		if( !isset($this->arrCompilers[$sTagName]) )
		{
			if( !isset($this->arrCompilers['*']) )
			{
				return null ;				
			}
			else 
			{
				$sTagName = '*' ;
			}
		}
		
		if( is_string($this->arrCompilers[$sTagName]) )
		{
			$this->arrCompilers[$sTagName] = new $this->arrCompilers[$sTagName]() ;
		}
		
		return $this->arrCompilers[$sTagName] ;
	}
	
	//
	static public function assignVariableName($sPrefix='')
	{
		return $sPrefix.'var'.self::$nVariableAssigned++ ;
	}
	static private $nVariableAssigned = 0 ;
}

?>