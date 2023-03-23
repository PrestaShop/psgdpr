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
use PrestaShop\PrestaShop\Core\Domain\Address\ValueObject\AddressId;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;

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

        $query = $qb->select('cart.id_cart', 'cart.date_add', 'carrier.name as carrier_name', 'cart.id_currency', 'currency.iso_code as currency_iso_code')
            ->from(_DB_PREFIX_ . 'cart', 'cart')
            ->leftJoin('cart', _DB_PREFIX_ . 'carrier', 'carrier', 'carrier.id_carrier = cart.id_carrier')
            ->leftJoin('cart', _DB_PREFIX_ . 'currency', 'currency', 'currency.id_currency = cart.id_currency')
            ->where('cart.id_customer = :id_customer')
            ->orderBy('cart.date_add', 'DESC')
            ->setParameter('id_customer', $customerId->getValue()
        );

        $result = $query->execute();

        return $result->fetchAssociative();
    }

    /**
     * Anonymize customer cart by customer id
     *
     * @param CustomerId $customerIdToAnonymize
     * @param CustomerId $anonymousCustomerId
     * @param AddressId $anonymousAddressId
     *
     * @return bool
     */
    public function anonymizeCustomerCartByCustomerId(
        CustomerId $customerIdToAnonymize,
        CustomerId $anonymousCustomerId,
        AddressId $anonymousAddressId
    ): bool {
        $qb = $this->connection->createQueryBuilder();
        $qb->update(_DB_PREFIX_ . 'cart', 'c')
            ->set('c.id_customer', strval($anonymousCustomerId->getValue()))
            ->set('c.id_address_delivery', strval($anonymousAddressId->getValue()))
            ->set('c.id_address_invoice', strval($anonymousAddressId->getValue()))
            ->where('c.id_customer = :customerId')
            ->setParameter('customerId', $customerIdToAnonymize->getValue())
        ;

        $qb->execute();

        return true;
    }
}
