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
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;

class OrderInvoiceRepository
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * OrderRepository constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Find customer cart products by customer id
     *
     * @param CustomerId $customerId
     *
     * @return bool
     */
    public function findIfInvoicesExistByCustomerId(CustomerId $customerId): bool
    {
        $qb = $this->connection->createQueryBuilder();

        $query = $qb->select('count(*)')
            ->from(_DB_PREFIX_ . 'order_invoice', 'oi')
            ->leftJoin('oi', _DB_PREFIX_ . 'orders', 'o', 'oi.id_order = o.id_order')
            ->where('o.id_customer = :customerId')
            ->setParameter('customerId', $customerId->getValue());

        $result = $query->execute();

        if ($result->fetchOne() == 0) {
            return false;
        }

        return true;
    }

    /**
     * Find customer cart products by customer id
     *
     * @param CustomerId $customerId
     *
     * @return array
     */
    public function findAllInvoicesByCustomerId(CustomerId $customerId): array
    {
        $qb = $this->connection->createQueryBuilder();

        $query = $qb->select('oi.*')
            ->from(_DB_PREFIX_ . 'order_invoice', 'oi')
            ->leftJoin('oi', _DB_PREFIX_ . 'orders', 'o', 'oi.id_order = o.id_order')
            ->where('o.id_customer = :customerId')
            ->setParameter('customerId', $customerId->getValue());

        $result = $query->execute();

        return $result->fetchAllAssociative();
    }
}
