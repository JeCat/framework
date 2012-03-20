<?php
namespace org\jecat\framework\mvc\model\db\orm ;

use org\jecat\framework\lang\Exception;
use org\jecat\framework\db\reflecter\AbStractColumnReflecter;
use org\jecat\framework\db\DB;
use org\jecat\framework\lang\Object;

class AutoPrimaryGenerator extends Object
{
	public function generate(AbStractColumnReflecter $aClmRfl,$sTable,DB $aDB=null,$nMinLen=4,$nMaxLen=10)
	{
		if( $aClmRfl->isString() )
		{
			return $this->generateCharValue($aDB,$sTable,$aClmRfl->name(),$nMinLen,$nMaxLen) ;
		}
		else if( $aClmRfl->isInts() or $aClmRfl->isFloat() )
		{
			return $this->generateNumValue($aDB,$sTable,$aClmRfl->name(),$nMinLen) ;
		}
		else 
		{
			throw new Exception("无法为数据表字段：%s.%s自动生成主键值，只接受字符或数值类型。",array($sTable,$aClmRfl->name())) ;
		}
	}
	
	private function generateCharValue(DB $aDB,$sTable,$sColumn,$nMinLen,$nMaxLen)
	{
		$sValue = '' ;
		for($nLen=0;$nLen<$nMinLen;$nLen++)
		{
			$sValue.= $this->randChar() ;
		}
		 ;
		while($nLen<=$nMaxLen)
		{
			// 检查表中的值是否存在
			$arrRow = $aDB->query("select count(*) as cnt from `{$sTable}` where `{$sColumn}`='{$sValue}' ;")->fetch(\PDO::FETCH_ASSOC) ; 
			if( $arrRow['cnt']=='0' )
			{
				return $sValue ;
			}
			
			// 增加一位
			$sValue.= $this->randChar() ;
			$nLen ++ ;
		} 
		
		return null ;
	}
	
	private function randChar()
	{
		$nFeed = rand(0,35) ;
		
		// a-z
		if($nFeed>9)
		{
			return chr($nFeed-10+97) ;
		}
		// 0-9
		else
		{
			return chr($nFeed+48) ;
		}
	}
	
	private function generateNumValue(DB $aDB,$sTable,$sColumn,$nMinVal)
	{
		$arrRow = $aDB->query("select max(`$sColumn`) as maxval from `{$sTable}` ;")->fetch(\PDO::FETCH_ASSOC) ;
		$maxVal = (int)$arrRow['maxval'] ;
		return $maxVal<(int)$nMinVal? $nMinVal: $maxVal+1 ;
	}
}

