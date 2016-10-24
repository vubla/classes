<?php 


class ZkAdminInterface 
{
    
    
    static function createConfig($wid)
    {
        // 
        exec("java -jar /var/vubla/jars/vubla-zk-management.jar -create -w " . (int) $wid + " > /dev/null 2>&1 &");
    }
     
}
 