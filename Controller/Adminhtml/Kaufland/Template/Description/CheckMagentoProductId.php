<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Template\Description;

use M2E\Kaufland\Controller\Adminhtml\Kaufland\Template\AbstractDescription;

class CheckMagentoProductId extends AbstractDescription
{
    public function execute()
    {
        $productId = $this->getRequest()->getParam('product_id', -1);

        $this->setJsonContent([
            'result' => $this->isMagentoProductExists($productId),
        ]);

        return $this->getResult();
    }
}
