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

use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query\Expr\Func;

class CustomerRepository
{
    /**
     * @var Connection
     */
    private $connection;

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
    public function findCustomerNameByCustomerId(CustomerId $customerId)
    {
        $qb = $this->connection->createQueryBuilder();
        $concat = new Func('CONCAT', array('firstname', ' ', 'lastname'));

        $query = $qb->addSelect($concat . ' as name')
            ->from(_DB_PREFIX_ . 'customer', 'c')
            ->where('c.id_customer = :id_customer')
            ->setParameter('id_customer', $customerId->getValue());

        $result = $query->execute();

        return $result->fetchOne();
    }
}

