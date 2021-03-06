<?php

namespace App\Model;

use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;

/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class ClientsFacade extends Facade
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
				company_name LIKE ? OR
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
	 * @param int $companyId
	 * @param int $userId
	 * @return array
	 */
	public function findInPairs($companyId, $userId)
	{
		$clients = $this->createSelection($userId)->where('company_id', $companyId)->fetchAll();
		$pairs = array();
		foreach ($clients as $client) {
			$pairs[$client->id] = $client->name . ' (' . $client->street . ', ' . $client->zip . ' ' . $client->city . ')';
		}
		return $pairs;
	}


	/**
	 * @param int $userId
	 * @return int
	 */
	public function count($userId)
	{
		return $this->createSelection($userId)->count('id');
	}


	/**
	 * @param int $companyId
	 * @param string $name
	 * @param string $street
	 * @param string $city
	 * @param string $zip
	 * @param string $companyIn
	 * @param string $vatId
	 * @param string $email
	 * @param string $phone
	 * @param string $comment
	 */
	public function create($companyId, $name, $street, $city, $zip, $companyIn, $vatId, $email, $phone, $comment)
	{
		$values = array(
			'company_id' => $companyId,
			'name' => $name,
			'street' => $street,
			'city' => $city,
			'zip' => $zip,
			'company_in' => $companyIn,
			'vat_id' => $vatId,
			'email' => $email,
			'phone' => $phone,
			'comment' => $comment,
		);
		$this->context->table('clients')->insert($values);
	}


	/**
	 * @param int $id
	 * @param int $userId
	 * @param string $name
	 * @param string $street
	 * @param string $city
	 * @param string $zip
	 * @param string $companyIn
	 * @param string $vatId
	 * @param string $email
	 * @param string $phone
	 * @param string $comment
	 */
	public function update($id, $userId, $name, $street, $city, $zip, $companyIn, $vatId, $email,
						   $phone, $comment)
	{
		$values = array(
			'name' => $name,
			'street' => $street,
			'city' => $city,
			'zip' => $zip,
			'company_in' => $companyIn,
			'vat_id' => $vatId,
			'email' => $email,
			'phone' => $phone,
			'comment' => $comment,
		);
		$this->context->query('UPDATE v_clients SET ? WHERE id = ? AND manager_id = ?', $values, $id, $userId);
	}


	/**
	 * @param int $id
	 * @param int $userId
	 */
	public function delete($id, $userId)
	{
		$this->context->query('DELETE FROM v_clients WHERE id = ? AND manager_id = ?', $id, $userId);
	}


	/**
	 * @param int $userId
	 * @return Selection
	 */
	private function createSelection($userId)
	{
		$query = $this->context->table('v_clients');
		$query->where('manager_id', $userId);

		$company = $this->getSelectedCompany();
		if ($company) {
			$query->where('company_id', $company);
		}

		return $query;
	}
}