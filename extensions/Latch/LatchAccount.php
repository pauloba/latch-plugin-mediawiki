<?php
/**
* Plugin Name: latch
* Plugin URI: http://www.elevenpaths.com
* Description: Latch MediaWiki integration
* Author: Paula - paula.rm@gmail.com
* Version: 1.0
* Compatibility: MediaWiki 1.23.8
*/


$dir = __DIR__ . '/';  //set the internationalization files directory for the extension
$wgMessagesDirs['latch'] = __DIR__ . '/i18n';

$wgHooks['UserLoadAfterLoadFromSession'][] = 'onUserLoadAfterLoadFromSession';
$wgHooks['GetPreferences'][] = 'onPreferencesForm';//to add buttons, text box, to the 2FA tab in user's preferences
$wgHooks['PreferencesFormPreSave'][] = 'onPreferencesFormPreSave';
$wgHooks['UserLogout'][] = 'onLogout';

/**
 * This hook is called when the user logs off Mediawiki
 * to unset the session variable latchStatus.
 * This variable is used in order to check only once 
 * during the logging if the user has an active latch that do not permit the login
 */
function onLogout( &$user ) 
{ 	
		global $wgRequest;
		$wgRequest->setSessionData("latchStatus", null);		
}


/**
 * Called to authenticate users on external/environmental means; 
 * occurs after session is loaded
 */ 
function onUserLoadAfterLoadFromSession( $user ) 
{

	global $wgRequest; //used to set a session variable, when the user logs into MW, check once if there is a latch and save it in the variable
	
	if( $user->getId() > 0 ) // user logged in MediaWiki
	{
		$latchStatus = $wgRequest->getSessionData("latchStatus");// have we checked the 2FA status for this session yet?
		
		if( $latchStatus==NULL ) 
		{
			// no, the 2FA should be checked
			//if( (string)LatchController::checkLatchStatus( ) == "on" ) 
			if( LatchController::checkLatchStatus( ) == "on" ) 
			{
				// 2FA says ok. Save this in session in order to avoid
				// further checks in this session
				$wgRequest->setSessionData("latchStatus", true);
			} 
			else // 2FA says no. Logout the user inmediatly.
			{	$user->logout();	}
		} 
		else {	
			// the 2FA is in session. No need to check again.	
			}
	}
	return true; // Required return value of a hook function.
}

/**
 * This hook is invoked through getPreferences(...) function from /includes/Preferences.php
 * the onPreferencesForm function displays a tab in the user's preferences options 
 * with 2FAuthentication settings (pair/unpair account)
 */ 
function onPreferencesForm( $user, &$preferences ) 
{
	
	if( dbHelper::isPaired( ) ) //if the user is paired render the view with unpair options in the form
	{	
		$preferences['formPairedButton'] = array(
			'type' => 'submit', 
			'section' => '2FA/Latch',//'2nd factor authentication
			'id'=>'unpairButton',
			'default' => wfMessage("prefs-2FA-button-unpair"),
		);		
	
	}

	else //if the user is not paired render the view with pair options in the form
	{
		$preferences['formUnpairedTextbox'] = array(
			'type' => 'text', 
			'section' => '2FA/Latch',
			'label-message' => 'prefs-2FA-label',
			'maxlength' => '6', //OTP is maximum 6 characters.
			'default' => '',//clear the last user input
			'id'=>'pairingToken',
		);
		
		$preferences['formUnpairedButton'] = array(
			'type' => 'submit', 
			'section' => '2FA/Latch',
			'id'=>'pairButton',
			'default' => wfMessage("prefs-2FA-button-pair"),			
		);
		
		$preferences['formUnpairedMessage'] = array (
			'type' => 'info',
			'section' => '2FA/Latch',
			'help-message' => 'prefs-2FA-help',
		);
	}

	return true; // Required return value of a hook function.
}

/**
 * Allows extension to modify what preferences will be saved
 */ 
function onPreferencesFormPreSave( $formData, $form, $user, &$result ) 
{	
	$errorMsg = '';
	//the user has not paired Mediawiki account with Latch
	if(  !dbHelper::isPaired()  ) 
	{		
        $oneTimePassword = $formData["formUnpairedTextbox"]; //get the OTP writen by the user in the textbox form
        LatchController::doPair($oneTimePassword);

	}
	//the user has paired Mediawiki account with Latch
	else if( dbHelper::isPaired() && isset( $_POST["wpformPairedButton"] )  ) 
	{	 
		$responseUnpair = LatchController::doUnpair();
		if( $responseUnpair == -1 )
        {
			$errorMsg = "Error durante proceso de despareado, por favor vuelve a intentarlo.";
			if ( $errorMsg <> '' ) 
			{
				return $errorMsg;
			}
		}
		 
	}
	
	return true;  // Required return value of a hook function.
}

