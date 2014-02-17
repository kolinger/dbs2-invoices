<?php

namespace App\Model;

use Nette\Database\Context;
use Nette\Http\Session;
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
	 * @var Session
	 */
	private $session;


	/**
	 * @param Context $context
	 * @param Session $session
	 */
	public function __construct(Context $context, Session $session)
	{
		$this->context = $context;
		$this->session = $session;
	}


	/**
	 * @return Context
	 */
	public function getContext()
	{
		return $this->context;
	}


	/**
	 * @return int|NULL
	 */
	public function getSelectedCompany()
	{
		$settings = $this->session->getSection('settings');
		return $settings && isset($settings->company) ? $settings->company : NULL;
	}
}