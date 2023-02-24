<?php

namespace PrestaShop\Module\Psgdpr\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;

class PsgdprController extends FrameworkBundleAdminController
{
    public function renderApp()
    {
        return $this->render(
            '@Modules/psgdpr/views/templates/admin/menu.html.twig',
            [
                // 'showContentHeader' => false,
                // 'psxlegalassistantApiUrl' => $this->linkHelper->getLinkWithoutToken($apiBaseRoute),
                // 'adminToken' => $this->linkHelper->getTokenFromAdminLink($apiBaseRoute),
                // 'legalModule' => $this->moduleHelper->buildModuleInformations(
                //     'psxlegalassistant'
                // ),
                // 'eventBusModule' => $this->moduleHelper->buildModuleInformations(
                //     'ps_eventbus'
                // ),
                // 'accountsModule' => $this->moduleHelper->buildModuleInformations(
                //     'ps_accounts'
                // ),
                // 'contextPsAccounts' => $this->loadPsAccountsAssets(),
                // 'useLocalVueApp' => $this->configHelper->getUseLocalVueApp(),
                // 'useBuildModeOnly' => $this->configHelper->getUseBuildModeOnly(),
                // 'pathAppBuilded' => $pathAppBuilded,
                // 'pathAppCdn' => $pathAppCdn,
                // 'pathAssetsCdn' => $pathAssetsCdn,
                // 'pathAssetsBuilded' => $pathAssetsBuilded,
            ]
        );

    }
}
