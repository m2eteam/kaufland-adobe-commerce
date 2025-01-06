<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\Template;

interface PolicyInterface
{
    public function getId(): ?int;
    public function getNick(): string;
    public function getTitle(): string;
}
