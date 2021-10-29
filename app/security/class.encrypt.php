<?php

namespace App\Security;

/**
 * @author
 * Web Design Enterprise
 * Website: www.webdesignenterprise.com
 * E-mail: info@webdesignenterprise.com
 *
 * @copyright
 * This work is licensed under the Creative Commons Attribution-Noncommercial-No Derivative Works 3.0 United States License.
 * To view a copy of this license, visit http://creativecommons.org/licenses/by-nc-nd/3.0/legalcode
 *
 * Be aware, violating this license agreement could result in the prosecution and punishment of the infractor.
 *
 * @copyright 2002- date('Y') Web Design Enterprise Corp. All rights reserved.
 */

final class Encrypt
{
    const CIPHER = 'aes-128';
    const KEY_BYTE_SIZE = 32;
    const CIPHER_MODE = 'cbc';
    const HASH_FUNCTION = 'sha256';
    const MAC_BYTE_SIZE = 32;
    const ENCRYPTION_INFO = 'DefusePHP|KeyForEncryption';
    const AUTHENTICATION_INFO = 'DefusePHP|KeyForAuthentication';

    public static function create_new_random_key()
    {
        return self::secure_random(self::KEY_BYTE_SIZE);
    }

    public static function Encrypt($plaintext, $key)
    {
        if (self::our_strlen($key) !== self::KEY_BYTE_SIZE) {
            $key = self::secure_random(self::KEY_BYTE_SIZE);
        }
        
        $method = self::CIPHER.'-'.self::CIPHER_MODE;

        self::ensure_function_exists('openssl_get_cipher_methods');
        
        if (in_array($method, openssl_get_cipher_methods()) === false) {
            throw new \Exception("Cipher method not supported.");
        }
        
        // Generate a sub-key for encryption.
        $keysize = self::KEY_BYTE_SIZE;
        $ekey = self::HKDF(self::HASH_FUNCTION, $key, $keysize, self::ENCRYPTION_INFO);
        
        // Generate a random initialization vector.
        self::ensure_function_exists("openssl_cipher_iv_length");
        $ivsize = openssl_cipher_iv_length($method);

        if ($ivsize === false || $ivsize <= 0) {
            throw new \Exception();
        }

        $iv = self::secure_random($ivsize);
        $ciphertext = $iv.self::PlainEncrypt($plaintext, $ekey, $iv);
        // Generate a sub-key for authentication and apply the HMAC.
        $akey = self::HKDF(self::HASH_FUNCTION, $key, self::KEY_BYTE_SIZE, self::AUTHENTICATION_INFO);
        $auth = hash_hmac(self::HASH_FUNCTION, $ciphertext, $akey, true);
        $ciphertext = $auth.$ciphertext;

        return urlencode(base64_encode(rtrim($ciphertext)));
    }

    public static function Decrypt($ciphertext, $key)
    {
        $ciphertext = base64_decode(urldecode($ciphertext));

        $method = self::CIPHER.'-'.self::CIPHER_MODE;

        self::ensure_function_exists('openssl_get_cipher_methods');
        
        if (in_array($method, openssl_get_cipher_methods()) === false) {
            throw new \Exception("Cipher method not supported.");
        }
        
        // Extract the HMAC from the front of the ciphertext.
        if (self::our_strlen($ciphertext) <= self::MAC_BYTE_SIZE) {
            throw new \Exception();
        }
        
        $hmac = self::our_substr($ciphertext, 0, self::MAC_BYTE_SIZE);
        
        if ($hmac === false) {
            throw new \Exception();
        }
        
        $ciphertext = self::our_substr($ciphertext, self::MAC_BYTE_SIZE);
        
        if ($ciphertext === false) {
            throw new \Exception();
        }
        
        // Regenerate the same authentication sub-key.
        $akey = self::HKDF(self::HASH_FUNCTION, $key, self::KEY_BYTE_SIZE, self::AUTHENTICATION_INFO);
        
        if (self::VerifyHMAC($hmac, $ciphertext, $akey)) {
            // Regenerate the same encryption sub-key.
            $keysize = self::KEY_BYTE_SIZE;
            $ekey = self::HKDF(self::HASH_FUNCTION, $key, $keysize, self::ENCRYPTION_INFO);
            
            // Extract the initialization vector from the ciphertext.
            self::ensure_function_exists("openssl_cipher_iv_length");
            $ivsize = openssl_cipher_iv_length($method);

            if ($ivsize === false || $ivsize <= 0) {
                throw new \Exception();
            }
            if (self::our_strlen($ciphertext) <= $ivsize) {
                throw new \Exception();
            }
            
            $iv = self::our_substr($ciphertext, 0, $ivsize);
            
            if ($iv === false) {
                throw new \Exception();
            }
            
            $ciphertext = self::our_substr($ciphertext, $ivsize);
            
            if ($ciphertext === false) {
                throw new \Exception();
            }

            $plaintext = self::PlainDecrypt($ciphertext, $ekey, $iv);
            
            return $plaintext;
        }
        
        throw new \Exception();
    }

    public static function valPasswd($passwd, $encrypted)
    {
        if (!empty($passwd) && !empty($encrypted)) {
            if (function_exists('password_verify')) {
                return password_verify($passwd, $encrypted);
            }
            
            $stack = explode('|', $encrypted);

            if (sizeof($stack) != 2) {
                return false;
            }
                
            if (md5($stack[1].$passwd) == $stack[0]) {
                return true;
            }
        }
        
        return false;
    }
    
