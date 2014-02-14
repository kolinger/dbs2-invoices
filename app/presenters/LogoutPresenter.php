<?php

namespace App\Presenters;


/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class LogoutPresenter extends ProtectedPresenter
{

	public function actionDefault()
	{
		$this->user->logout();
		$this->redirect('Login:');
	}
}
