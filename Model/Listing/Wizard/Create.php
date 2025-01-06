<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Listing\Wizard;

class Create
{
    /** @var \M2E\Kaufland\Model\Listing\Wizard\Repository */
    private Repository $wizardRepository;
    private \M2E\Kaufland\Model\Listing\WizardFactory $wizardFactory;
    /** @var \M2E\Kaufland\Model\Listing\Wizard\StepFactory */
    private StepFactory $stepFactory;
    /** @var \M2E\Kaufland\Model\Listing\Wizard\StepDeclarationCollectionFactory */
    private StepDeclarationCollectionFactory $stepDeclarationCollectionFactory;
    /** @var \M2E\Kaufland\Model\Listing\Wizard\DeleteService */
    private DeleteService $deleteService;

    public function __construct(
        Repository $wizardRepository,
        \M2E\Kaufland\Model\Listing\WizardFactory $wizardFactory,
        \M2E\Kaufland\Model\Listing\Wizard\StepFactory $stepFactory,
        StepDeclarationCollectionFactory $stepDeclarationCollectionFactory,
        \M2E\Kaufland\Model\Listing\Wizard\DeleteService $deleteService
    ) {
        $this->wizardRepository = $wizardRepository;
        $this->wizardFactory = $wizardFactory;
        $this->stepFactory = $stepFactory;
        $this->stepDeclarationCollectionFactory = $stepDeclarationCollectionFactory;
        $this->deleteService = $deleteService;
    }

    public function process(\M2E\Kaufland\Model\Listing $listing, string $type): \M2E\Kaufland\Model\Listing\Wizard
    {
        \M2E\Kaufland\Model\Listing\Wizard::validateType($type);

        $existWizard = $this->wizardRepository->findNotCompletedByListingAndType($listing, $type);
        if ($existWizard !== null) {
            return $existWizard;
        }

        $stepsDeclaration = $this->stepDeclarationCollectionFactory->create($type);

        $wizard = $this->wizardFactory->create()
                                      ->init($listing, $type, $stepsDeclaration->getFirst()->getNick());
        $this->wizardRepository->create($wizard);

        $steps = [];
        foreach ($stepsDeclaration->getAll() as $stepDeclaration) {
            $steps[] = $this->stepFactory->create()
                                         ->init($wizard, $stepDeclaration->getNick());
        }

        $this->wizardRepository->createSteps($steps);

        $wizard->initSteps($steps);

        $this->deleteService->removeOld();

        return $wizard;
    }
}
