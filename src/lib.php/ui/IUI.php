<?php

namespace jc\ui ;

interface IUI
{
	/**
	 * return ICompiler
	 */
	public function compiler() ;
	
	public function setCompiler(ICompiler $aCompiler) ;
	
	/**
	 * return IDisplayer
	 */
	public function displayer() ;
	
	public function setDisplayer(IDisplayer $aDisplayer) ;
}

?>