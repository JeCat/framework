<?php
namespace jc\db\recordset ;

interface IRecordSet extends \SeekableIterator
{
	const currentRow = -1 ;
	
	public function rowCount() ;
	
	public function row( $nRow=self::currentRow ) ;

	public function field($sFieldName,$nRow=self::currentRow) ;

	public function fieldIterator() ;
	
}

?>