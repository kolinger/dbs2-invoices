<?php
namespace App;

use Nette\Object;
use Nette\Utils\Strings;

/**
 * Standard password encoder - SHA-256, 1024 iterations, 8-bytes random salt
 *
 * @author Tomáš Kolinger <tomas@kolinger.name>
 */
class PasswordEncoder extends Object
{

	/**
	 * @param string $raw
	 * @param string $salt
	 * @return string
	 */
	public function encode($raw, $salt = NULL)
	{
		if ($salt === NULL) {
			$salt = Strings::random(8);
		}
		$raw = $salt . $raw;
		for ($count = 1; $count <= 1024; $count++) {
			$raw = hash('sha256', $raw);
		}
		return $salt . $raw;
	}


	/**
	 * @param string $raw
	 * @param string $encoded
	 * @return bool
	 */
	public function matches($raw, $encoded)
	{
		$salt = substr($encoded, 0, 8);
		return $this->encode($raw, $salt) === $encoded;
	}
}