<?php

namespace App\Model;

use Nette\Database\Table\IRow;
use Nette\Database\Table\Selection;

/**
 * @author Tomáš Kolinger <tomas@kolinger.me>
 */
class PaymentsFacade extends Facade
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
		$selection = $this->createSelection($userId)->limit($limit, $offset)->order('id DESC');

		if ($query) {
			$query = '%' . $query . '%';
			$selection->where('(
				company_name LIKE ? OR
				CAST(invoice_id AS text) LIKE ? OR
				client_name LIKE ? OR
				CAST(amount AS text) LIKE ? OR
				CAST(date AS text) LIKE ?
				)', $query, $query, $query, $query, $query
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
		return $this->createSelection($userId)->count('id');
	}


	/**
	 * @param int $invoiceId
	 * @param float $amount
	 * @param \DateTime $date
	 * @param string $comment
	 */
	public function create($invoiceId, $amount, $date, $comment)
	{
		$values = array(
			'invoice_id' => $invoiceId,
			'amount' => $amount,
			'date' => $date,
			'comment' => $comment,
		);
		$this->context->table('payments')->insert($values);
	}


	/**
	 * @param int $id
	 * @param int $userId
	 * @param float $amount
	 * @param \DateTime $date
	 * @param string $comment
	 */
	public function update($id, $userId, $amount, $date, $comment)
	{
		$values = array(
			'amount' => $amount,
			'date' => $date,
			'comment' => $comment,
		);
		$this->context->query('UPDATE v_payments SET ? WHERE id = ? AND manager_id = ?', $values, $id, $userId);
	}


	/**
	 * @param int $id
	 * @param int $userId
	 */
	public function delete($id, $userId)
	{
		$this->context->query('DELETE FROM v_payments WHERE id = ? AND manager_id = ?', $id, $userId);
	}


	/**
	 * @param int $userId
	 * @return Selection
	 */
	private function createSelection($userId)
	{
		$query = $this->context->table('v_payments');
		$query->where('manager_id', $userId);

		$company = $this->getSelectedCompany();
		if ($company) {
			$query->where('company_id', $company);
		}

		return $query;
	}
}