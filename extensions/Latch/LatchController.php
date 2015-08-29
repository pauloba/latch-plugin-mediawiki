<?php
/**
* Plugin Name: latch
* Plugin URI: http://www.elevenpaths.com
* Description: Latch MediaWiki integration
* Author: Paula - paula.rm@gmail.com
* Version: 1.0
* Compatibility: MediaWiki 1.23.8
*/


global $wgUser;//used to get the ID of the user currently logged into mediawiki
class LatchController 
{
	/**
	 * Sends the OTP writen by the user in the form to the Latch server to check it
	 * if OK stores the appId and secret in the Latch server.
	 * Receives the accountId from the Latch server and stores it in the Mediwiki DB.
	 * @param OTP sent to the mobile phone of the user and writen by user in the Mediawiki pairing form
	 * @ret 1: pairing OK, -1: pairing error
	 */ 
    public static function doPair( $otp ) 
    {
		$api = new Latch( LatchConfig::appId, LatchConfig::secret );  //creation of a Latch API object
        $response = $api->pair( $otp ); //send the OTP writen by the user in the textbox
        $data = $response->getData( );
        echo( $data->accountId );
        if(  !is_null( $data ) && property_exists( $data, "accountId" )  ) //if the Latch API object contains the accountId
        {
            $accountId = $data->accountId;
            dbHelper::storeAccountId( $accountId );
            $toRet=1; //return value=1, pairing process successful
        }
        $toRet=-1; //return value=-1, error during pairing process
        return $toRet;
    }
	/**
	 * Removes the user data from the Latch server and the Mediawiki DB (unpairs the service)
	 * @ret 1: unpairing OK, -1: unpairing error
	 */ 
    public static function doUnpair( ) 
    {
		global $wgUser; //mediawiki global var to get the userID that is currently logged into mediawiki
		//if(  dbHelper::isPaired( $wgUser->getId() )  ) 
		//{
			$api = dbHelper::getLatchApi();
			$accountId = dbHelper::getAccountId( $wgUser->getId() );
				if( $accountId!=null )//&& $accountId != '' ) 
				{
					$api->unpair( $accountId );
					dbHelper::removeAccountId( $wgUser->getId() );
					$toRet=1; //return value=1, unpairing process successful					
				}
		//}
		$toRet=-1;//return value=-1, error during unpairing process
		return $toRet;
    }
    
    /**
     * Checks the status of a digital latch, or the status of an operation given as a parameter
     */
    public static function checkLatchStatus( ) 
    {
		$status = "";
		global $wgUser; //mediawiki global var to get the userID that is currently logged into mediawiki
        $accountId = dbHelper::getAccountId( $wgUser->getId() );
        if( isset($accountId) && !empty($accountId) ) //if an accountId for the current user exists: is paired
        {
            $api = dbHelper::getLatchApi(); //create a Latch API instance
            $status = $api->status( $accountId ); //get the status of the latch for current user
            if( !empty($status) && $status->getData()!=null ) 
            {
                $operations = $status->getData()->operations;
                $operation = $operations->{LatchConfig::appId};
                $toRet = $operation->status;
                $toRet = (string)($toRet);
                return $toRet; //return $operation->status;
            }
        }        
        return "on"; //other case return the latch status is open << if there's conectivity problems better to let it open than lock the user out
    }
}
