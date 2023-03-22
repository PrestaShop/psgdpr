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

use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\Module\Psgdpr\Entity\PsgdprLog;

class LoggerRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $entitymanager;

    /**
     * LoggerRepository constructor.
     *
     * @param EntityManagerInterface $entitymanager
     */
    public function __construct(EntityManagerInterface $entitymanager)
    {
        $this->entitymanager = $entitymanager;
    }

    /**
     * Add log to database
     *
     * @param PsgdprLog $log
     *
     * @return bool
     */
    public function add(PsgdprLog $log): bool
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
    public function findAll(): array
    {
        $qb = $this->entitymanager->createQueryBuilder();
        $query = $qb->select('*')->from('ps_psgdpr_log', 'l');

        $result = $this->entitymanager->getConnection()->executeQuery($query);

        return $result->fetchAllAssociative();
    }
}