    public static function encryptPasswd($passwd)
    {
        if (function_exists('password_hash')) {
            return password_hash($passwd, PASSWORD_DEFAULT);
        }
        
        $newPassword = '';

        for ($i = 0; $i < 10; $i++) {
            $newPassword .= mt_rand();
        }

        $salt = substr(md5($newPassword), 0, 2);
        $newPassword = md5($salt.$passwd).'|'.$salt;
            
        return $newPassword;
    }

    private static function PlainEncrypt($plaintext, $key, $iv)
    {
        $method = self::CIPHER.'-'.self::CIPHER_MODE;
        self::EnsureConstantExists("OPENSSL_RAW_DATA");
        self::ensure_function_exists("openssl_encrypt");

        $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);

        if ($ciphertext === false) {
            throw new \Exception();
        }

        return $ciphertext;
    }

    private static function PlainDecrypt($ciphertext, $key, $iv)
    {
        $method = self::CIPHER.'-'.self::CIPHER_MODE;

        self::EnsureConstantExists("OPENSSL_RAW_DATA");
        self::ensure_function_exists("openssl_encrypt");

        $plaintext = openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);

        if ($plaintext === false) {
            throw new \Exception();
        }

        return $plaintext;
    }
    /*
    * Returns a random binary string of length $octets bytes.
    */
    private static function secure_random($octets)
    {
        self::ensure_function_exists('openssl_random_pseudo_bytes');
        $secure = false;
        $random = openssl_random_pseudo_bytes($octets, $secure);

        if ($random === false || $secure === false) {
            throw new \Exception();
        }
         
        return $random;
    }
    /*
    * Use HKDF to derive multiple keys from one.
    */
    private static function HKDF($hash, $ikm, $length, $info = '', $salt = null)
    {
        // Find the correct digest length as quickly as we can.
        $digest_length = self::MAC_BYTE_SIZE;
        
        if ($hash != self::HASH_FUNCTION) {
            $digest_length = self::our_strlen(hash_hmac($hash, '', '', true));
        }
        
        // Sanity-check the desired output length.
        if (empty($length) || !is_int($length) || $length < 0 || $length > 255 * $digest_length) {
            throw new \Exception();
        }
        
        // "if [salt] not provided, is set to a string of HashLen zeroes."
        if (is_null($salt)) {
            $salt = str_repeat("\x00", $digest_length);
        }
        
        // HKDF-Extract:
        // PRK = HMAC-Hash(salt, IKM)
        // The salt is the HMAC key.
        $prk = hash_hmac($hash, $ikm, $salt, true);
        
        // HKDF-Expand:
        // This check is useless, but it serves as a reminder to the spec.
        if (self::our_strlen($prk) < $digest_length) {
            throw new \Exception();
        }
        
        // T(0) = ''
        $t = '';
        $last_block = '';

        for ($block_index = 1; self::our_strlen($t) < $length; $block_index++) {
            // T(i) = HMAC-Hash(PRK, T(i-1) | info | 0x??)
            $last_block = hash_hmac($hash, $last_block.$info.chr($block_index), $prk, true);

            // T = T(1) | T(2) | T(3) | ... | T(N)
            $t .= $last_block;
        }

        // ORM = first L octets of T
        $orm = self::our_substr($t, 0, $length);

        if ($orm === false) {
            throw new \Exception();
        }

        return $orm;
    }

    private static function VerifyHMAC($correct_hmac, $message, $key)
    {
        $message_hmac = hash_hmac(self::HASH_FUNCTION, $message, $key, true);

        // We can't just compare the strings with '==', since it would make
        // timing attacks possible. We could use the XOR-OR constant-time
        // comparison algorithm, but I'm not sure if that's good enough way up
        // here in an interpreted language. So we use the method of HMACing the
        // strings we want to compare with a random key, then comparing those.
        // NOTE: This leaks information when the strings are not the same
        // length, but they should always be the same length here. Enforce it:
        
        if (self::our_strlen($correct_hmac) !== self::our_strlen($message_hmac)) {
            throw new \Exception();
        }
        
        $blind = self::create_new_random_key();
        $message_compare = hash_hmac(self::HASH_FUNCTION, $message_hmac, $blind);
        $correct_compare = hash_hmac(self::HASH_FUNCTION, $correct_hmac, $blind);
        
        return $correct_compare === $message_compare;
    }
    
    /* WARNING: Do not call this function on secrets. It creates side channels. */
    private static function hexToBytes($hex_string)
    {
        return pack("H*", $hex_string);
    }

    private static function EnsureConstantExists($name)
    {
        if (!defined($name)) {
            throw new \Exception();
        }
    }

    private static function ensure_function_exists($name)
    {
        if (!function_exists($name)) {
            throw new \Exception();
        }
    }
    /*
    * We need these strlen() and substr() functions because when
    * 'mbstring.func_overload' is set in php.ini, the standard strlen() and
    * substr() are replaced by mb_strlen() and mb_substr().
    */
    private static function our_strlen($str)
    {
        if (function_exists('mb_strlen')) {
            $length = mb_strlen($str, '8bit');

            if ($length === false) {
                throw new \Exception();
            }

            return $length;
        }
         
        return strlen($str);
    }

    private static function our_substr($str, $start, $length = null)
    {
        if (function_exists('mb_substr')) {
            // mb_substr($str, 0, NULL, '8bit') returns an empty string on PHP
            // 5.3, so we have to find the length ourselves.
            if (!isset($length)) {
                if ($start >= 0) {
                    $length = self::our_strlen($str) - $start;
                } else {
                    $length = -$start;
                }
            }

            return mb_substr($str, $start, $length, '8bit');
        }
        
        // Unlike mb_substr(), substr() doesn't accept NULL for length
        if (isset($length)) {
            return substr($str, $start, $length);
        }
         
        return substr($str, $start);
    }
}
