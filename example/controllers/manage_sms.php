<?php

class Manage_sms extends CI_Controller {
	private $firstrun=false;

	function Manage_sms()
	{
		#parent::Controller();	
		parent::__construct();
        $this->load->library('sms');
	}
	
    function index()
    {
            $viewdata['mobile'] = ""; 
            $viewdata['message'] = "Testing SMS library";
            
            $viewdata['sender'] = "SMS";
            $viewdata['function'] = "send";
            $this->load->view('sms',$viewdata);        
    }
    
    
	function send()
	{        
            $mobiles = $this->input->post('mobile'); 
            $message = $this->input->post('message');
            $sender = $this->input->post('sender');
            
            $result = $this->sms->sendMessage($mobiles, $message, $sender);
            
            $viewdata['mobile'] = $mobiles; 
            $viewdata['message'] = $message;
            $viewdata['sender'] = $sender;
            $viewdata['result'] = $result;
            $viewdata['function'] = "query";
            
            $this->load->view('sms',$viewdata);
                        
	}    

	function query()
	{        
            $batch = $this->input->post('batch');            
            $result = $this->sms->queryMessage($batch);
            
            $viewdata['result'] = $result;
            $viewdata['function'] = "get_balance";
            
            $this->load->view('sms',$viewdata);            
         
	}    

	function getBalance()
	{                    
            $result = $this->sms->getBalance();
            $viewdata['result'] = $result;
            $viewdata['function'] = "print_balance";
            
            $this->load->view('sms',$viewdata);         
	} 

}

/* End of file manage_sms.php */
/* Location: ./system/application/controllers/manage_sms.php */