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

class CartRepository
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * CartRepository constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Find customer carts by customer id
     *
     * @param CustomerId $customerId
     *
     * @return array
     */
    public function findCartsByCustomerId(CustomerId $customerId): array
    {
        $qb = $this->connection->createQueryBuilder();

        $query = $qb->select('c.id_cart', 'c.date_add', 'ca.name as carrier_name', 'c.id_currency', 'cu.iso_code as currency_iso_code')
            ->from(_DB_PREFIX_ . 'cart', 'c')
            ->leftJoin('c', _DB_PREFIX_ . 'carrier', 'ca', 'ca.id_carrier = c.id_carrier')
            ->leftJoin('c', _DB_PREFIX_ . 'currency', 'cu', 'cu.id_currency = c.id_currency')
            ->where('c.id_customer = :id_customer')
            ->orderBy('c.date_add', 'DESC')
            ->setParameter('id_customer', $customerId->getValue());

        $result = $query->execute();

        return $result->fetchAssociative();
    }
}
