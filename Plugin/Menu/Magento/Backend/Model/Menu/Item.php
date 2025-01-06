<?php

declare(strict_types=1);

namespace M2E\Kaufland\Plugin\Menu\Magento\Backend\Model\Menu;

use M2E\Kaufland\Helper\View;

class Item extends \M2E\Kaufland\Plugin\AbstractPlugin
{
    private array $menuTitlesUsing = [];

    private \M2E\Kaufland\Helper\Module\Wizard $wizardHelper;

    public function __construct(
        \M2E\Kaufland\Helper\Module\Wizard $wizardHelper
    ) {
        $this->wizardHelper = $wizardHelper;
    }

    public function aroundGetClickCallback($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('getClickCallback', $interceptor, $callback, $arguments);
    }

    protected function processGetClickCallback($interceptor, \Closure $callback, array $arguments)
    {
        $id = $interceptor->getId();
        $urls = $this->getUrls();

        if (isset($urls[$id])) {
            return $this->renderOnClickCallback($urls[$id]);
        }

        return $callback(...$arguments);
    }

    /**
     * Gives able to display titles in menu slider which differ from titles in menu panel
     *
     * @param \Magento\Backend\Model\Menu\Item $interceptor
     * @param \Closure $callback
     *
     * @return string
     */
    public function aroundGetTitle($interceptor, \Closure $callback, ...$arguments)
    {
        return $this->execute('getTitle', $interceptor, $callback, $arguments);
    }

    protected function processGetTitle($interceptor, \Closure $callback, array $arguments)
    {
        if (
            $interceptor->getId() === View\Kaufland::MENU_ROOT_NODE_NICK
            && !isset($this->menuTitlesUsing[View\Kaufland::MENU_ROOT_NODE_NICK])
        ) {
            $wizard = $this->wizardHelper->getActiveWizard(
                View\Kaufland::NICK
            );

            if ($wizard === null) {
                $this->menuTitlesUsing[View\Kaufland::MENU_ROOT_NODE_NICK] = true;

                return 'Kaufland';
            }
        }

        return $callback(...$arguments);
    }

    private function getUrls()
    {
        return [
            'M2E_Kaufland::kaufland_help_center_knowledge_base'
            => 'https://help.m2epro.com/support/solutions/folders/9000194666',
        ];
    }

    private function renderOnClickCallback($url)
    {
        return "window.open('$url', '_blank'); return false;";
    }
}
