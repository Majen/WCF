<?php
namespace wcf\util;
use wcf\system\WCF;

/**
 * Contains user-related functions.
 * 
 * @author 	Marcel Werk
 * @copyright	2001-2009 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	util
 * @category 	Community Framework
 */
class UserUtil {
	protected static $privateIpList = array("/^0\./", "/^127\.0\.0\.1/", "/^192\.168\..*/", "/^172\.16\..*/", "/^10..*/", "/^224..*/", "/^240..*/");
	
	/**
	 * Returns true, if the given name is a valid username.
	 * 
	 * @param	string		$name		username
	 * @return 	boolean
	 */
	public static function isValidUsername($name) {
		// check illegal characters
		if (!preg_match('!^[^,\n]+$!', $name)) {
			return false;
		}
		// check long words
		$words = preg_split('!\s+!', $name, -1, PREG_SPLIT_NO_EMPTY);
		foreach ($words as $word) {
			if (StringUtil::length($word) > 20) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * Returns true, if the given username is available.
	 * 
	 * @param	string		$name		username
	 * @return 	boolean
	 */
	public static function isAvailableUsername($name) {
		$sql = "SELECT 	COUNT(username) AS count
			FROM 	wcf".WCF_N."_user
			WHERE 	username = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($name));
		$row = $statement->fetchArray();
		return $row['count'] == 0;
	}
		
	/**
	 * Returns true, if the given e-mail is a valid address.
	 * @see http://www.faqs.org/rfcs/rfc821.html
	 * 
	 * @param	string		$email
	 * @return 	boolean
	 */
	public static function isValidEmail($email) {
		// local-part
		$c = '!#\$%&\'\*\+\-\/0-9=\?a-z\^_`\{\}\|~';
		$string = '['.$c.']*(?:\\\\[\x00-\x7F]['.$c.']*)*';
		$localPart = $string.'(?:\.'.$string.')*';
		
		// domain
		$name = '[a-z0-9](?:[a-z0-9-]*[a-z0-9])?';
		$domain = $name.'(?:\.'.$name.')*\.[a-z]{2,}';
		
		// mailbox
		$mailbox = $localPart.'@'.$domain;
		
		return preg_match('/^'.$mailbox.'$/i', $email);
	}
	
	/**
	 * Returns true, if the given email address is available.
	 * 
	 * @param	string		$email		email address
	 * @return 	boolean
	 */
	public static function isAvailableEmail($email) {
		$sql = "SELECT 	COUNT(email) AS count
			FROM 	wcf".WCF_N."_user
			WHERE 	email = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($email));
		$row = $statement->fetchArray();
		return $row['count'] == 0;
	}
	
	/**
	 * Returns the user agent of the client.
	 * 
	 * @return	string
	 */
	public static function getUserAgent() {
		if (isset($_SERVER['HTTP_USER_AGENT'])) return substr($_SERVER['HTTP_USER_AGENT'], 0, 255);
		return '';
	}
	
	/**
	 * Returns the ipv6 address of the client.
	 *
	 * @return 	string		ipv6 address
	 */
	public static function getIpAddress() {
		$REMOTE_ADDR = '';
		if (isset($_SERVER['REMOTE_ADDR'])) $REMOTE_ADDR = $_SERVER['REMOTE_ADDR'];
		
		// darwin fix
		if ($REMOTE_ADDR == '::1' || $REMOTE_ADDR == 'fe80::1') {
			$REMOTE_ADDR = '127.0.0.1'; 
		}
		
		$REMOTE_ADDR = self::convertIPv4To6($REMOTE_ADDR);
		
		return $REMOTE_ADDR;
	}
	
	/**
	 * Converts given ipv4 to ipv6.
	 * 
	 * @param	string		$ip
	 * @return	string
	 */
	public static function convertIPv4To6($ip) {
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
			// given ip is already ipv6
			return $ip;
		}
		
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
			// invalid ip given
			return '';
		}
		
		$ipArray = array_pad(explode('.', $ip), 4, 0);
		$part7 = base_convert(($ipArray[0] * 256) + $ipArray[1], 10, 16);
		$part8 = base_convert(($ipArray[2] * 256) + $ipArray[3], 10, 16);
		return '::ffff:'.$part7.':'.$part8;
	}
	
	/**
	 * Returns the request uri of the active request.
	 * 
	 * @return	string
	 */
	public static function getRequestURI() {
		$REQUEST_URI = '';
		/*if (!empty($_SERVER['REQUEST_URI'])) {
			$REQUEST_URI = $_SERVER['REQUEST_URI'];
		}
		else {*/
			if (!empty($_SERVER['ORIG_PATH_INFO']) && strpos($_SERVER['ORIG_PATH_INFO'], '.php') !== false) {
				$REQUEST_URI = $_SERVER['ORIG_PATH_INFO'];
			}
			else if (!empty($_SERVER['ORIG_SCRIPT_NAME'])) {
				$REQUEST_URI = $_SERVER['ORIG_SCRIPT_NAME'];
			}
			else if (!empty($_SERVER['SCRIPT_NAME'])) {
				$REQUEST_URI = $_SERVER['SCRIPT_NAME'];
			}
			else if (!empty($_SERVER['PHP_SELF'])) {
				$REQUEST_URI = $_SERVER['PHP_SELF'];
			}
			else if (!empty($_SERVER['PATH_INFO'])) {
				$REQUEST_URI = $_SERVER['PATH_INFO'];
			}
			if (!empty($_SERVER['QUERY_STRING'])) {
				$REQUEST_URI .= '?'.$_SERVER['QUERY_STRING'];
			}
		//}
		
		//if (!strstr($REQUEST_URI, '.')) $REQUEST_URI = 'index.php';
		
		return substr(FileUtil::unifyDirSeperator($REQUEST_URI), 0, 255);
	}
}
