<?php

namespace M2E\Kaufland\Helper;

class View
{
    public const LISTING_CREATION_MODE_FULL = 0;
    public const LISTING_CREATION_MODE_LISTING_ONLY = 1;

    public const MOVING_LISTING_OTHER_SELECTED_SESSION_KEY = 'moving_listing_other_selected';
    public const MOVING_LISTING_PRODUCTS_SELECTED_SESSION_KEY = 'moving_listing_products_selected';

    /** @var \Magento\Backend\Model\UrlInterface */
    private $urlBuilder;
    /** @var \M2E\Kaufland\Helper\View\Kaufland */
    private $viewHelper;
    /** @var \M2E\Kaufland\Helper\View\Kaufland\Controller */
    private $controllerHelper;
    /** @var \Magento\Framework\App\RequestInterface */
    private $request;

    public function __construct(
        \Magento\Backend\Model\UrlInterface $urlBuilder,
        \M2E\Kaufland\Helper\View\Kaufland $viewHelper,
        \M2E\Kaufland\Helper\View\Kaufland\Controller $controllerHelper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->viewHelper = $viewHelper;
        $this->controllerHelper = $controllerHelper;
        $this->request = $request;
    }

    public function getViewHelper(): View\Kaufland
    {
        return $this->viewHelper;
    }

    public function getControllerHelper(): View\Kaufland\Controller
    {
        return $this->controllerHelper;
    }

    public function getCurrentView(): ?string
    {
        $controllerName = $this->request->getControllerName();

        if ($controllerName === null) {
            return null;
        }

        if (stripos($controllerName, \M2E\Kaufland\Helper\View\Kaufland::NICK) !== false) {
            return \M2E\Kaufland\Helper\View\Kaufland::NICK;
        }

        if (stripos($controllerName, \M2E\Kaufland\Helper\View\ControlPanel::NICK) !== false) {
            return \M2E\Kaufland\Helper\View\ControlPanel::NICK;
        }

        if (stripos($controllerName, 'system_config') !== false) {
            return \M2E\Kaufland\Helper\View\Configuration::NICK;
        }

        return null;
    }

    public function isCurrentViewKaufland(): bool
    {
        return $this->getCurrentView() == \M2E\Kaufland\Helper\View\Kaufland::NICK;
    }

    public function isCurrentViewControlPanel(): bool
    {
        return $this->getCurrentView() == \M2E\Kaufland\Helper\View\ControlPanel::NICK;
    }

    public function isCurrentViewConfiguration(): bool
    {
        return $this->getCurrentView() == \M2E\Kaufland\Helper\View\Configuration::NICK;
    }

    public function getUrl($row, $controller, $action, array $params = []): string
    {
        return $this->urlBuilder->getUrl("*/Kaufland_$controller/$action", $params);
    }

    public function getModifiedLogMessage($logMessage)
    {
        return \M2E\Core\Helper\Data::escapeHtml(
            \M2E\Kaufland\Helper\Module\Log::decodeDescription($logMessage),
            ['a'],
            ENT_NOQUOTES
        );
    }
}
