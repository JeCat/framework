<?php

namespace org\jecat\framework\ui\xhtml ;

use org\jecat\framework\util\CombinedIterator;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\lang\Type;
use org\jecat\framework\util\HashTable;
use org\jecat\framework\ui\xhtml\compiler\ExpressionCompiler ;

class Attributes extends HashTable
{
	public function count() 
	{
		return parent::count() + $this->anonymousCount() ;
	}
	
	public function anonymousCount() 
	{
		return count($this->arrAnonymous) ;
	}
	
	public function add(AttributeValue $aVal)
	{
		if( $aVal->name() )
		{
			parent::set($aVal->name(),$aVal) ;
		}
		else 
		{
			parent::add($aVal) ;
			$this->arrAnonymous[] = $aVal ;
		}
	}

	public function anonymous()
	{
		return reset($this->arrAnonymous) ;
	}
	
	public function anonymousIterator()
	{
		return new \org\jecat\framework\pattern\iterate\ArrayIterator($this->arrAnonymous) ;
	}
	
	public function remove($req)
	{
		if( is_string($req) )
		{
			parent::remove($req) ;
		}
		
		else if( $req instanceof \org\jecat\framework\ui\xhtml\AttributeValue )
		{
			$this->removeByValue($req) ;
			
			// anonymous attribute
			$key = array_search($req, $this->arrAnonymous) ;
			if($key!==false)
			{
				unset($this->arrAnonymous[$key]) ;
			}
		}
		
		else 
		{
			Type::check(array('string',__NAMESPACE__.'\\AttributeValue'),$req) ;
		}
	}
	/**
	 * @wiki /MVC模式/视图/模板标签
	 * ==控件标签附加属性==
	 * 
	 * 在是用widget标签的时候我们可能需要传入一些值给控件对象.这一点可以通过attr.xxx属性做到.比如:
	 * <widget id='text' attr.widgetname='textarea'/>
	 * 这样一来控件对象就可以通过attribute方法获取widgetname的值
	 * 我们甚至可以动态获取type的值,比如
	 * <widget id='text' attr.widgetname='$theController->name()' attr.widgetname.type='expression'/>
	 * 同样通过attribute方法获取widgetname的值,只是返回的正是'$theController->name()'所返回的值,这个返回值可以是任何东西,包括对象.
	 */
	public function get($sName)
	{
		$sType = $this->has($sName.'.type')? $this->string($sName.'.type'): 'string' ;
		if( !in_array($sType,self::$arrAttributeTypes) )
		{
			throw new Exception(
					"ui node 属性 %s.type 值无效：%s; 必须为以下值："
					, array($sName,$sType,implode(',', self::$arrAttributeTypes))
			) ; 
		}
		
		switch($sType)
		{
			case 'string' :
				return '"'.$this->string($sName).'"' ;
				
			case 'expression' :
				return $this->expression($sName) ;
				
			default :
				return $this->$sType($sName) ;
		}
	}
	public function set($sName,$sValue,$sQuoteType='"',$nPosition=-1,$nLine=-1)
	{
		$aVal = new AttributeValue($sName, $sQuoteType, $nPosition, $nLine) ;
		$aVal->setSource($sValue) ;
		
		$this->add($aVal) ;
	}
	
	public function string($sName,$sDefault=null)
	{
		if( !$this->has($sName) and $sDefault!==null )
		{
			return (string) $sDefault ;
		}
		
		return ($aText=parent::get($sName))? $aText->source() :null ;
	}
	public function bool($sName,$bDefault=null)
	{
		if( !$this->has($sName) and $bDefault!==null )
		{
			return $bDefault? true: false ;
		}
		
		return !in_array( 
				strtolower( ($aText=parent::get($sName))?$aText->source():'' )
				, self::$arrFalseValues
				, true ) ;
	}
	public function int($sName,$nDefault=null)
	{
		if( !$this->has($sName) and $nDefault!==null )
		{
			return intval($nDefault) ;
		}
		
		return ($aText=parent::get($sName))?intval($aText->source()):0 ;
	}
	public function float($sName,$fDefault=null)
	{
		if( !$this->has($sName) and $fDefault!==null )
		{
			return floatval($fDefault) ;
		}
		
		return ($aText=parent::get($sName))?floatval($aText->source()):0 ;
	}
	public function expression($sName)
	{
		if( !$aText=parent::get($sName) )
		{
			return null ;
		}
		
		if( !$aObContainer = $aText->objectContainer() )
		{
			throw new Exception('AttributeVar::objectContainer() 返回空') ;
		}
		
		return ExpressionCompiler::compileExpression($aText->source(),$aObContainer->variableDeclares()) ;
	}
	public function object($sName)
	{
		return parent::get($sName) ;
	}
	
	public function source()
	{
		return $this->sSource ;
	}
	public function setSource($sSource)
	{
		$this->sSource = $sSource ;
	}
	
	static public $arrFalseValues = array(
		'false', '0', 'off', '',
	) ;
	
	static private $arrAttributeTypes = array('string','int','float','bool','expression') ; 
	
	private $sSource ;
	private $arrAnonymous = array() ;
}

?>