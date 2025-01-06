<?php

declare(strict_types=1);

namespace M2E\Kaufland\Controller\Adminhtml\Kaufland\Category;

class SaveCategoryAttributesAjax extends \M2E\Kaufland\Controller\Adminhtml\Kaufland\AbstractCategory
{
    private \M2E\Kaufland\Model\Category\AttributeFactory $attributeFactory;
    private \M2E\Kaufland\Model\Category\Dictionary\Repository $dictionaryRepository;
    private \M2E\Kaufland\Model\Category\Attribute\Manager $attributeManager;

    public function __construct(
        \M2E\Kaufland\Model\Category\AttributeFactory $attributeFactory,
        \M2E\Kaufland\Model\Category\Dictionary\Repository $dictionaryRepository,
        \M2E\Kaufland\Model\Category\Attribute\Manager $attributeManager
    ) {
        parent::__construct();

        $this->attributeFactory = $attributeFactory;
        $this->dictionaryRepository = $dictionaryRepository;
        $this->attributeManager = $attributeManager;
    }

    public function execute()
    {
        $post = $this->getRequest()->getPost()->toArray();

        if (
            empty($post['dictionary_id'])
            || empty($post['attributes'])
        ) {
            throw new \M2E\Kaufland\Model\Exception\Logic('Invalid input');
        }

        try {
            $attributes = json_decode($post['attributes'], true);
            $dictionary = $this->dictionaryRepository->get((int)$post['dictionary_id']);

            $allAttributes = array_merge(
                array_values($attributes['real_attributes'] ?? []),
            );

            $allAttributes = $this->getAttributes($dictionary->getId(), $allAttributes);

            $this->attributeManager->createOrUpdateAttributes($allAttributes, $dictionary);
        } catch (\M2E\Kaufland\Model\Exception\Logic $e) {
            $this->setJsonContent(
                [
                    'success' => false,
                    'messages' => [
                        ['error' => 'Attributes not saved'],
                    ],
                ]
            );
        }

        $this->setJsonContent(['success' => true]);

        return $this->getResult();
    }

    /**
     * @param int $dictionaryId
     * @param array $inputAttributes
     *
     * @return \M2E\Kaufland\Model\Category\Attribute[]
     */
    private function getAttributes(int $dictionaryId, array $inputAttributes): array
    {
        $attributes = [];
        foreach ($inputAttributes as $inputAttribute) {
            $recommendedValues = [];
            if (isset($inputAttribute['value_kaufland_recommended'])) {
                $recommendedValues = $this->getRecommendedValues($inputAttribute['value_kaufland_recommended']);
            }
            $attributes[] = $this->attributeFactory->create()->create(
                $dictionaryId,
                $inputAttribute['attribute_type'],
                $inputAttribute['attribute_id'],
                $inputAttribute['attribute_nick'],
                $inputAttribute['attribute_title'],
                $inputAttribute['attribute_description'],
                (int)$inputAttribute['value_mode'],
                $recommendedValues,
                $inputAttribute['value_custom_value'] ?? '',
                $inputAttribute['value_custom_attribute'] ?? ''
            );
        }

        return $attributes;
    }

    /**
     * @param array|string $inputValues
     *
     * @return string[]
     */
    private function getRecommendedValues($inputValues): array
    {
        if (is_string($inputValues)) {
            $inputValues = [$inputValues];
        }

        $values = [];
        foreach ($inputValues as $value) {
            if (!empty($value)) {
                $values[] = $value;
            }
        }

        return $values;
    }
}
