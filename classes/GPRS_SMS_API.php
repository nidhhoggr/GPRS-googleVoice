<?php

/**
 * GPRS_SMS_API 
 * 
 * This class has some loose coupling with PhpSerial, a class for interfacing 
 * a device connected to a computer. the purpose of this class is to interface
 * SMS functionality of the connected GPRS shield.
 *  
 * @abstract
 * @package 
 * @version $id$
 * @copyright 
 * @author Joseph Persie <joseph@supraliminalsolutions.com> 
 * @license 
 */
abstract class GPRS_SMS_API
{
    protected $sms_ids = array(1,2,3,4,5);

    protected $sms_last_index = 1;

    /**
     * __construct
     * 
     * @param PhpSerial $serial 
     * @param mixed $device 
     * @param int $baud 
     * @access protected
     * @return void
     */
    function __construct(PhpSerial $serial, $device, $baud = 9600)
    {
        $serial->deviceSet($device);

        $serial->confBaudRate($baud);

        $serial->deviceOpen();

        $this->serial = $serial;

        $this->init();
    }

    /**
     * boot_load
     * 
     * @access public
     * @return void
     */
    function boot_load()
    {
        $this->serial->sendMessage("AT\r",1);
    }

    /**
     * setSmsLastIndex
     * 
     * @param mixed $sms_last_index 
     * @access public
     * @return void
     */
    public function setSmsLastIndex($sms_last_index)
    {
        $this->sms_last_index = $sms_last_index;
    }

    /**
     * setSmsIds
     * 
     * @param array $sms_ids 
     * @access public
     * @return void
     */
    public function setSmsIds($sms_ids = array())
    {
        $this->sms_ids = $sms_ids; 
    }

    /**
     * getSerial
     * 
     * @access public
     * @return PhpSerial $serial
     */
    public function getSerial()
    {
        return $this->serial;
    }

    /**
     * _getSmsIds
     * 
     * @access protected
     * @return array $sms_ids
     */
    protected function _getSmsIds()
    {
        return $this->sms_ids;
    }   

    /**
     * _getSmsLastIndex
     * 
     * @access protected
     * @return int $sms_last_index
     */
    protected function _getSmsLastIndex()
    {
        return $this->sms_last_index;
    }

    /**
     * listAllIncomingSMSBySmsIds
     * 
     * @access public
     * @return void
     */
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

    /**
     * listAllIncomingSMSFromLastIndex
     * 
     * @param mixed $sms_processor 
     * @access public
     * @return void
     */
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

    /**
     * _saveSms
     * 
     * @param mixed array $sms 
     * @abstract
     * @access protected
     * @return void
     */
    abstract protected function _saveSms($sms);

    /**
     * _readSms
     * 
     * @param mixed $sms_id 
     * @param mixed $deviceBuffer 
     * @access private
     * @return void
     */
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

    /**
     * _getSmsInfoKeys
     * 
     * @access protected
     * @return array $sms_info_keys
     */
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
