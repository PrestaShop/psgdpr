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

namespace PrestaShop\Module\Psgdpr\Service\BackResponder;

use PrestaShop\Module\Psgdpr\Repository\CustomerRepository;
use PrestaShop\Module\Psgdpr\Service\CustomerService;
use PrestaShop\Module\Psgdpr\Service\Export\ExportFactory;
use PrestaShop\Module\Psgdpr\Service\ExportService;
use PrestaShop\Module\Psgdpr\Service\LoggerService;

abstract class BackResponderContext
{
    /**
     * @var ExportFactory
     */
    protected $exportFactory;

    /**
     * @var CustomerRepository
     */
    protected $customerRepository;

    /**
     * @var CustomerService
     */
    protected $customerService;

    /**
     * @var LoggerService
     */
    protected $loggerService;

    /**
     * @var ExportService
     */
    protected $exportService;

    public function __construct(
        ExportFactory $exportFactory,
        CustomerRepository $customerRepository,
        CustomerService $customerService,
        LoggerService $loggerService,
        ExportService $exportService
    ) {
        $this->exportFactory = $exportFactory;
        $this->customerRepository = $customerRepository;
        $this->customerService = $customerService;
        $this->loggerService = $loggerService;
        $this->exportService = $exportService;
    }
}
