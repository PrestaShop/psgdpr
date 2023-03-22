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
use PrestaShop\Module\Psgdpr\Entity\PsgdprConsent;
use PrestaShop\Module\Psgdpr\Entity\PsgdprConsentLang;

class ConsentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PsgdprConsent::class);
    }

    /**
     * Add consent to database
     *
     * @param PsgdprConsent $psgdprConsent
     *
     * @return void
     */
    public function createOrUpdateConsent(PsgdprConsent $psgdprConsent): void
    {
        /** @var PsgdprConsent|null $consent */
        $consent = $this->findConsentByModuleId($psgdprConsent->getModuleId());

        if ($consent !== null) {
            $consent->setActive($psgdprConsent->isActive());
            $consent->setError($psgdprConsent->isError());
            $consent->setErrorMessage($psgdprConsent->getErrorMessage());

            /** @var PsgdprConsentLang $consentLang */
            foreach ($consent->getConsentLangs() as $consentLang) {
                /** @var PsgdprConsentLang $psgdprConsentLang */
                foreach ($psgdprConsent->getConsentLangs() as $psgdprConsentLang) {
                    if ($consentLang->getLang() === $psgdprConsentLang->getLang()) {
                        $consentLang->setMessage($psgdprConsentLang->getMessage());
                    }
                }
            }
        } else {
            $consent = $psgdprConsent;
        }

        $this->getEntityManager()->persist($consent);
        $this->getEntityManager()->flush();
    }

    /**
     * Find consent by module id
     *
     * @param int $moduleId
     *
     * @return object|null
     */
    public function findConsentByModuleId(int $moduleId)
    {
        return $this->findOneBy([
            'moduleId' => $moduleId,
        ]);
    }

    /**
     * Find all registered modules for GDPR
     *
     * @return array
     */
    public function findAllRegisteredModules(): array
    {
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $query = $queryBuilder->select('consent.id_gdpr_consent', 'consent.id_module')
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
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $query = $queryBuilder->select('consent_lang.message')
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
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $query = $queryBuilder->select('consent.active')
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
        $queryBuilder = $this->getEntityManager()->getConnection()->createQueryBuilder();

        $query = $queryBuilder->select('id_module')
            ->from(_DB_PREFIX_ . 'psgdpr_consent', 'consent')
            ->leftJoin('consent', _DB_PREFIX_ . 'psgdpr_consent_lang', 'consent_lang', 'consent.id_gdpr_consent = consent_lang.id_gdpr_consent')
            ->where('consent.id_module = :id_module')
            ->setParameter('id_module', $moduleId);

        $queryResult = $query->execute();
        $data = $queryResult->fetchOne();

        return $data ? true : false;
    }
}
