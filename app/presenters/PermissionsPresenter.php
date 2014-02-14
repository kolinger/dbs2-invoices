<?php

namespace App\Presenters;

use App\BootstrapForm;
use App\VisualPaginator;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;


/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class PermissionsPresenter extends ProtectedPresenter
{

	/**
	 * @inject
	 * @var \App\Model\PermissionsFacade
	 */
	public $permissionsFacade;

	/**
	 * @inject
	 * @var \App\Model\ManagersFacade
	 */
	public $managersFacade;


	/************************ list ************************/


	public function renderDefault()
	{
		$paginator = new VisualPaginator($this, 'paginator');
		$paginator = $paginator->getPaginator();
		$paginator->itemCount = $this->permissionsFacade->count($this->user->id);
		$paginator->itemsPerPage = 20;
		$this->template->permissions = $this->permissionsFacade->findAll($paginator->offset, $paginator->itemsPerPage,
			$this->user->id);
	}


	/************************ form ************************/


	/**
	 * @return BootstrapForm
	 */
	protected function createComponentForm()
	{
		$companyId = $this->getParameter('company');
		$managerId = $this->getParameter('manager');

		$form = new BootstrapForm;

		if (!$companyId && !$managerId) {
			$items = $this->permissionsFacade->findAvailableCompaniesInPairs($this->user->id);
			$form->addSelect('companyId', 'Společnost', $items)
				->setRequired('Musíte vybrat společnost');
		}

		if (!$managerId) {
			$form->addText('manager', 'Uživatel')
				->setRequired('Musíte vyplnit uživatele');
		}

		$form->addCheckbox('roleCompany', 'Úprava společnosti');
		$form->addCheckbox('rolePermissions', 'Oprávnění');
		$form->addCheckbox('roleClients', 'Zákazníci');
		$form->addCheckbox('roleInvoices', 'Faktury');
		$form->addCheckbox('roleProducts', 'Produkty');
		$form->addCheckbox('rolePayments', 'Platby');

		$form->addSubmit('send', 'Uložit');

		$form->onSuccess[] = callback($this, 'formSubmitted');
		return $form;
	}


	/**
	 * @param Form $form
	 * @throws \PDOException
	 */
	public function formSubmitted(Form $form)
	{
		$values = $form->getValues();

		$companyId = $this->getParameter('company');
		$managerId = $this->getParameter('manager');
		if ($managerId && $companyId) {
			$this->permissionsFacade->update($companyId, $managerId, $this->user->id, $values->roleCompany,
				$values->rolePermissions, $values->roleClients, $values->roleInvoices, $values->roleProducts,
				$values->rolePayments);

			$this->flashMessage('Oprávnění bylo upraveno', 'success');
			$this->redirect('this');
		} else {
			$manager = $this->managersFacade->findOneByUsername($values->manager);
			if (!$manager) {
				$form->addError('Zadaný uživatel neexistuje');
				return;
			}
			$permission = $this->permissionsFacade->findOneById($values->companyId, $manager->id, $this->user->id);
			if ($permission) {
				$form->addError('Uživatel ' . $permission->manager_name . ' již má přiřazená oprávnění ke společnosti
				' . $permission->company_name);
				return;
			}

			$this->permissionsFacade->create($values->companyId, $manager->id, $values->roleCompany,
				$values->rolePermissions, $values->roleClients, $values->roleInvoices, $values->roleProducts,
				$values->rolePayments);

			$this->flashMessage('Oprávnění bylo vytvořeno', 'success');
			$this->redirect('default');
		}
	}


	/************************ edit ************************/


	/**
	 * @param int $company
	 * @param int $manager
	 * @throws \Nette\Application\BadRequestException
	 */
	public function renderEdit($company, $manager)
	{
		$permission = $this->permissionsFacade->findOneById($company, $manager, $this->user->id);
		if (!$permission) {
			throw new BadRequestException();
		}

		$this->template->permission = $permission;

		$this['form']->setDefaults(array(
			'roleCompany' => $permission->role_company,
			'rolePermissions' => $permission->role_permissions,
			'roleClients' => $permission->role_clients,
			'roleInvoices' => $permission->role_invoices,
			'roleProducts' => $permission->role_products,
			'rolePayments' => $permission->role_payments,
		));
	}


	/************************ delete ************************/


	/**
	 * @param int $company
	 * @param int $manager
	 */
	public function actionDelete($company, $manager)
	{
		$this->permissionsFacade->delete($company, $manager, $this->user->id);
		$this->flashMessage('Oprávnění byla úspěšně odebráno', 'success');
		$this->redirect('default');
	}
}
