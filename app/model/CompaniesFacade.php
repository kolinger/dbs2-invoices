<?php

namespace App\Model;

use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;

/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class CompaniesFacade extends Facade
{

	/**
	 * @param int $id
	 * @param int $userId
	 * @return bool|IRow
	 */
	public function findOneById($id, $userId)
	{
		return $this->createSelection($userId)->where('id', $id)->fetch();
	}


	/**
	 * @param int $userId
	 * @return array
	 */
	public function findMine($userId)
	{
		return $this->createSelection($userId)->fetchAll();
	}


	/**
	 * @param int $userId
	 * @return array
	 */
	public function findMineInPairs($userId)
	{
		return $this->createSelection($userId)->fetchPairs('id', 'name');
	}


	/**
	 * @param string $query
	 * @param int $offset
	 * @param int $limit
	 * @param int $userId
	 * @return array
	 */
	public function findAll($query, $offset = 0, $limit = 20, $userId)
	{
		$selection = $this->createSelection($userId)->limit($limit, $offset);

		if ($query) {
			$query = '%' . $query . '%';
			$selection->where('(
				name LIKE ? OR
				name LIKE ? OR
				street LIKE ? OR
				city LIKE ? OR
				zip LIKE ? OR
				company_in LIKE ? OR
				vat_id LIKE ?
				)', $query, $query, $query, $query, $query, $query, $query
			);
		}

		return $selection->fetchAll();
	}


	/**
	 * @param int $userId
	 * @return int
	 */
	public function count($userId)
	{
		return $this->createSelection($userId)->count();
	}


	/**
	 * @param int $userId
	 * @param string $name
	 * @param string $street
	 * @param string $city
	 * @param string $zip
	 * @param string $tradeRegister
	 * @param string $companyIn
	 * @param string $vatId
	 * @param string $email
	 * @param string $phone
	 * @param string $website
	 * @param string $bankAccount
	 * @param string $comment
	 */
	public function create($userId, $name, $street, $city, $zip, $tradeRegister, $companyIn, $vatId, $email, $phone,
						   $website, $bankAccount, $comment)
	{
		$values = array(
			'name' => $name,
			'street' => $street,
			'city' => $city,
			'zip' => $zip,
			'trade_register' => $tradeRegister,
			'company_in' => $companyIn,
			'vat_id' => $vatId,
			'email' => $email,
			'phone' => $phone,
			'website' => $website,
			'bank_account' => $bankAccount,
			'comment' => $comment,
		);
		$company = $this->context->table('companies')->insert($values);

		$values = array(
			'company_id' => $company->id,
			'manager_id' => $userId,
			'role_company' => TRUE,
			'role_permissions' => TRUE,
			'role_clients' => TRUE,
			'role_invoices' => TRUE,
			'role_products' => TRUE,
			'role_payments' => TRUE,
		);
		$this->context->query('INSERT INTO permissions', $values);
	}


	/**
	 * @param int $id
	 * @param int $userId
	 * @param string $name
	 * @param string $street
	 * @param string $city
	 * @param string $zip
	 * @param string $tradeRegister
	 * @param string $companyIn
	 * @param string $vatId
	 * @param string $email
	 * @param string $phone
	 * @param string $website
	 * @param string $bankAccount
	 * @param string $comment
	 */
	public function update($id, $userId, $name, $street, $city, $zip, $tradeRegister, $companyIn, $vatId, $email,
						   $phone, $website, $bankAccount, $comment)
	{
		$values = array(
			'name' => $name,
			'street' => $street,
			'city' => $city,
			'zip' => $zip,
			'trade_register' => $tradeRegister,
			'company_in' => $companyIn,
			'vat_id' => $vatId,
			'email' => $email,
			'phone' => $phone,
			'website' => $website,
			'bank_account' => $bankAccount,
			'comment' => $comment,
		);
		$this->context->query('UPDATE v_companies SET ? WHERE id = ? AND manager_id = ?', $values, $id, $userId);
	}


	/**
	 * @param int $id
	 * @param int $userId
	 */
	public function delete($id, $userId)
	{
		$this->context->query('DELETE FROM v_companies WHERE id = ? AND manager_id = ?', $id, $userId);
	}


	/**
	 * @param int $userId
	 * @return Selection
	 */
	private function createSelection($userId)
	{
		$query = $this->context->table('v_companies');
		$query->where('manager_id', $userId);
		return $query;
	}
}