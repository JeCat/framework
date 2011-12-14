<?php
namespace org\jecat\framework\mvc\view\layout ;

use org\jecat\framework\io\IOutputStream;
use org\jecat\framework\mvc\view\IView;
use org\jecat\framework\mvc\view\View;

class LayoutableView extends View
{
	public function addWrapperCssClass($sCssClass)
	{
		$arrClasses =& $this->variables()->getRef('wrapper.classes') ;
		if($arrClasses===null)
		{
			$arrClasses = array() ;
		}
		
		if(!in_array($sCssClass,$arrClasses))
		{
			$arrClasses[] = $sCssClass ;
		}
	}
	
	public function removeWrapperCssClass($sCssClass)
	{
		$arrClasses =& $this->variables()->getRef('wrapper.classes') ;
		if($arrClasses===null)
		{
			$arrClasses = array() ;
		}
		$pos = array_search($sCssClass,$arrClasses) ;
		if($pos!==false)
		{
			unset($arrClasses[$pos]) ;
		}
	}
	
	public function setWrapperStyle($sStyle)
	{
		$this->variables()->set('wrapper.style',$sStyle) ;
	}
	public function wrapperStyle()
	{
		return $this->variables()->getRef('wrapper.style') ;
	}
	
	public function addWrapperAttr($sName,$sValue)
	{
		$arrAttrs =& $this->variables()->getRef('wrapper.attrs') ;
		if($arrAttrs===null)
		{
			$arrAttrs = array() ;
		}
		
		$sName = strtolower($sName) ;
		$arrAttrs[$sName] = $sValue ;
	}
	
	public function removeWrapperAttr($sName)
	{
		$arrAttrs =& $this->variables()->getRef('wrapper.attrs') ;
		if($arrAttrs===null)
		{
			$arrAttrs = array() ;
		}
		
		$sName = strtolower($sName) ;
		unset($arrAttrs[$sName]) ;
	}
	
	public function renderWrapperHeader(IView $aView,IOutputStream $aOutputStream,$sClass=null,$sStyle=null)
	{
		// id
		$sId = self::htmlWrapperId($this) ;
	
		// name
		$sViewNameEsc = addslashes($this->name()) ;
	
		// class
		$arrClasses = $this->variables()->get('wrapper.classes')?: array() ;
		if($sClass)
		{
			$arrClasses[] = $sClass ;
		}
		$sClasses = implode(' ',$arrClasses) ;
	
		// style
		if( $sStyle = $this->wrapperStyle().$sStyle )
		{
			$sStyle = ' style="' . $sStyle . '"' ;
		}
	
		// attrs
		$sAttrs = '' ;
		foreach($this->variables()->get('wrapper.attrs')?: array() as $sName=>$value)
		{
			$sAttrs.= " {$sName}=\"".addslashes($value).'"' ;
		}
	
		$aOutputStream->write("<div{$sAttrs} id='{$sId}' class='{$sClasses}' name='{$sViewNameEsc}'{$sStyle}>") ;
	}
	
	static public function htmlWrapperId(IView $aView)
	{
		return 'view-wrapper-'.$aView->id() ;
	}
}

?>