<?php

declare(strict_types=1);

namespace M2E\Kaufland\Ui\Component;

class ExportButton extends \Magento\Ui\Component\ExportButton
{
    public function prepare(): void
    {
        $context = $this->getContext();
        $config = $this->getData('config');
        if (isset($config['options'])) {
            $options = [];
            foreach ($config['options'] as $option) {
                if ($option['value'] === 'xml') {
                    continue;
                }

                $additionalParams = $this->getAdditionalParams($config, $context);
                $option['url'] = $this->urlBuilder->getUrl($option['url'], $additionalParams);
                $options[] = $option;
            }
            $config['options'] = $options;
            $this->setData('config', $config);
        }

        parent::prepare();
    }
}
