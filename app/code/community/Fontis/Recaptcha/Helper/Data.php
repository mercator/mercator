<?php
/**
 * Fontis Recaptcha Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 * 
 * This code has been adopted from the reCAPTCHA module available at:
 * 	http://www.google.com/recaptcha
 * The original reCAPTCHA module was written by:
 * 	Mike Crawford
 * 	Ben Maurer
 *
 * @category   Fontis
 * @package    Fontis_Recaptcha
 * @author     Denis Margetic
 * @author     Chris Norton
 * @copyright  Copyright (c) 2011 Fontis Pty. Ltd. (http://www.fontis.com.au)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Fontis_Recaptcha_Helper_Data extends Mage_Core_Helper_Abstract 
{
    const RECAPTCHA_API_SERVER_HOST = "www.google.com";
    const RECAPTCHA_API_SERVER_PATH = "/recaptcha/api";
    const RECAPTCHA_API_SECURE_SERVER = "https://www.google.com/recaptcha/api";

    /**
     * Encodes the given data into a query string format
     * @param $data - array of string elements to be encoded
     * @return string - encoded request
     */
    function _recaptcha_qsencode($data)
    {
        $req = "";
        foreach ($data as $key => $value) {
            $req .= $key . '=' . urlencode(stripslashes($value)) . '&';
        }

        // Cut the last '&'
        $req = substr($req, 0, strlen($req) - 1);
        return $req;
    }

    /**
     * Submits an HTTP POST to a reCAPTCHA server
     * @param string $host
     * @param string $path
     * @param array $data
     * @param int port
     * @return array response
     */
    function _recaptcha_http_post($host, $path, $data, $port = 80) 
    {
        $req = $this->_recaptcha_qsencode($data);

        $http_request  = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
        $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;

        $response = '';
        if (false == ($fs = @fsockopen($host, $port, $errno, $errstr, 10))) {
            die('Could not open socket');
        }

        fwrite($fs, $http_request);

        while (!feof($fs)) {
            $response .= fgets($fs, 1160); // One TCP-IP packet
        }
        fclose($fs);
        $response = explode("\r\n\r\n", $response, 2);

        return $response;
    }

    /**
     * Gets the challenge HTML (javascript and non-javascript version).
     * This is called from the browser, and the resulting reCAPTCHA HTML widget
     * is embedded within the HTML form it was called from.
     * @param string $pubkey A public key for reCAPTCHA
     * @param string $error The error given by reCAPTCHA (optional, default is null)
     * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)

     * @return string - The HTML to be embedded in the user's form.
     */
    function recaptcha_get_html($pubkey, $error = null, $use_ssl = false)
    {
	    if ($pubkey == null || $pubkey == '') {
		    return "To use reCAPTCHA you must get an API key from <a href='http://recaptcha.net/api/getkey'>http://recaptcha.net/api/getkey</a>";
	    }
	
	    if ($use_ssl) {
            $server = self::RECAPTCHA_API_SECURE_SERVER;
        } else {
            $server = 'http://' . self::RECAPTCHA_API_SERVER_HOST . self::RECAPTCHA_API_SERVER_PATH;
        }

        $errorpart = "";
        if ($error) {
           $errorpart = "&amp;error=" . $error;
        }
        return '<script type="text/javascript" src="'. $server . '/challenge?k=' . $pubkey . $errorpart . '"></script>

	    <noscript>
  		    <iframe src="'. $server . '/noscript?k=' . $pubkey . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
  		    <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
  		    <input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
	    </noscript>';
    }

    /**
      * Calls an HTTP POST function to verify if the user's guess was correct
      * @param string $privkey
      * @param string $remoteip
      * @param string $challenge
      * @param string $response
      * @param array $extra_params an array of extra variables to post to the server
      * @return ReCaptchaResponse
      */
    function recaptcha_check_answer($privkey, $remoteip, $challenge, $response, $extra_params = array())
    {
	    if ($privkey == null || $privkey == '') {
		    return "To use reCAPTCHA you must get an API key from <a href='http://recaptcha.net/api/getkey'>http://recaptcha.net/api/getkey</a>";
	    }

	    if ($remoteip == null || $remoteip == '') {
		    die("For security reasons, you must pass the remote ip to reCAPTCHA");
	    }
	
        //discard spam submissions
        if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
            return false;
        }

        $response = $this->_recaptcha_http_post(self::RECAPTCHA_API_SERVER_HOST,
                                                self::RECAPTCHA_API_SERVER_PATH . "/verify",
                                                array('privatekey' => $privkey,
                                                'remoteip' => $remoteip,
                                                'challenge' => $challenge,
                                                'response' => $response
                                                ) + $extra_params
                                               );

        $answers = explode("\n", $response[1]);

        if (trim($answers[0]) == 'true') {
            return true;
        }
        return false;
    }

    /**
     * Gets a URL where the user can sign up for reCAPTCHA. If your application
     * has a configuration page where you enter a key, you should provide a link
     * using this function.
     * @param string $domain The domain where the page is hosted
     * @param string $appname The name of your application
     */
    function recaptcha_get_signup_url($domain = null, $appname = null)
    {
	    return "http://recaptcha.net/api/getkey?" . $this->_recaptcha_qsencode(array("domain" => $domain, "app" => $appname));
    }

    function _recaptcha_aes_pad($val)
    {
	    $block_size = 16;
	    $numpad = $block_size - (strlen($val) % $block_size);
	    return str_pad($val, strlen($val) + $numpad, chr($numpad));
    }

    /**
     * Check whether or not the extension is enabled by checking whether or not the keys
     * have been entered in the system configuration settings for the extension.
     * Separating out this logic should allow other extensions to use the extension.
     * @return boolean
     */
    function isEnabled()
    {
        if (Mage::helper("core")->isModuleOutputEnabled("Fontis_Recaptcha")) {
            if (Mage::getStoreConfig("fontis_recaptcha/setup/public_key") == "" || Mage::getStoreConfig("fontis_recaptcha/setup/private_key") == "") {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    /**
     * Checks to see whether or not the reCAPTCHA form should be shown on a given page.
     * @param string $page This should correspond to the on/off system config setting
     * @param boolean $loggedIn Whether or not the "must be logged in" setting should be obeyed
     * @return boolean
     */
    function showForm($page, $loggedIn = true)
    {
        if ($this->isEnabled()) {
            if (!$loggedIn || !(Mage::getStoreConfig("fontis_recaptcha/recaptcha/when_loggedin") && (Mage::getSingleton('customer/session')->isLoggedIn()))) {
                return (bool) Mage::getStoreConfig("fontis_recaptcha/recaptcha/" . $page);
            }
        }
        return false;
    }
}
