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
use PrestaShop\Module\Psgdpr\Service\LoggerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BackResponderByEmail extends BackResponderContext implements BackResponderInterface
{
    const TYPE = 'email';

    /**
     * export customer data
     *
     * @param string $data
     *
     * @return Response
     */
    public function export(string $data): Response
    {
        $result = $this->exportService->getThirdPartyModulesInformations(['email' => $data]);

        return new JsonResponse($result);
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
        $this->customerService->deleteCustomerDataFromModules(['email' => $data]);

        $this->loggerService->createLog(0, LoggerService::REQUEST_TYPE_DELETE, 0, 0, $data);

        return new JsonResponse(['message' => 'delete completed']);
    }

    public function supports(string $type): bool
    {
        return $type === self::TYPE;
    }
}
