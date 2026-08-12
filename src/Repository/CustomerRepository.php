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
use PrestaShop\Module\Psgdpr\Service\LoggerService;
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

    /**
     * Find customers inactive beyond the retention period.
     *
     * "Inactive" = the most recent of (last connection / profile update) and
     * (last VALID order) is older than $inactivityDays. The anonymous customer
     * and soft-deleted customers are excluded.
     *
     * @param int $inactivityDays retention period, in days
     * @param int $limit safety cap on rows returned per run
     *
     * @return array<int, array{id_customer: int, id_lang: int, email: string, firstname: string, lastname: string}>
     *
     * @todo Multistore: this scans all shops. Scope by id_shop if the retention
     *       policy must differ per shop.
     */
    public function findInactiveCustomers(int $inactivityDays, int $limit): array
    {
        $cutoff = date('Y-m-d H:i:s', strtotime('-' . $inactivityDays . ' days'));

        $qb = $this->connection->createQueryBuilder();

        $query = $qb->select('c.id_customer', 'c.id_lang', 'c.email', 'c.firstname', 'c.lastname')
            ->from(_DB_PREFIX_ . 'customer', 'c')
            ->where('c.deleted = 0')
            ->andWhere('c.email != :anonymous_email')
            ->andWhere(
                'GREATEST('
                . 'c.date_upd, '
                . 'COALESCE((SELECT MAX(o.date_add) FROM ' . _DB_PREFIX_ . 'orders o '
                . 'WHERE o.id_customer = c.id_customer AND o.valid = 1), c.date_add)'
                . ') < :cutoff'
            )
            ->setParameter('anonymous_email', 'anonymous@psgdpr.com')
            ->setParameter('cutoff', $cutoff)
            ->setMaxResults($limit)
        ;

        $result = $query->execute();

        return $result->fetchAllAssociative();
    }

    /**
     * Return the date of the last retention warning logged for a customer, or null.
     * Used to enforce the grace window between warning and anonymization.
     *
     * @param int $customerId
     * @param int $moduleId
     *
     * @return string|null datetime string, or null if never warned
     */
    public function findLastRetentionWarningDate(int $customerId, int $moduleId)
    {
        $qb = $this->connection->createQueryBuilder();

        $query = $qb->select('MAX(l.date_add)')
            ->from(_DB_PREFIX_ . 'psgdpr_log', 'l')
            ->where('l.id_customer = :id_customer')
            ->andWhere('l.id_module = :id_module')
            ->andWhere('l.request_type = :request_type')
            ->setParameter('id_customer', $customerId)
            ->setParameter('id_module', $moduleId)
            ->setParameter('request_type', LoggerService::REQUEST_TYPE_RETENTION_WARNING)
        ;

        $result = $query->execute();
        $data = $result->fetchOne();

        return $data !== false && $data !== null ? (string) $data : null;
    }
}
