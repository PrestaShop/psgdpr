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

namespace PrestaShop\Module\PSGDPR\Infrastructure\Repository;

use Doctrine\ORM\EntityManager;
use PrestaShop\Module\PSGDPR\Entity\Log;

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
}
