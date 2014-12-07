<?php

/**
 * SMSProcessor 
 * 
 * @package 
 * @version $id$
 * @author Joseph Persie <joseph@supraliminalsolutions.com> 
 * @license 
 */
interface SMSProcessor
{

    /**
     * init
     * 
     * this funtion is called in the constructor of the super class
     *
     * @access public
     * @return void
     */
    function init();


    /**
     * processIncomingSMS
     * 
     * This is why the child class is implementing me.
     *
     * @access public
     * @return void
     */
    function processIncomingSMS();
}
