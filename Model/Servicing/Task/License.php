<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Servicing\Task;

class License implements \M2E\Kaufland\Model\Servicing\TaskInterface
{
    public const NAME = 'license';

    private \M2E\Core\Model\LicenseService $licenseService;

    public function __construct(
        \M2E\Core\Model\LicenseService $licenseService
    ) {
        $this->licenseService = $licenseService;
    }

    // ----------------------------------------

    public function getServerTaskName(): string
    {
        return self::NAME;
    }

    // ----------------------------------------

    public function isAllowed(): bool
    {
        return true;
    }

    // ----------------------------------------

    public function getRequestData(): array
    {
        return [];
    }

    // ----------------------------------------

    public function processResponseData(array $data): void
    {
        $license = $this->licenseService->get();

        $info = $license->getInfo();
        if (isset($data['info']) && is_array($data['info'])) {
            $info = $this->updateInfoData($data['info'], $info);
        }

        if (
            isset($data['validation'])
            && is_array($data['validation'])
        ) {
            $info = $this->updateValidationMainData($data['validation'], $info);

            if (
                isset($data['validation']['validation'])
                && is_array($data['validation']['validation'])
            ) {
                $info = $this->updateValidationValidData($data['validation']['validation'], $info);
            }
        }

        if (isset($data['connection']) && is_array($data['connection'])) {
            $info = $this->updateConnectionData($data['connection'], $info);
        }

        $updatedLicense = $license->withInfo($info);

        $this->licenseService->update($updatedLicense);
    }

    // ----------------------------------------

    private function updateInfoData(array $infoData, \M2E\Core\Model\License\Info $info): \M2E\Core\Model\License\Info
    {
        if (array_key_exists('email', $infoData)) {
            $info = $info->withEmail((string)$infoData['email']);
        }

        return $info;
    }

    private function updateValidationMainData(
        array $validationData,
        \M2E\Core\Model\License\Info $info
    ): \M2E\Core\Model\License\Info {
        if (array_key_exists('domain', $validationData)) {
            $identifierDomain = $info->getDomainIdentifier()->withValidValue((string)$validationData['domain']);
            $info = $info->withDomainIdentifier($identifierDomain);
        }

        if (array_key_exists('ip', $validationData)) {
            $identifierIp = $info->getIpIdentifier()->withValidValue((string)$validationData['ip']);
            $info = $info->withIpIdentifier($identifierIp);
        }

        return $info;
    }

    private function updateValidationValidData(
        array $isValidData,
        \M2E\Core\Model\License\Info $info
    ): \M2E\Core\Model\License\Info {
        if (isset($isValidData['domain'])) {
            $identifierDomain = $info->getDomainIdentifier()->withValid((bool)$isValidData['domain']);
            $info = $info->withDomainIdentifier($identifierDomain);
        }

        if (isset($isValidData['ip'])) {
            $identifierIp = $info->getIpIdentifier()->withValid((bool)$isValidData['ip']);
            $info = $info->withIpIdentifier($identifierIp);
        }

        return $info;
    }

    private function updateConnectionData(array $data, \M2E\Core\Model\License\Info $info): \M2E\Core\Model\License\Info
    {
        if (array_key_exists('domain', $data)) {
            $identifierDomain = $info->getDomainIdentifier()->withRealValue((string)$data['domain']);
            $info = $info->withDomainIdentifier($identifierDomain);
        }

        if (array_key_exists('ip', $data)) {
            $identifierIp = $info->getIpIdentifier()->withRealValue((string)$data['ip']);
            $info = $info->withIpIdentifier($identifierIp);
        }

        return $info;
    }
}
