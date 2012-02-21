<?php
namespace org\jecat\framework\bean ;

use org\jecat\framework\lang\Object;

class BeanConfXml extends Object{
	public function xmlSourceToArray( $sXmlSource ){
		$aXml = \simplexml_load_string($sXmlSource) ;
		
		$arr = $this->xmlElementToArray($aXml);
		
		return $arr ;
	}
	
	public function xmlElementToArray( \SimpleXMLElement $aXml ){
		$arr = array() ;
		// attributes
		foreach($aXml->attributes() as $key=>$value){
			$value = (string)$value ;
			$arr[ $key ] = $value ;
		}
		
		// children
		foreach($aXml->children() as $key=>$value){
			$arr[ $key ] = $this->xmlElementToArray($value);
		}
		
		if( empty($arr) ){
			return (string)$aXml ;
		}else{
			return $arr ;
		}
	}
}
