<?php
namespace org\jecat\framework\mvc\view ;

interface IAssemblable
{
	const free = 0 ;
	const weak = 1 ;
	const soft = 3 ;
	const hard = 5 ;
	const xhard = 7 ;
	const zhard = 9 ;
	
	public function assemble(IAssemblable $aView,$nLevel=self::soft) ;

	/**
	 * @return IView
	 */
	public function assembledParent() ;

	/**
	 * @return IView
	 */
	public function setAssembledParent(IAssemblable $aView=null) ;
	
	/**
	 * @return IView
	 */
	public function unassemble(IAssemblable $aView) ;
	
	/**
	 * @return IIterator
	 */
	public function assembledIterator() ;

	public function assembledLevel() ;
	
	/**
	 * @return IView
	 */
	public function setAssembledLevel($nLevel) ;
	
}
