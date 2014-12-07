<?php
abstract class GPRS_SMS_API
{
    protected $sms_ids = array(1,2,3,4,5);

    protected $sms_last_index = 1;

    function __construct(PhpSerial $serial, $device, $baud = 9600)
    {
        $serial->deviceSet($device);

        $serial->confBaudRate($baud);

        $serial->deviceOpen();

        $this->serial = $serial;

        $this->init();
    }

    function boot_load()
    {
        $this->serial->sendMessage("AT\r",1);
    }

    public function setSmsLastIndex($sms_last_index)
    {
        $this->sms_last_index = $sms_last_index;
    }

    public function setSmsIds($sms_ids = array())
    {
        $this->sms_ids = $sms_ids; 
    }

    public function getSerial()
    {
        return $this->serial;
    }

    protected function _getSmsIds()
    {
        return $this->sms_ids;
    }   

    protected function _getSmsLastIndex()
    {
        return $this->sms_last_index;
    }
    public function listAllIncomingSMSBySmsIds()
    {
        $this->serial->sendMessage("AT+CMGF=1\r",1);

        $sms_ids = $this->_getSmsIds();

        foreach($sms_ids as $sms_id)
        {
            $this->lastCmd = "AT+CMGR=$sms_id\r";
            $this->setial->readPort(); 
            $this->serial->sendMessage($this->lastCmd,1);
            $this->_readSms($this->serial->readPort());
        }
    }

    public function listAllIncomingSMSFromLastIndex($sms_processor)
    {
        $this->serial->sendMessage("AT+CMGF=1\r",1);

        $sms_id = $this->_getSmsLastIndex();

        do
        {
            $this->lastCmd = "AT+CMGR=$sms_id\r";
            $this->serial->sendMessage($this->lastCmd,1);
            $sms = $this->_readSms($sms_id, $this->serial->readPort());

            if($sms)
            {
                $this->_saveSms($sms);
            }

            $sms_processor($sms); 

            $sms_id++;
        }
        while($sms);
    }

    abstract protected function _saveSms($sms);

    private function _readSms($sms_id, $deviceBuffer)
    {
        //the SMS query should be the last command
        if(!strstr($deviceBuffer, $this->lastCmd))
        {
            return FALSE;
        }

        $replacable = array(
            $this->lastCmd, 
            "+CMGR: ",
            "OK"
        );

        foreach($replacable as $to_replace)
        {
            $deviceBuffer = str_replace($to_replace,'',$deviceBuffer);

        }
        
        $trimmed_message = trim($deviceBuffer);

        if(empty($trimmed_message))
        {   
            return FALSE;
        }
        else
        {

            $contents = explode("\r\n", $trimmed_message);

            $info = explode(',', $contents[0]);

            $sms_info_keys = $this->_getSmsInfoKeys();

            foreach($info as $info_key=>$info_val)
            {
                $key_name = $sms_info_keys[$info_key];

                if($key_name !== "omit")
                {
                    $info[$key_name] = str_replace('"','',$info_val);
                }

                unset($info[$info_key]);
            }

            $info['index'] = $sms_id;

            $sms['info'] = $info; 

            if(empty($contents[1]))
            {
                return FALSE;
            }

            $sms['msg'] = $contents[1];
            

            return $sms;
        }
    }

    protected function _getSmsInfoKeys()
    {

        /**

        array(5) {
            [0]=>
                string(10) ""REC READ""
                [1]=>
                string(14) ""+13214277445""
                [2]=>
                string(2) """"
                [3]=>
                string(9) ""14/12/06"
                [4]=>
                string(12) "16:46:18-20""
                **/

        return array(
            'status',
            'recipient_phone_number',
            'omit',
            'date',
            'time'
        );
    }
}
