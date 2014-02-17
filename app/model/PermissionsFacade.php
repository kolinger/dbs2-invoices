<?php

namespace App\Model;

use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;

/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class PermissionsFacade extends Facade
{

	/**
	 * @param int $companyId
	 * @param int $managerId
	 * @param int $ownerId
	 * @return bool|IRow
	 */
	public function findOneById($companyId, $managerId, $ownerId)
	{
		return $this->createSelection($ownerId)
			->where('manager_id', $managerId)
			->where('company_id', $companyId)
			->fetch();
	}


	/**
	 * @param int $ownerId
	 * @return array
	 */
	public function findAvailableCompaniesInPairs($ownerId)
	{
		$companies = $this->createSelection($ownerId)
			->select('company_id AS id, company_name AS name')
			->group('company_id, company_name')
			->order('company_name ASC');

		$pairs = array();
		foreach ($companies as $company) {
			$pairs[$company->id] = $company->name;
		}
		return $pairs;
	}


	/**
	 * @param int $offset
	 * @param int $limit
	 * @param int $ownerId
	 * @return array
	 */
	public function findAll($offset = 0, $limit = 20, $ownerId)
	{
		return $this->createSelection($ownerId)->limit($limit, $offset)->fetchAll();
	}


	/**
	 * @param int $ownerId
	 * @return int
	 */
	public function count($ownerId)
	{
		return $this->createSelection($ownerId)->count();
	}


	/**
	 * @param int $companyId
	 * @param int $managerId
	 * @param bool $roleCompany
	 * @param bool $rolePermissions
	 * @param bool $roleClients
	 * @param bool $roleInvoices
	 * @param bool $roleProducts
	 * @param bool $rolePayments
	 */
	public function create($companyId, $managerId, $roleCompany, $rolePermissions, $roleClients, $roleInvoices,
						   $roleProducts, $rolePayments)
	{
		$values = array(
			'company_id' => $companyId,
			'manager_id' => $managerId,
			'role_company' => $roleCompany,
			'role_permissions' => $rolePermissions,
			'role_clients' => $roleClients,
			'role_invoices' => $roleInvoices,
			'role_products' => $roleProducts,
			'role_payments' => $rolePayments,
		);
		$this->context->query('INSERT INTO permissions', $values);
	}


	/**
	 * @param int $companyId
	 * @param int $managerId
	 * @param int $ownerId
	 * @param bool $roleCompany
	 * @param bool $rolePermissions
	 * @param bool $roleClients
	 * @param bool $roleInvoices
	 * @param bool $roleProducts
	 * @param bool $rolePayments
	 */
	public function update($companyId, $managerId, $ownerId, $roleCompany, $rolePermissions, $roleClients, $roleInvoices,
						   $roleProducts, $rolePayments)
	{
		$values = array(
			'role_company' => $roleCompany,
			'role_permissions' => $rolePermissions,
			'role_clients' => $roleClients,
			'role_invoices' => $roleInvoices,
			'role_products' => $roleProducts,
			'role_payments' => $rolePayments,
		);
		$this->context->query('UPDATE v_permissions SET ? WHERE company_id = ? AND manager_id = ? AND owner_id = ?',
			$values, $companyId, $managerId, $ownerId);
	}


	/**
	 * @param int $companyId
	 * @param int $managerId
	 * @param int $ownerId
	 */
	public function delete($companyId, $managerId, $ownerId)
	{
		$this->context->query('DELETE FROM v_permissions WHERE company_id = ? AND manager_id = ? AND owner_id = ?',
			$companyId, $managerId, $ownerId);
	}


	/**
	 * @param int $ownerId
	 * @return Selection
	 */
	private function createSelection($ownerId)
	{
		$query = $this->context->table('v_permissions');
		$query->where('owner_id', $ownerId);

		$company = $this->getSelectedCompany();
		if ($company) {
			$query->where('company_id', $company);
		}

		return $query;
	}
}