<?php

namespace M2E\Kaufland\Model\Tag;

class Repository
{
    /** @var \M2E\Kaufland\Model\TagFactory */
    private $tagFactory;
    /** @var \M2E\Kaufland\Model\ResourceModel\Tag\CollectionFactory */
    private $collectionFactory;
    /** @var \M2E\Kaufland\Model\Tag\EntityFactory */
    private $entityFactory;

    private $isLoad = false;
    private $entitiesByErrorCode = [];
    private $entitiesById = [];
    /** @var \M2E\Kaufland\Model\Tag[] */
    private $tags;

    public function __construct(
        \M2E\Kaufland\Model\TagFactory $tagFactory,
        \M2E\Kaufland\Model\ResourceModel\Tag\CollectionFactory $collectionFactory,
        EntityFactory $entityFactory
    ) {
        $this->tagFactory = $tagFactory;
        $this->entityFactory = $entityFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function create(\M2E\Kaufland\Model\Tag $tag): void
    {
        if ($this->has($tag)) {
            return;
        }

        $entity = $this->entityFactory->create(
            $tag->getText(),
            $tag->getErrorCode(),
            \M2E\Core\Helper\Date::createCurrentGmt()
        );
        $entity->save();

        $this->tags[$entity->getErrorCode()] = $tag;
        $this->entitiesById[$entity->getId()] = $entity;
        $this->entitiesByErrorCode[$entity->getErrorCode()] = $entity;
    }

    public function has(\M2E\Kaufland\Model\Tag $tag): bool
    {
        $this->load();

        return isset($this->tags[$tag->getErrorCode()]);
    }

    /**
     * @return \M2E\Kaufland\Model\Tag[]
     */
    public function getAllTags(): array
    {
        $this->load();

        return array_values($this->tags);
    }

    public function findEntityById(int $id): ?\M2E\Kaufland\Model\Tag\Entity
    {
        $this->load();

        return $this->entitiesById[$id] ?? null;
    }

    /**
     * @return \M2E\Kaufland\Model\Tag\Entity[]
     */
    public function getAllEntities(): array
    {
        $this->load();

        return array_values($this->entitiesById);
    }

    // ----------------------------------------

    private function load(): void
    {
        if ($this->isLoad) {
            return;
        }

        $this->entitiesById = [];
        $this->entitiesByErrorCode = [];

        $collection = $this->collectionFactory->create();
        foreach ($collection->getAll() as $item) {
            $this->entitiesById[$item->getId()] = $item;
            $this->entitiesByErrorCode[$item->getErrorCode()] = $item;
            $this->tags[$item->getErrorCode()] = $this->tagFactory->create($item->getErrorCode(), $item->getText());
        }

        $this->isLoad = true;
    }
}
