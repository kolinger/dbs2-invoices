<?php

namespace App\Model;

use Nette\Database\Context;
use Nette\Database\Table\Selection;
use Nette\Object;

/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 *
 * @property-read Context $context
 */
abstract class Facade extends Object
{

	/**
	 * @var Context
	 */
	private $context;


	/**
	 * @param Context $context
	 */
	public function __construct(Context $context)
	{
		$this->context = $context;
	}


	/**
	 * @return Context
	 */
	public function getContext()
	{
		return $this->context;
	}
}