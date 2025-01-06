<?php

declare(strict_types=1);

namespace M2E\Kaufland\Model\ControlPanel\Inspection;

class Definition
{
    /** @var string */
    private $nick;

    /** @var string */
    private $title;

    /** @var string */
    private $description;

    /** @var string */
    private $group;

    /** @var string */
    private $executionSpeedGroup;

    /** @var string */
    private $handler;

    public function __construct(
        string $nick,
        string $title,
        string $description,
        string $group,
        string $executionSpeedGroup,
        string $handler
    ) {
        $this->nick = $nick;
        $this->title = $title;
        $this->description = $description;
        $this->group = $group;
        $this->executionSpeedGroup = $executionSpeedGroup;
        $this->handler = $handler;
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getExecutionSpeedGroup()
    {
        return $this->executionSpeedGroup;
    }

    /**
     * @return string
     */
    public function getHandler()
    {
        return $this->handler;
    }
}
