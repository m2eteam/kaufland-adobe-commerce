<?php

namespace M2E\Kaufland\Block\Adminhtml\Kaufland\Order;

use M2E\Kaufland\Block\Adminhtml\Magento\Form\AbstractContainer;

class View extends AbstractContainer
{
    /** @var \M2E\Kaufland\Helper\Data\GlobalData */
    private $globalDataHelper;
    private \M2E\Core\Helper\Url $urlHelper;

    public function __construct(
        \M2E\Kaufland\Block\Adminhtml\Magento\Context\Widget $context,
        \M2E\Core\Helper\Url $urlHelper,
        \M2E\Kaufland\Helper\Data\GlobalData $globalDataHelper,
        array $data = []
    ) {
        $this->urlHelper = $urlHelper;
        $this->globalDataHelper = $globalDataHelper;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        parent::_construct();

        $this->setId('kauflandOrderView');
        $this->_controller = 'adminhtml_Kaufland_order';
        $this->_mode = 'view';

        /** @var \M2E\Kaufland\Model\Order $order */
        $order = $this->globalDataHelper->getValue('order');

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->urlHelper->getBackUrl('*/Kaufland_order/index');
        $this->addButton('back', [
            'label' => __('Back'),
            'onclick' => 'CommonObj.backClick(\'' . $url . '\')',
            'class' => 'back',
        ]);

        if ($order->canUpdateShippingStatus()) {
            $url = $this->getUrl('*/*/updateShippingStatus', ['id' => $order->getId()]);
            $this->addButton('ship', [
                'label' => __('Mark as Shipped'),
                'onclick' => "setLocation('" . $url . "');",
                'class' => 'primary',
            ]);
        }

        if ($order->getReserve()->isPlaced()) {
            $url = $this->getUrl('*/order/reservationCancel', ['ids' => $order->getId()]);
            $this->addButton('reservation_cancel', [
                'label' => __('Cancel QTY Reserve'),
                'onclick' => "confirmSetLocation(Kaufland.translator.translate('Are you sure?'), '" . $url . "');",
                'class' => 'primary',
            ]);
        } elseif ($order->isReservable()) {
            $url = $this->getUrl('*/order/reservationPlace', ['ids' => $order->getId()]);
            $this->addButton('reservation_place', [
                'label' => __('Reserve QTY'),
                'onclick' => "confirmSetLocation(Kaufland.translator.translate('Are you sure?'), '" . $url . "');",
                'class' => 'primary',
            ]);
        }

        if ($order->canCreateMagentoOrder()) {
            $url = $this->getUrl('*/*/createMagentoOrder', ['id' => $order->getId()]);
            $this->addButton('order', [
                'label' => __('Create Magento Order'),
                'onclick' => "setLocation('" . $url . "');",
                'class' => 'primary',
            ]);
        }
    }

    protected function _beforeToHtml()
    {
        $this->js->addRequireJs(['debug' => 'Kaufland/Order/Debug'], '');

        return parent::_beforeToHtml();
    }
}
