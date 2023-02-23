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

namespace PrestaShop\Module\Psgdpr\Domain\Logger\Installer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * We cannot use Doctrine entities on install because the mapping is not available yet
 * but we can still use Doctrine connection to perform DQL or SQL queries.
 */
class LoggerInstaller
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var string
     */
    private $dbPrefix;

    /**
     * LoggerInstaller constructor.
     *
     * @param Connection $connection
     * @param string $dbPrefix
     */
    public function __construct(
        Connection $connection,
        $dbPrefix
    ) {
        $this->connection = $connection;
        $this->dbPrefix = $dbPrefix;
    }

    /**
     * Create tables
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function createTables(): array
    {
        $errors = [];
        $sqlInstallFile = dirname(__DIR__, 3) . '/Migration/migration_01.sql';

        if (!file_exists($sqlInstallFile)) {
            $errors[] = [
                'key' => json_encode('SQL file not found'),
                'parameters' => [],
                'domain' => 'Admin.Modules.Notification',
            ];

            return $errors;
        }

        $sqlQueries = explode(PHP_EOL, (string) file_get_contents($sqlInstallFile));
        $sqlQueries = str_replace('PREFIX_', $this->dbPrefix, $sqlQueries);

        foreach ($sqlQueries as $query) {
            if (empty($query)) {
                continue;
            }

            try {
                $this->connection->executeQuery($query);
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
}
