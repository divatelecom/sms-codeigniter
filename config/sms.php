<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    /* DivaTxt SMS platform configuration file*/
    /* This file must be placed into application/config/ directory */
        
    /* Variables to be set by the user*/
            
        /* DivaTxt API user name - MANDATORY */
		$config['divasms']['username'] = "";
        
        /* DivaTxt API password - MANDATORY */  
        $config['divasms']['password'] = "";
        
        /*This is the URL of a webpage you have setup to 
        receive a delivery or status report for a message - OPTIONAL*/
        $config['divasms']['deliveryHandler'] = "";              
        
    /* constant variables*/     
       
        $config['divasms']['serverURL'] = "https://secure.divatelecom.co.uk/";
        $config['divasms']['messagePath'] = "API/index.php/V1/message";
        $config['divasms']['accountPath'] = "API/index.php/V1/account";                                                                     