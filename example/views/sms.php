<?php 
if($function=="send")
{ 
?>
        <div style="font-weight: bold;">Test 'Send' function</div>
        <br>
        <form name="sendsms" method="post" accept-charset="utf-8" action="<?php echo $this->config->item('base_url'); ?>manage_sms/send">
        
        		<div>Mobile Number <input name="mobile" type="text" maxlength="16" value="<?php echo $mobile; ?>" /></div>
                <div>Message <textarea name="message"><?php echo $message; ?></textarea></div>
                <div>Sender <input name="sender" type="text" maxlength="16" value="<?php echo $sender; ?>" /></div>
                <div><input type="submit" value="Send SMS" /></div>       
        </form>

<?php
}

if($function=="query")
{
        if(isset($result))
        {
                echo "<div style=\"font-weight: bold;\">Send result</div>";
                echo "<div>";
                if($result!=false)
                {                
                    echo "Batch: ".$result['batch']."<br>";
                    echo "Error: ".$result['error'];
                }
                else
                {
                    echo "An error occurred when tried to send a message. Please check that all required data is set";
                }    
                echo "</div>";
        }
?>
        <br><br>
        <div style="font-weight: bold;">Test 'Query Message' function</div>
        <br>    
        <form name="querysms" method="post" accept-charset="utf-8" action="<?php echo $this->config->item('base_url'); ?>manage_sms/query">
        
        		<div>Batch <input name="batch" type="text" maxlength="10" 
                    value="<?php if(isset($result['batch'])){ echo $result['batch'];}?>" /></div>
                    
                <div><input type="submit" value="Query Status" /></div>       
        </form>    
<?php    
}

if($function=="get_balance")
{
        if(isset($result))
        {
                echo "<div style=\"font-weight: bold;\">Query result</div>";
                echo "<div>";
                print_r($result);    
                echo "</div>";
        }
?> 
        <br><br>
        <div style="font-weight: bold;">Test 'Get Balance' function</div>
        <br>   
        <form name="getbalance" method="post" accept-charset="utf-8" action="<?php echo $this->config->item('base_url'); ?>manage_sms/getBalance">
                <div><input type="submit" value="Get Balance" /></div>       
        </form>    
<?php
}

if($function=="print_balance")
{
        if(isset($result))
        {
                echo "<div style=\"font-weight: bold;\">Get Balance result</div>";
                echo "<div>";
                
                // if it's a float value
                if(preg_match("/^[-]?[0-9]+(.[0-9]+)?$/",$result))            
                {
                        echo "Current balance: ".$result." credits";
                }
                else
                {
                        // if error
                        echo $result;
                } 
                   
                echo "</div>";
        }
}
?>