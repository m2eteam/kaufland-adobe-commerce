<?php

namespace M2E\Kaufland\Model\ActiveRecord;

abstract class AbstractModel extends \Magento\Framework\Model\AbstractModel
{
    protected bool $cacheLoading = false;

    protected ?\M2E\Kaufland\Model\Factory $modelFactory;
    protected ?\M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory;

    public function __construct(
        \M2E\Kaufland\Model\Factory $modelFactory = null,
        \M2E\Kaufland\Model\ActiveRecord\Factory $activeRecordFactory = null,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->modelFactory = $modelFactory;
        $this->activeRecordFactory = $activeRecordFactory;
    }

    //########################################

    public function getObjectModelName()
    {
        $className = \M2E\Core\Helper\Client::getClassName($this);

        return str_replace(['M2E\Kaufland\Model\\', '\\'], ['', '_'], $className);
    }

    //########################################

    public function isLoaded(): bool
    {
        return parent::getId() !== null;
    }

    public function getId(): ?int
    {
        $id = parent::getId();
        if ($id === null) {
            return null;
        }

        return (int)$id;
    }

    /**
     * @param int $modelId
     * @param null|string $field
     *
     * @return \M2E\Kaufland\Model\ActiveRecord\AbstractModel
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function load($modelId, $field = null)
    {
        parent::load($modelId, $field);

        if (!$this->isLoaded()) {
            throw new \M2E\Kaufland\Model\Exception\Logic(
                'Instance does not exist.',
                [
                    'id' => $modelId,
                    'field' => $field,
                    'model' => $this->_resourceName,
                ]
            );
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function save()
    {
        /** @var \M2E\Kaufland\Helper\Data\Cache\Permanent $helper */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Kaufland\Helper\Data\Cache\Permanent::class
        );

        if ($this->getId() !== null && $this->isCacheEnabled()) {
            $helper->removeTagValues($this->getCacheInstancesTag());
        }

        return parent::save();
    }

    /**
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function delete()
    {
        if ($this->getId() === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Method require loaded instance first');
        }

        if ($this->isLocked()) {
            return $this;
        }

        /** @var \M2E\Kaufland\Helper\Data\Cache\Permanent $helper */
        $helper = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \M2E\Kaufland\Helper\Data\Cache\Permanent::class
        );

        if ($this->isCacheEnabled()) {
            $helper->removeTagValues($this->getCacheInstancesTag());
        }

        return parent::delete();
    }

    //########################################

    /**
     * @deprecated
     */
    public function isLocked()
    {
        return false;
    }

    //########################################

    /**
     * @param string $fieldName
     *
     * @return array
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function getSettings(string $fieldName): array
    {
        $settings = $this->getData($fieldName);

        if ($settings === null) {
            return [];
        }

        $settings = \M2E\Core\Helper\Json::decode($settings);

        return !empty($settings) ? $settings : [];
    }

    /**
     * @param string $fieldName
     * @param string|array $settingNamePath
     * @param mixed $defaultValue
     *
     * @return mixed|null
     */
    public function getSetting(
        $fieldName,
        $settingNamePath,
        $defaultValue = null
    ) {
        if (empty($settingNamePath)) {
            return $defaultValue;
        }

        $settings = $this->getSettings($fieldName);

        !is_array($settingNamePath) && $settingNamePath = [$settingNamePath];

        foreach ($settingNamePath as $pathPart) {
            if (!isset($settings[$pathPart])) {
                return $defaultValue;
            }

            $settings = $settings[$pathPart];
        }

        if (is_numeric($settings)) {
            $settings = (int)$settings;
        }

        return $settings;
    }

    // ---------------------------------------

    /**
     * @param string $fieldName
     * @param array $settings
     *
     * @return \M2E\Kaufland\Model\ActiveRecord\AbstractModel
     * @throws \M2E\Kaufland\Model\Exception\Logic
     */
    public function setSettings($fieldName, array $settings = [])
    {
        $this->setData((string)$fieldName, \M2E\Core\Helper\Json::encode($settings));

        return $this;
    }

    /**
     * @param string $fieldName
     * @param string|array $settingNamePath
     * @param mixed $settingValue
     *
     * @return \M2E\Kaufland\Model\ActiveRecord\AbstractModel
     */
    public function setSetting(
        $fieldName,
        $settingNamePath,
        $settingValue
    ) {
        if (empty($settingNamePath)) {
            return $this;
        }

        $settings = $this->getSettings($fieldName);
        $target = &$settings;

        !is_array($settingNamePath) && $settingNamePath = [$settingNamePath];

        $currentPathNumber = 0;
        $totalPartsNumber = count($settingNamePath);

        foreach ($settingNamePath as $pathPart) {
            $currentPathNumber++;

            if (!array_key_exists($pathPart, $settings) && $currentPathNumber != $totalPartsNumber) {
                $target[$pathPart] = [];
            }

            if ($currentPathNumber != $totalPartsNumber) {
                $target = &$target[$pathPart];
                continue;
            }

            $target[$pathPart] = $settingValue;
        }

        $this->setSettings($fieldName, $settings);

        return $this;
    }

    //########################################

    /**
     * @return boolean
     */
    public function isCacheLoading()
    {
        return $this->cacheLoading;
    }

    /**
     * @param mixed $cacheLoading
     */
    public function setCacheLoading($cacheLoading)
    {
        $this->cacheLoading = $cacheLoading;
    }

    //########################################

    public function isCacheEnabled()
    {
        return false;
    }

    public function getCacheLifetime()
    {
        return 60 * 60 * 24;
    }

    // ---------------------------------------

    public function getCacheGroupTags()
    {
        $modelName = str_replace('M2E\Kaufland\Model\\', '', \M2E\Core\Helper\Client::getClassName($this));

        $tags[] = $modelName;

        $tags = array_unique($tags);
        $tags = array_map('strtolower', $tags);

        return $tags;
    }

    public function getCacheInstancesTag()
    {
        if ($this->getId() === null) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Method require loaded instance first');
        }

        return $this->getObjectModelName() . '_' . $this->getId();
    }
}
