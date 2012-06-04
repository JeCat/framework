<?php
namespace org\jecat\framework\mvc\view ;

interface IAssemblable
{
	public function assemble(IView $aView) ;

	/**
	 * @return IView
	 */
	public function assembledParent() ;
	
	/**
	 * @return IView
	 */
	public function unassemble(IView $aView) ;
	
	/**
	 * @return IIterator
	 */
	public function assembledIterator() ;
}

?>