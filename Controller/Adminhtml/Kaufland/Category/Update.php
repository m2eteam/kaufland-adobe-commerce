<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Category;

use M2E\Kaufland\Model\ResourceModel\Category\Dictionary\CollectionFactory as DictionaryCollectionFactory;

class Update extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractCategory
{
    private \M2E\Kaufland\Model\Category\Dictionary\UpdateService $updateService;
    private \M2E\Kaufland\Model\Category\Dictionary\Repository $repository;

    public function __construct(
        \M2E\Kaufland\Model\Category\Dictionary\UpdateService $updateService,
        \M2E\Kaufland\Model\Category\Dictionary\Repository $repository
    ) {
        parent::__construct();

        $this->updateService = $updateService;
        $this->repository = $repository;
    }

    public function execute()
    {
        try {
            foreach ($this->repository->getAllItems() as $category) {
                /** @var \M2E\Kaufland\Model\Category\Dictionary $category */
                $this->updateService->update($category);
            }

            $this->messageManager->addSuccessMessage(__(
                'Category data has been updated.',
            ));
        } catch (\Throwable $exception) {
            $this->messageManager->addErrorMessage(__(
                'Category data failed to be updated, please try again.',
            ));
        }

        return $this->_redirect('*/kaufland_template_category/index');
    }
}
