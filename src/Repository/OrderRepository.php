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
use Exception;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;

class OrderRepository
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
     * @return array
     */
    public function findProductsCartsNotOrderedByCustomerId(CustomerId $customerId): array
    {
        try {
            $qb = $this->connection->createQueryBuilder();

            $orderedProductQuery = $qb->select('1')
                ->from(_DB_PREFIX_ . 'orders', 'order')
                ->leftJoin('order', _DB_PREFIX_ . 'order_detail', 'detail', 'order.id_order = detail.id_order')
                ->where('product_id = cart_product.id_product')
                ->andWhere('order.valid = 1')
                ->andWhere('order.id_customer = :id_customer')
                ->getSQL();

            $query = $qb->select('cart_product.id_product', 'cart.id_cart', 'cart.id_shop', 'cart_product.id_shop AS cart_product_id_shop')
                ->from(_DB_PREFIX_ . 'cart_product', 'cart_product')
                ->leftJoin('cart_product', _DB_PREFIX_ . 'cart', 'cart', 'cart.id_cart = cart_product.id_cart')
                ->leftJoin('cart_product', _DB_PREFIX_ . 'product', 'product', 'cart_product.id_product = product.id_product')
                ->where('cart.id_customer = :id_customer')
                ->andWhere('NOT EXISTS (' . $orderedProductQuery . ')')
                ->setParameter('id_customer', $customerId->getValue());

            $result = $query->execute();

            return $result->fetchAllAssociative();
        } catch (Exception $e) {
            return [];
        }
    }
}
