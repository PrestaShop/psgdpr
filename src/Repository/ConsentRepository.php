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

class ConsentRepository
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
     * Find all registered modules for GDPR
     *
     * @return array
     */
    public function getAllRegisteredModules(): array
    {
        $qb = $this->connection->createQueryBuilder();

        $query = $qb->select('consent.id_gdpr_consent', 'consent.id_module')
            ->from(_DB_PREFIX_ . 'psgdpr_consent', 'consent')
            ->innerJoin(_DB_PREFIX_ . 'module', 'module', 'module.id_module = consent.id_module')
            ->orderBy('consent.id_gdpr_consent', 'DESC');

        $result = $query->execute();

        return $result->fetchAllAssociative();
    }

    /**
     * Get consent message for module
     *
     * @param int $moduleId
     * @param int $langId
     *
     * @return string
     */
    public function getModuleConsentMessage(int $moduleId, int $langId): string
    {
        $qb = $this->connection->createQueryBuilder();

        $query = $qb->select('consent_lang.message')
            ->from(_DB_PREFIX_ . 'psgdpr_consent', 'consent')
            ->leftJoin('consent', _DB_PREFIX_ . 'psgdpr_consent_lang', 'consent_lang', 'consent.id_gdpr_consent = consent_lang.id_gdpr_consent')
            ->where('consent.id_module = :id_module')
            ->andWhere('consent_lang.id_lang = :id_lang')
            ->setParameter('id_module', $moduleId)
            ->setParameter('id_lang', $langId);

        $result = $query->execute();

        return $result->fetchAssociative();
    }

    public function getModuleConsentIsActive(int $moduleId): bool
    {
        $qb = $this->connection->createQueryBuilder();

        $query = $qb->select('consent.active')
            ->from(_DB_PREFIX_ . 'psgdpr_consent', 'consent')
            ->where('consent.id_module = :id_module')
            ->setParameter('id_module', $moduleId);

        $result = $query->execute();

        return $result->fetchAssociative();
    }

    /**
     * Get consent exist for module
     *
     * @param int $moduleId
     *
     * @return bool
     */
    public function getModuleConsentIsExist(int $moduleId): bool
    {
        $qb = $this->connection->createQueryBuilder();

        $query = $qb->select('id_module')
            ->from(_DB_PREFIX_ . 'psgdpr_consent', 'consent')
            ->leftJoin('consent', _DB_PREFIX_ . 'psgdpr_consent_lang', 'consent_lang', 'consent.id_gdpr_consent = consent_lang.id_gdpr_consent')
            ->where('consent.id_module = :id_module')
            ->setParameter('id_module', $moduleId);

        $result = $query->execute();

        return $result->fetchAssociative();
    }
}
