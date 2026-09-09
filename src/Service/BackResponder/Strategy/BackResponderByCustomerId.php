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

namespace PrestaShop\Module\Psgdpr\Service\BackResponder\Strategy;

use PrestaShop\Module\Psgdpr\Service\BackResponder\BackResponderContext;
use PrestaShop\Module\Psgdpr\Service\BackResponder\BackResponderInterface;
use PrestaShop\Module\Psgdpr\Service\Export\Strategy\ExportToJson;
use PrestaShop\Module\Psgdpr\Service\LoggerService;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BackResponderByCustomerId extends BackResponderContext implements BackResponderInterface
{
    const TYPE = 'customer';

    /**
     * export customer data
     *
     * @param string $data
     *
     * @return Response
     */
    public function export(string $data): Response
    {
        $customerId = new CustomerId((int) $data);

        $exportStrategy = $this->exportFactory->getStrategyByType(ExportToJson::TYPE);

        $result = $this->exportService->exportCustomerData($customerId, $exportStrategy);

        return new JsonResponse(json_decode($result));
    }

    /**
     * delete customer data
     *
     * @param string $data
     *
     * @return Response
     */
    public function delete(string $data): Response
    {
        $customerId = new CustomerId(intval($data));
        $customerData = $this->customerRepository->findCustomerNameByCustomerId($customerId);

        $this->customerService->deleteCustomerDataFromPrestashop($customerId);
        $this->customerService->deleteCustomerDataFromModules(strval($customerId->getValue()));
        $this->customerService->deleteCustomerDataFromGDPRModule($customerId);

        // Store customer ID as 0 to avoid connecting previously anonymized records with customer's name by ID
        $this->loggerService->createLog(0, LoggerService::REQUEST_TYPE_DELETE, 0, 0, $customerData);

        return new JsonResponse(['message' => 'delete completed']);
    }

    public function supports(string $type): bool
    {
        return $type === self::TYPE;
    }
}
