<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Listing\Wizard;

class CreateUnmanaged extends \M2E\Kaufland\Controller\Adminhtml\AbstractListing
{
    use \M2E\Kaufland\Controller\Adminhtml\Listing\Wizard\WizardTrait;

    private \M2E\Kaufland\Model\Listing\Repository $listingRepository;
    private \M2E\Kaufland\Model\Listing\Wizard\Create $createModel;
    private \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository;
    private \M2E\Kaufland\Helper\Data\Session $sessionDataHelper;
    private \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory;
    private \M2E\Kaufland\Model\Product\Repository $productRepository;

    public function __construct(
        \M2E\Kaufland\Model\Listing\Repository $listingRepository,
        \M2E\Kaufland\Model\Listing\Wizard\Create $createModel,
        \M2E\Kaufland\Model\Listing\Other\Repository $listingOtherRepository,
        \M2E\Kaufland\Helper\Data\Session $sessionDataHelper,
        \M2E\Kaufland\Model\Listing\Wizard\ManagerFactory $wizardManagerFactory,
        \M2E\Kaufland\Model\Product\Repository $productRepository
    ) {
        parent::__construct();

        $this->listingRepository = $listingRepository;
        $this->createModel = $createModel;
        $this->listingOtherRepository = $listingOtherRepository;
        $this->sessionDataHelper = $sessionDataHelper;
        $this->wizardManagerFactory = $wizardManagerFactory;
        $this->productRepository = $productRepository;
    }

    public function execute()
    {
        $listingId = (int)$this->getRequest()->getParam('listing_id');
        if (empty($listingId)) {
            $this->getMessageManager()->addError(__('Cannot start Wizard, Listing must be created first.'));

            return $this->_redirect('*/kaufland_listing/index');
        }

        $listing = $this->listingRepository->get($listingId);
        $wizard = $this->createModel->process($listing, \M2E\Kaufland\Model\Listing\Wizard::TYPE_UNMANAGED);
        $manager = $this->wizardManagerFactory->create($wizard);

        $sessionKey = \M2E\Kaufland\Helper\View::MOVING_LISTING_OTHER_SELECTED_SESSION_KEY;
        $selectedProducts = $this->sessionDataHelper->getValue($sessionKey);

        $errorsCount = 0;
        foreach ($selectedProducts as $otherListingId) {
            $unmanagedProduct = $this->listingOtherRepository->get((int)$otherListingId);

            if ($this->productRepository->findByListingAndMagentoProductId($listing, $unmanagedProduct->getMagentoProductId())) {
                $errorsCount++;
                continue;
            }

            $wizardProduct = $manager->addUnmanagedProduct($unmanagedProduct);

            if ($wizardProduct === null) {
                $errorsCount++;
            }
        }

        $this->sessionDataHelper->removeValue($sessionKey);

        if ($errorsCount) {
            if (count($selectedProducts) == $errorsCount) {
                $manager->cancel();

                $this->getMessageManager()->addErrorMessage(
                    __(
                        'Products were not moved because they already exist in the selected Listing or do not
                            belong to the channel account or storefront of the listing.'
                    )
                );

                return $this->_redirect('*/product_grid/unmanaged');
            }

            $this->getMessageManager()->addErrorMessage(
                __(
                    'Some products were not moved because they already exist in the selected Listing or do not
                    belong to the channel account or storefront of the listing.'
                )
            );
        }

        return $this->_redirect('*/listing_wizard/index', ['id' => $wizard->getId()]);
    }
}
