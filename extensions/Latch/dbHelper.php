<?php
/**
* Plugin Name: latch
* Plugin URI: http://www.elevenpaths.com
* Description: Latch MediaWiki integration
* Author: Paula - 
* Version: 1.0
* Compatibility: MediaWiki 1.23.8
*/


/**
 * Database connection functionalities
 * to manage operations within the MediaWiki database
 * in order to pair/unpair user's account
 */
global $wgUser;//used to get the ID of the user currently logged into Mediawiki
class dbHelper {
	/**
	 * @ret an object Latch API
	 */
    public static function getLatchApi( ) 
    {	return new Latch(LatchConfig::appId, LatchConfig::secret);	}
	
	/**
	* Check if there is an accountId in the Mediawiki DB
	* if the accontId exists this means the Mediawiki account is paired with Latch
	* @param ID of the user that is currently logged into Mediawiki
	* @return true if MediaWiki user's account is paired with Latch false if it's not paired
	*/	
	public static function isPaired( ) //$mwUserId) {
	{
		global $wgUser;
		$userId = $wgUser->getId();
		//The next SQL query returns the accountId if the user account is paired, empty if it's not paired
		$toRet = true; //return value of the function
		$dbr =& wfGetDB( DB_SLAVE ); //DataBase connection for reading
		$res = $dbr->select(
							'latch', //$table
							array('account_id'), //$vars (columns of the table)
							array (	//$conds (WHERE ...)
							"mw_user_id = $userId",
							),
							__METHOD__	//$fname Database::select
							);
		 
		 $toStringID = '';
		 foreach( $res as $row ) {
				 $toStringID .= $row->account_id;
		 }
		 if( strval($toStringID) === '' )//if there is no ID the user is not paired
		 {	$toRet = false; } //then return false 
		 return $toRet;
	}
	
	/**
	* Stores userId and accountId in the MediaWiki DB
	* @param the Latch accountId for the actual Mediawiki user
	*/ 	 
    public static function storeAccountId($accountId)
    {
		global $wgUser;//Mediawiki global var to get the userID currently logged returns int, 0 if annonymous user
		$userId = $wgUser->getId();
		$dbw =& wfGetDB( DB_MASTER );  //DataBase connection for writing
        $res = $dbw->insert( 
							'latch', //$table
							array( //$a array of rows to insert 
									'mw_user_id' => $userId, 
									'account_id' => $accountId,
									//'otp' => "prueba",
									),
							__METHOD__
						);      
    }
    
	/**
	 * Returns the Latch account Id for the given Mediawiki user ID (the user that's logged in)
	 * @param the Id of the user currently logged into Mediawiki
	 */
    public static function getAccountId( ) //$mwUserId) {
    {
		global $wgUser;
		$userId = $wgUser->getId();
		$dbr =& wfGetDB( DB_SLAVE ); //DataBase connection for reading
		$res = $dbr->select(
							'latch', //$table
							array('account_id'), //$vars (columns of the table)
							array (	//$conds (WHERE ...)
							"mw_user_id = $userId",//$mwUserId",
							),
							__METHOD__	//$fname Database::select
							);
		 
		 $toStringId = '';
		 foreach( $res as $row ) {
				 $toStringId .= $row->account_id;
		 }
		return $toStringId;
	  }
	  
	/**
	 * Removes the Latch accountId for the given $userId (the actual user logged into Mediawiki)
	 */
	public static function removeAccountId( ) //$mwUserId) {
	{
		global $wgUser;
		$userId = $wgUser->getId();
		$dbw =& wfGetDB( DB_MASTER );  //DataBase connection for writing
		return $dbw->delete(
							'latch',
							array( "mw_user_id = $userId" ),//$mwUserId ),
							__METHOD__
							);
	}
}
