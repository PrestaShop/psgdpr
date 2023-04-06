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

namespace PrestaShop\Module\Psgdpr\Service\FrontResponder\Strategy;

use Exception;
use PrestaShop\Module\Psgdpr\Exception\Customer\ExportException;
use PrestaShop\Module\Psgdpr\Service\Export\Strategy\ExportToPdf;
use PrestaShop\Module\Psgdpr\Service\FrontResponder\FrontResponderContext;
use PrestaShop\Module\Psgdpr\Service\FrontResponder\FrontResponderInterface;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;

class FrontResponderForPdf extends FrontResponderContext implements FrontResponderInterface
{
    const TYPE = 'pdf';

    /**
     * export customer data to csv
     *
     * @param CustomerId $customerid
     *
     * @return void
     */
    public function export(CustomerId $customerid): void
    {
        try {
            $exportStrategy = $this->exportFactory->getStrategyByType(ExportToPdf::TYPE);
            $this->exportService->exportCustomerData($customerid, $exportStrategy);

            exit();
        } catch (Exception $e) {
            throw new ExportException('A problem occurred while exporting customer to pdf. please try again');
        }
    }

    public function supports(string $type): bool
    {
        return $type === self::TYPE;
    }
}
