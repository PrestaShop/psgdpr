<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Psgdpr\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query\Expr;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;

class CustomerRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * CustomerRepository constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Find customer name by customer id
     *
     * @param CustomerId $customerId
     *
     * @return string
     */
    public function findCustomerNameByCustomerId(CustomerId $customerId): string
    {
        $qb = $this->connection->createQueryBuilder();
        $expression = new Expr();
        $concat = $expression->concat('firstname', '" "', 'lastname');

        $query = $qb->select($concat . ' as name')
            ->from(_DB_PREFIX_ . 'customer', 'customer')
            ->where('customer.id_customer = :id_customer')
            ->setParameter('id_customer', $customerId->getValue())
        ;

        $result = $query->execute();

        return $result->fetchOne();
    }

    /**
     * Find customer id by email
     *
     * @param string $email
     *
     * @return int|bool
     */
    public function findCustomerIdByEmail(string $email)
    {
        $qb = $this->connection->createQueryBuilder();

        $query = $qb->addSelect('c.id_customer')
            ->from(_DB_PREFIX_ . 'customer', 'c')
            ->where('c.email = :email')
            ->setParameter('email', $email)
        ;

        $result = $query->execute();
        $data = $result->fetchOne();

        if ($data) {
            return (int) $data;
        }

        return false;
    }
}
