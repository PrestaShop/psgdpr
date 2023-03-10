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
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use PrestaShop\Module\Psgdpr\Entity\PsgdprConsent;


class ConsentRepository extends EntityRepository
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
     * Add consent to database
     *
     * @param PsgdprConsent $psgdprConsent
     *
     * @return bool
     */
    public function createOrUpdateConsent(PsgdprConsent $psgdprConsent): bool
    {
        $consent = $this->findConsentByModuleId($psgdprConsent->getModuleId());

        dump($consent, $psgdprConsent);

        $consent = $consent ?: $psgdprConsent;

        $this->getEntityManager()->persist($consent);
        $this->getEntityManager()->flush();

        return true;
    }

    public function findConsentByModuleId(int $idModule): ?PsgdprConsent
    {
        return $this->findOneBy([
            'id_module' => $idModule,
        ]);
    }

    /**
     * Find all registered modules for GDPR
     *
     * @return array
     */
    public function findAllRegisteredModules(): array
    {
        $qb = $this->connection->createQueryBuilder();

        $query = $qb->select('consent.id_gdpr_consent', 'consent.id_module')
            ->from(_DB_PREFIX_ . 'psgdpr_consent', 'consent')
            ->innerJoin('consent', _DB_PREFIX_ . 'module', 'module', 'module.id_module = consent.id_module')
            ->orderBy('consent.id_gdpr_consent', 'DESC');

        $data = $query->execute();

        return $data->fetchAllAssociative();
    }

    /**
     * Find consent message for module
     *
     * @param int $moduleId
     * @param int $langId
     *
     * @return string
     */
    public function findModuleConsentMessage(int $moduleId, int $langId): string
    {
        $qb = $this->connection->createQueryBuilder();

        $query = $qb->select('consent_lang.message')
            ->from(_DB_PREFIX_ . 'psgdpr_consent', 'consent')
            ->leftJoin('consent', _DB_PREFIX_ . 'psgdpr_consent_lang', 'consent_lang', 'consent.id_gdpr_consent = consent_lang.id_gdpr_consent')
            ->where('consent.id_module = :id_module')
            ->andWhere('consent_lang.id_lang = :id_lang')
            ->setParameter('id_module', $moduleId)
            ->setParameter('id_lang', $langId);

        $queryResult = $query->execute();
        $data = $queryResult->fetchOne();

        return $data ? $data : '';
    }

    /**
     * Find consent active for module
     *
     * @param int $moduleId
     *
     * @return bool
     */
    public function findModuleConsentIsActive(int $moduleId): bool
    {
        $qb = $this->connection->createQueryBuilder();

        $query = $qb->select('consent.active')
            ->from(_DB_PREFIX_ . 'psgdpr_consent', 'consent')
            ->where('consent.id_module = :id_module')
            ->setParameter('id_module', $moduleId);

        $queryResult = $query->execute();
        $data = $queryResult->fetchAssociative();

        return $data['active'] == 1 ? true : false;
    }

    /**
     * Find consent exist for module
     *
     * @param int $moduleId
     *
     * @return bool
     */
    public function findModuleConsentExist(int $moduleId): bool
    {
        $qb = $this->connection->createQueryBuilder();

        $query = $qb->select('id_module')
            ->from(_DB_PREFIX_ . 'psgdpr_consent', 'consent')
            ->leftJoin('consent', _DB_PREFIX_ . 'psgdpr_consent_lang', 'consent_lang', 'consent.id_gdpr_consent = consent_lang.id_gdpr_consent')
            ->where('consent.id_module = :id_module')
            ->setParameter('id_module', $moduleId);

        $queryResult = $query->execute();
        $data = $queryResult->fetchOne();

        return $data ? true : false;
    }
}
