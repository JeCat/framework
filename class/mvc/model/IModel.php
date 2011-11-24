<?php
namespace org\jecat\framework\mvc\model ;

use org\jecat\framework\pattern\composite\IContainer;

interface IModel extends \ArrayAccess, \Iterator
{
	public function isEmpty() ;
	
	public function hasSerialized() ;
	
	public function setSerialized($bSerialized=true) ;
	
	public function load() ;
	
	public function save() ;
	
	public function delete() ;
	
	// for child model ///////////////////////////////
	public function addChild(IModel $aModel,$sName) ;
	
	public function removeChild(IModel $aModel) ;
	
	public function clearChildren() ;
	
	public function childrenCount() ;
	
	/**
	 * @return IModel
	 */
	public function child($sName) ;
	
	public function childIterator() ;
	
	public function childNameIterator() ;
	
	public function createChild($bAdd=true);
	
	// for data ///////////////////////////////
	public function data($sName) ;
	
	public function setData($sName,$sValue,$bChanged=true) ;
	
	public function hasData($sName) ;
	
	public function removeData($sName) ;
	
	public function clearData() ;
	
	public function dataNameIterator() ;
	
	public function dataIterator() ;
	
}

?>
