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
use Doctrine\ORM\EntityManager;

class CartRepository
{

    /**
     * @var EntityManager
     */
    private $entitymanager;

    public function __construct(EntityManager $entitymanager)
    {
        $this->entitymanager = $entitymanager;
    }

    /**
     * Find customer carts by customer id
     *
     * @param CustomerId $customerId
     */
    public function findCartsByCustomerId(CustomerId $customerId)
    {
      $qb = $this->entitymanager->createQueryBuilder();

      $result = $qb->select('c.id_cart', 'c.date_add', 'ca.name as carrier_name', 'c.id_currency', 'cu.iso_code as currency_iso_code')
        ->from('cart', 'c')
        ->leftJoin('c', 'carrier', 'ca', 'ca.id_carrier = c.id_carrier')
        ->leftJoin('c', 'currency', 'cu', 'cu.id_currency = c.id_currency')
        ->where('c.id_customer = :id_customer')
        ->orderBy('c.date_add', 'DESC')
        ->setParameter('id_customer', $customerId->getValue());

      return $result->getQuery()->getResult();
    }

    /**
     * Find customer cart products by customer id
     *
     * @param CustomerId $customerId
     */
    public function findProductsCartsNotOrderedByCustomerId(CustomerId $customerId)
    {
        $qb = $this->entitymanager->createQueryBuilder();

        $orderedProduct = $qb->select('1')
            ->from('orders', 'o')
            ->leftJoin('o', 'order_detail', 'od', 'o.id_order = od.id_order')
            ->where('product_id = cp.id_product')
            ->andWhere('o.valid = 1')
            ->andWhere('o.id_customer = :id_customer')
            ->getDQL();

        $result = $qb->select('cp.id_product', 'c.id_cart', 'c.id_shop', 'cp.id_shop AS cp_id_shop')
            ->from('cart_product', 'cp')
            ->leftJoin('cp', 'cart', 'c', 'c.id_cart = cp.id_cart')
            ->leftJoin('cp', 'product', 'p', 'cp.id_product = p.id_product')
            ->where('c.id_customer = :id_customer')
            ->andWhere('NOT EXISTS (' . $orderedProduct . ')')
            ->setParameter('id_customer', $customerId->getValue());

        return $result->getQuery()->getResult();
    }
}
