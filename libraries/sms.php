<?php  if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 *
 * It is also available through the world-wide-web at this URL:
 * http://www.divatxt.co.uk/license/new-bsd.php
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to support@divatxt.co.uk so we can send you a copy immediately.
 *
 * DivaTxt SMS library  
 * @copyright  Copyright (c) 2013 Diva Telecom Ltd (http://www.divatelecom.co.uk)
 * @license    http://www.divatxt.co.uk/license/new-bsd.php     New BSD License
 * @version    1
 */
 
	class sms 
	{
		/**
		 * __construct
		 *
		 * @access public
		 * @param void
		 * @return void
		**/
                    
		function __construct()
		{
    			$this->ci =& get_instance();
    	
                $this->ci->load->library('Curl');
    			$this->ci->config->load('sms');
                $divasms = $this->ci->config->item('divasms');
                
    			$this->_uname = $divasms['username'];  
                $this->_pass = $divasms['password']; 
                $this->_delHandler = $divasms['deliveryHandler']; 
                $this->_serverURL = $divasms['serverURL']; 
                $this->_messagePath = $divasms['messagePath']; 
                $this->_accountPath = $divasms['accountPath'];            			
		}



        /**
         * Send an SMS message
         * 
         * @param       string/array  $mobile The mobile(s) to send the message to, either a single string of comma seperated, or an array of strings 
         * @param       string  $message The message to send, this is currently limited to 608 characters 
         * @param       string  $sender This is the name or number you want the message to appear to have come from 11 alphanumeric characters
         * @param       string  $param1 Optional - This is a reference number you can define, so you can marry up the message in delivery reports
         * @param       string  $param2 Optional - This is a second user defined reference
         * @return      array   ['batch'] - DivaTxt batchID (is returned in delivery handler along with param1 & param2)
         *                      ['error'] - error description if one occurs        
         */
        public function sendMessage($mobiles=null,$message=null,$sender='SMS',$param1=null,$param2=null)
        {
                if($mobiles != null && $message != null)
                {
                        $mobile = '';
                        
                        // if mobiles received as a CSV string, make an array
                        if(!(is_array($mobiles)))
                        {
                                $mobiles = explode(',',$mobiles);                       
                        }

                        if(is_array($mobiles))
                        {
                                foreach($mobiles as $mob)
                                {         
                                        // perform some validation                      
                                        $mobile.=preg_replace("/[^0-9]/", "", $mob).",";
                                }
                                if(strlen($mobile)>0)
                                {
                                        //remove the last character
                                        $mobile=substr($mobile,0,strlen($mobile)-1);
                                } 
                        }                                                        
                        
                        // max sender length is 11 characters 
                        $sender = substr($sender,0,11);
                        
                        $params=array(
                                "handler" => $this->_delHandler,
                                "mobile" => $mobile,
                                "message" => $message,
                                "subject" => $sender,
                                "param1" => $param1,
                                "param2" => $param2
                        );
                        
                        $data = $this->makeRequest($this->_serverURL.$this->_messagePath,'POST',$params);
                        
                        $res=array();
                        $res['batch']="";
                        $res['error']="";
                        
                        if($data!=false){
                                $status = $data['info']['http_code'];
                                
                                //check status
                                if($status=="200"){
                                    $res['batch']=$this->getBetweenXML($data['response'], 'batch');
                                }
                                else{
                                    $res['error']=$this->getBetweenXML($data['response'], 'error');
                                }
                                
                                return $res;
                        }
                       
                }
                
                return false;
        }



        /**
         * Query status of an SMS 
         *
         * @param       string $batch - The batch ID recieved when the SMS was submitted
         * @param       string $mobile Optional mobile number within this batch if the message was sent to multiple recipients
         * 
         * if the request was successfull                  
         * @return      array $reports(['batch'] - same as above 
         *                             ['param1'] - your reference, as set when submitting the message
         *                             ['param2'] - your second reference field
         *                             ['mobile'] - the number the message was sent to
         *                             ['code'] - result code (described in the API documentation)
         *                             ['time'] - delivery time if delivered
         *                             ['status'] - message status (string)
         *                             )
         *              separate report is returned for each number even if they are in the same batch 
         *                                              
         * if the request failed
         * @return     string error description                                                           
         */
         
         public function queryMessage($batch=null,$mobile=null)
         {
                $reports = array();
                if($batch != null && $batch != "")
                {
                        //set the parameters
                        $params=array(
                                "batch" => $batch,
                                "mobile" => $mobile
                        );
                        
                        // mahe the request
                        $data = $this->makeRequest($this->_serverURL.$this->_messagePath,'GET',$params);
                        
                        // check if the request was successful - if it wasn't, $data['response'] will contain a tag '<error>'
                        if (strpos($data['response'], 'error') !== FALSE)
                        {
                                // parse error
                                $reports = $this->getBetweenXML($data['response'], 'error');                        
                        }
                        else{
                                // extract reports from the XML
                                $reports = $this->parseGetXML($data['response']);
                                
                                // if the request was successful, but no reports were received
                                //return the respone - usually a message e.g. 'You are not authorised to queery this batch' 
                                if(sizeof($reports)<1){
                                        $reports = $data['response'];
                                }                        
                        }
                }
                
                return $reports;
         }        


       
        /**
         * Get the credit balance of the account associated with this API user 
         *      
         * @return      string  Balance of the account in credits, positive = pre-pay, negative = post-pay
         * @return      string  Error description if the request wasn't successful         
         */
        public function getBalance()
        {
                $result = "";
                $data = $this->makeRequest($this->_serverURL.$this->_accountPath,'GET');
                
                if($data!=false)
                {               
                        // check if the request was successful - if it wasn't, $data['response'] will contain a tag '<error>'
                        if (strpos($data['response'], 'error') !== FALSE)
                        {
                                // parse error
                                $result = $this->getBetweenXML($data['response'], 'error');                        
                        }
                        else{
                                // extract credits value
                                $result = $this->getBetweenXML($data['response'], 'credits');
                        }
                                        
                        return $result;
                }        
        }
        
        
        
        /**
         * Send the request to the platform 
         * 
         * @param       string $path - URL Path for the method             
         * @param       string $method - either GET or POST
         * @param       array $params Array 
         * @return      array $result['response'] - 
         *                           ['info'] -        
         */ 
        private function makeRequest($path, $method='GET', array $params = array())
        {
                $response = null;
                 
                if($this->isValid($this->_uname) && $this->isValid($this->_pass))
                {                                     
                        // Start session (also wipes existing/previous sessions)
                        $this->ci->curl->create($path);
                        
                        // Option & Options
                        $this->ci->curl->option(CURLOPT_BUFFERSIZE, 10);
                        $this->ci->curl->options(array(CURLOPT_BUFFERSIZE => 10,CURLOPT_FAILONERROR => FALSE));
    
                        $this->ci->curl->ssl(FALSE); //dont verify the SSL certificate
                        
                        // Login to HTTP user authentication
                        $this->ci->curl->http_login($this->_uname, $this->_pass);
                        
                        // Post - If you do not use post, it will just run a GET request
                        if(strtoupper($method)=="POST")
                        {
                                $this->ci->curl->post($params);
                        }
                        else
                        {
                                // make the GET query string
                                $qry_str="?";
                                
                                foreach($params as $key=>$val){
                                    $qry_str.=$key."=".$val."&";
                                }
                                
                                $qry_str=substr($qry_str,0,strlen($qry_str)-1);
                                
                                $this->ci->curl->option(CURLOPT_HTTPGET, TRUE);
                                $this->ci->curl->option(CURLOPT_URL, $path.$qry_str);
                        }
                                            
                        
                        // Execute - returns response
                        $result['response'] =  $this->ci->curl->execute();
                                                               
                        // Errors
                        //$result['error_code'] = $this->ci->curl->error_code; // int
                        //$result['error_string'] = $this->ci->curl->error_string;
                        
                        // Information
                        $result['info'] = $this->ci->curl->info; // array
                                           
                }
                else{
                        $result['response'] = "<error>Username/password is invalid or not set</error>";
                        $result['info']['http_code'] = "201"; //can be any but 200
                }
                
                return $result;
        }
        
        
        

        /**
         * Checks if the variable in not null/empty/empty string 
         *      
         * @return      boolean   valid = true, invalid = false
         */
        private function isValid($var)
        {       if($var==null || empty($var) || implode('',explode(' ',$var))=="")
                {
                        return false;
                } 
                
                return true;        
        }
        
        /**
         * Parses the GET response from the API 
         * 
         * @param       string  $mxl - a response string in XML format 
         * @return      array   $result containing one or more reports (arrays) - described above the 'queryMessage' fuction        
         */
                 
        private function parseGetXML($xml){
                $result = array();
                $batch = $this->getBetweenXML($xml,"batch");
                if($batch!="")
                {
                        $reports = array();
                        $param1 = $this->getBetweenXML($xml,"param1");
                        $param2 = $this->getBetweenXML($xml,"param2");
                        $reps = $this->getBetweenXML($xml,"report");
                        
                        // if more than one report was found
                        if(!(is_array($reps))){
                                $reports[0]=$reps;
                        }
                        else
                        {
                                $reports = $reps;
                        }
                        //print_r($reports);
                        $i = 0;
                        foreach($reports as $report)        
                        {
                                $result[$i]['batch'] = $batch;
                                $result[$i]['param1'] = $param1;
                                $result[$i]['param2'] = $param2;
                                $result[$i]['mobile'] = $this->getBetweenXML($report,"mobile");
                                $result[$i]['code'] = $this->getBetweenXML($report,"code");
                                $result[$i]['time'] = $this->getBetweenXML($report,"time");
                                $result[$i]['credits'] = $this->getBetweenXML($report,"credits");
                                $result[$i]['status'] = $this->getBetweenXML($report,"status");
                                
                                $i++; 
                        }
                }
                return $result;
        }



        /**
         * A helper funtion to extract a string between opening and closing tags 
         * 
         * @param       string  $mxl - a string in XML format
         * @param       string  $key - the tag          
         * @return      string/array   $result containing a string or an array of strings        
         */
                 
        private function getBetweenXML($xml, $key){
                $result = "";
                $a = explode("<".$key.">", $xml);
                if(sizeof($a)>1)
                {            
                        if(sizeof($a)==2) //single entry
                        {
                                $b = explode("</".$key.">", $a[1]);
                                if(sizeof($b)>0){
                                        $result = $b[0];
                                }                    
                        }
                        else              // multiple entries - return an array                
                        {
                                $result=array();
                                foreach($a as $rep)
                                {
                                        $b = explode("</".$key.">", $rep);
                                        if(sizeof($b)>1){
                                                $result[] = $b[0];
                                        }     
                                }
                        }                
                }
                
                return $result;
        }        

	}