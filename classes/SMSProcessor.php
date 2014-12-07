<?php

interface SMSProcessor
{

    function init();
    function processIncomingSMS();
}
