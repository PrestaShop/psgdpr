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

use Doctrine\ORM\EntityManager;
use Exception;
use PrestaShop\Module\Psgdpr\Entity\Log;

class LoggerRepository
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
     * Create tables
     *
     * @return array
     *
     * @throws Exception
     */
    public function createTables(): array
    {
        $errors = [];
        $sqlInstallFile = dirname(__DIR__, 1) . '/Migration/migration_01.sql';

        if (!file_exists($sqlInstallFile)) {
            $errors[] = [
                'key' => json_encode('SQL file not found'),
                'parameters' => [],
                'domain' => 'Admin.Modules.Notification',
            ];

            return $errors;
        }

        $sqlQueries = explode(PHP_EOL, (string) file_get_contents($sqlInstallFile));
        $sqlQueries = str_replace('PREFIX_', _DB_PREFIX_ , $sqlQueries);

        foreach ($sqlQueries as $query) {
            if (empty($query)) {
                continue;
            }

            try {
                $this->entitymanager->getConnection()->executeQuery($query);
            } catch (Exception $e) {
                $errors[] = [
                    'key' => json_encode($e->getMessage()),
                    'parameters' => [],
                    'domain' => 'Admin.Modules.Notification',
                ];
            }
        }

        return $errors;
    }

    /**
     * Add log to database
     *
     * @param Log $log
     *
     * @return bool
     */
    public function add(Log $log): bool
    {
        $this->entitymanager->persist($log);
        $this->entitymanager->flush();

        return true;
    }

    /**
     * Get all logs
     *
     * @return array
     */
    public function findAll()
    {
        $qb = $this->entitymanager->createQueryBuilder();
        $qb->select('*')->from('psgdpr_log', 'l');

        return $qb->getQuery()->getResult();
    }
}
