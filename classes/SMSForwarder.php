<?php

class SMSForwarder extends GPRS_SMS_API implements SMSProcessor
{
    const AUDIO_DIR = 'audio';

    const GOOGLE_TRANSLATE_API_CALL = 'http://translate.google.com/translate_tts?ie=UTF-8&tl=en&q=';

    public function init()
    {   
        R::setup('mysql:host=localhost;dbname=gprs','root','');
    }

    protected function _saveSms($sms)
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

    public function setSmsLastIndex()
    {
        $lastSms = R::findOne('sms', ' ORDER BY id DESC LIMIT 1');

        $smsLastIndex = $lastSms->index + 1;

        parent::setSmsLastIndex($smsLastIndex);
    }
}
