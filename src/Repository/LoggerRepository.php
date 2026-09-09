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

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PrestaShop\Module\Psgdpr\Entity\PsgdprLog;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;

class LoggerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PsgdprLog::class);
    }

    /**
     * Add log to database
     *
     * @param PsgdprLog $log
     *
     * @return void
     */
    public function add(PsgdprLog $log)
    {
        $this->getEntityManager()->persist($log);
        $this->getEntityManager()->flush();
    }

    /**
     * Get all logs
     *
     * @return array
     */
    public function findAll(): array
    {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $query = $queryBuilder->select('*')->from(_DB_PREFIX_ . 'psgdpr_log', 'l');

        $result = $this->getEntityManager()->getConnection()->executeQuery($query);

        return $result->fetchAllAssociative();
    }

    /**
     * Anonymize customer activity logs by customer ID.
     *
     * @param CustomerId $customerIdToAnonymize
     * @param CustomerId $anonymousCustomerId
     * @param string $anonymousCustomerName
     *
     * @return bool
     */
    public function anonymizeLogsByCustomerId(
        CustomerId $customerIdToAnonymize
    ): bool
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();
        $queryBuilder
            ->update(_DB_PREFIX_ . 'psgdpr_log', 'l')
            ->set('l.id_guest', '0')
            ->set('l.client_name', $queryBuilder->expr()->literal('Anonymous'))
            ->where('l.id_customer = :customerId')
            ->setParameter('customerId', $customerIdToAnonymize->getValue())
        ;

        $queryBuilder->execute();

        return true;
    }
}
