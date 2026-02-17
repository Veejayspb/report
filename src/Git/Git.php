<?php
declare(strict_types=1);

namespace Veejay\Report\Git;

class Git
{
    /**
     * Путь до git.exe или название переменной окружения.
     * @var string
     */
    protected string $path = 'git';

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * Вернуть путь до git.
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
