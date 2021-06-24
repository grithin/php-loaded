<?php
namespace Grithin;

/*
use `defuse/php-encryption` for IV, HMAC symmetric encryptiong:
	/** key generation */
	$key = \Defuse\Crypto\Key::createNewRandomKey()->saveToAsciiSafeString()

	/** encrypt */
	$key = \Defuse\Crypto\Key::loadFromAsciiSafeString($_ENV['payments_encrypt_key']);
	$encrypted = \Defuse\Crypto\Crypto::encrypt(Tool::json_encode($card_details), $key);

	/** decrypt */
	$key = \Defuse\Crypto\Key::loadFromAsciiSafeString($_ENV['payments_encrypt_key']);
	\Defuse\Crypto\Crypto::decrypt($encrypted, $key)
*/

class Crypt{
	/**
	Use combination of assymetric and symmetric encryption to encrypt data of arbitrary size
	Asymmetric encrypt of symmetric key used to symmetrically encrypt data (since symmetric encryption does not have data size limit like asym)
	*/
	/** Testing
	$assert = function($ought, $is){
		if($ought != $is){
			throw new Exception('ought is not is : '.\Grithin\Debug::pretty([$ought, $is]));
		}
	};
	$rsa = new \phpseclib\Crypt\RSA();
	$keys = $rsa->createKey(2048);

	$plaintext = 'test123';
	$encrypted = \Grithin\Crypt::encrypt($plaintext, $keys['publickey']);
	$assert($plaintext, \Grithin\Crypt::decrypt($encrypted, $keys['privatekey']));
	*/

	function mixed_encrypt($plaintext,$public_key,$key_length=150){
		$rsa = new \phpseclib\Crypt\RSA(); # asymmetric
		$rij = new \phpseclib\Crypt\Rijndael(); # symmetric

		// Generate Random Symmetric Key
		$sym_key = \phpseclib\Crypt\Random::string($key_length);

		// Encrypt Message with new Symmetric Key
		# since a random key is being used anyway, it is unnecessary to create an IV
		$rij->setKey($sym_key);
		$ciphertext = $rij->encrypt($plaintext);
		$ciphertext = base64_encode($ciphertext);

		// Encrypted the Symmetric Key with the Asymmetric Key
		$rsa->loadKey($public_key);
		$sym_key = $rsa->encrypt($sym_key);

		// Base 64 encode the symmetric key for transport
		$sym_key = base64_encode($sym_key);
		$len = strlen($sym_key); // Get the length

		$len = dechex($len); // The first 3 bytes of the message are the key length
		$len = str_pad($len,3,'0',STR_PAD_LEFT); // Zero pad to be sure.

		// Concatenate the length, the encrypted symmetric key, and the message
		$message = $len.$sym_key.$ciphertext;

		return $message;
	}

	function mixed_decrypt($message,$private_key){
		$rsa = new \phpseclib\Crypt\RSA(); # asymmetric
		$rij = new \phpseclib\Crypt\Rijndael(); # symmetric

		// Extract the Symmetric Key
		$len = substr($message,0,3);
		$len = hexdec($len);

		$sym_key = substr($message,3,$len);

		//Extract the encrypted message
		$ciphertext = substr($message,$len + 3);
		$ciphertext = base64_decode($ciphertext);

		// Decrypt the encrypted symmetric key
		$rsa->loadKey($private_key);
		$sym_key = base64_decode($sym_key);

		$sym_key = $rsa->decrypt($sym_key);

		// Decrypt the message
		$rij->setKey($sym_key);
		$plaintext = $rij->decrypt($ciphertext);

		return $plaintext;
	}

	/** basic Rijndael encryption (no iv, no hmac, to allow for caching) */
	/** Testing
	$plaintext = 'test123';
	$encrypted = \Grithin\Crypt::simple_encrypt($plaintext, 'password');
	$assert($plaintext, \Grithin\Crypt::simple_decrypt($encrypted, 'password'));
	*/

	function simple_encrypt($plaintext, $key){
		$rij = new \phpseclib\Crypt\Rijndael();

		$key = md5($key); # the key is limited to 32 by the algorithm, so force it to that length

		// Encrypt Message with new Symmetric Key
		$rij->setKey($key);
		$ciphertext = $rij->encrypt($plaintext);
		$ciphertext = base64_encode($ciphertext);

		return $ciphertext;
	}
	/** basic Rijndael decryption (no iv) */
	function simple_decrypt($message, $key){
		$rij = new \phpseclib\Crypt\Rijndael();

		$key = md5($key); # the key is limited to 32 by the algorithm, so force it to that length

		// Decrypt the message
		$rij->setKey($key);
		$plaintext = $rij->decrypt(base64_decode($message));

		return $plaintext;
	}
}
