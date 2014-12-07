<?php

/**
 * SMSForwarder 
 *
 * This class is not only a concrete implementation of an SMSProcessor
 * but it also acts to decouple database persietnce from abstracted functionality
 * in the GPRS_SMS_API. The main purpose of this class is to play Google Tranlsate
 * MP3's of the lastest text message recieved and them as processed.
 *
 * @uses GPRS_SMS_API
 * @uses SMSProcessor
 * @package 
 * @version $id$
 * @copyright 
 * @author Joseph Persie <joseph@supraliminalsolutions.com> 
 * @license 
 */
class SMSForwarder extends GPRS_SMS_API implements SMSProcessor
{
    const AUDIO_DIR = 'audio';

    const GOOGLE_TRANSLATE_API_CALL = 'http://translate.google.com/translate_tts?ie=UTF-8&tl=en&q=';

    /**
     * init
     * 
     * @access public
     * @return void
     */
    public function init()
    {   
        R::setup('mysql:host=localhost;dbname=gprs','root','');
    }

    /**
     * _saveSms
     * 
     * @param array $sms 
     * @access protected
     * @return void
     */
    protected function _saveSms($sms = array())
    {
        $sms_bean = R::dispense('sms');

        $sms_info = $sms['info'];

        $sms_bean->index = $sms_info['index'];
        $sms_bean->status = $sms_info['status'];
        $sms_bean->recipient_phone_number = $sms_info['recipient_phone_number'];
        $sms_bean->date = $sms_info['date'];
        $sms_bean->time = $sms_info['time'];
        $sms_bean->msg = $sms['msg'];
        $sms_bean->is_sent = FALSE;

        $id = R::store($sms_bean); 
    }

    /**
     * processIncomingSMS
     * 
     * @access public
     * @return void
     */
    public function processIncomingSMS()
    {
        $sendableSms = R::find('sms', ' is_sent = 0');

        foreach($sendableSms as $sms_to_send)
        {
            $message = urlencode($sms_to_send->msg);

            $audio_file = self::AUDIO_DIR . "/output-{$sms_to_send->id}.mp3";

            shell_exec("wget -q -U Mozilla -O {$audio_file} '".self::GOOGLE_TRANSLATE_API_CALL."{$message}'");

            if(file_exists($audio_file))
            {
                exec("mplayer " . $audio_file);

                exec("rm -rf $audio_file");

                $sms_to_send->is_sent = TRUE;

                R::store($sms_to_send);
            }
        }
    }

    /**
     * setSmsLastIndex
     * 
     * @access public
     * @return void
     */
    public function setSmsLastIndex()
    {
        $lastSms = R::findOne('sms', ' ORDER BY id DESC LIMIT 1');

        $smsLastIndex = $lastSms->index + 1;

        parent::setSmsLastIndex($smsLastIndex);
    }
}
