<?php

/**
 *
 */
class DeployDbMaintainer {
    
    /**
     * @var string 
     */
    protected $meta_sql;
    
    /**
     * @var string 
     */
    protected $webshop_sql;
    
    /**
     * @var boolean
     */
    protected $isVerified = false;
    
    function __construct()
    {
        if(!defined('DEPLOY_DB_HOOKS_PATH'))
        {
            define('DEPLOY_DB_HOOKS_PATH', '/var/vubla/deploy_db_hooks');
        }
        if(!defined('OLD_DEPLOY_DB_HOOKS_PATH'))
        {
            define('OLD_DEPLOY_DB_HOOKS_PATH', '/var/vubla/old_deploy_db_hooks');
        }
    }
    
    /**
     * Retrive the sql from DB_DEPLOY_PATH and loads it into $meta_sql and $webshop_sql
     * Files in DB_DEPLOY_PATH must be prefixed w_ or m_ 
     * Resets the verified property
     */
    function load()
    {
        
    }
    
    /**
     * Presents the sql in a nicely fashion. 
     */
    function __toString()
    {
        
    }
    
    /**
     * Verifies the loaded sql and checks for validity
     * Sets the verified proprty
     */
    function verify()
    {
        
    }
    
    
    /**
     * Deploys the sql to the dbs
     * Calls verify if it has not been called.
     */
    function deploy()
    {
        
    }
    
    /**
     * Returns true id verified to okay. 
     * @return bool
     */
    function isVerified()
    {
        
    }
    
    /**
     * Cleans up EPLOY_DB_HOOKS_PATH and backups to OLD_DEPLOY_DB_HOOKS_PATH
     */
    function cleanup()
    {
        
    }
    
}



?>