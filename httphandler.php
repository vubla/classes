<?php

//This is currently implemented as a bunch of static methods, but later to be a trait
class HttpHandler {
	static function resolveWid($host) {
		$meta = VPDO::getVDO(DB_METADATA);
        $sql = "Select id from webshops where hostname like " . $meta->quote($host). " limit 1";
        $wid = $meta->fetchOne($sql);
        
        if(!$wid){
            $sql = "Select id from webshops where hostname like " . $meta->quote(str_replace('www.', '', $host)). " limit 1";
            $wid = $meta->fetchOne($sql);
        }
        if(!$wid){
            $sql = "Select id from webshops where hostname like " . $meta->quote('%'.$host.'%'). " limit 1";
            $wid = $meta->fetchOne($sql);
        }
        if(!$wid){
            $sql = "Select id from webshops where hostname like " . $meta->quote('%'.str_replace('www.', '', $host).'%'). " limit 1";
            $wid = $meta->fetchOne($sql);
        }
        if(!$wid){
          //  VublaMailer::sendOnargiEmail("Hostname could not be resolved", "Hostname $host could not be resolved to a WID \n", "Hosname, ". $host);
            
            return 0;
        }
       // if(!defined('WID')) define('WID', $wid);
        return $wid;
	}
}
