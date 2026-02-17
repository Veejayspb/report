<?php
declare(strict_types=1);

namespace Veejay\Report\Git;

class RepositoriesCollection
{
    /**
     * Экземпляр объекта Git.
     * @var Git
     */
    protected Git $git;

    /**
     * Список добавленных репозиториев.
     * @var array
     */
    protected array $repositories = [];

    /**
     * @param Git $git
     */
    public function __construct(Git $git)
    {
        $this->git = $git;
    }

    /**
     * Вернуть объект репозитория.
     * @param string $path - путь до директории с git репозиторием
     * @return Repository|null
     */
    public function getRepository(string $path): ?Repository
    {
        if (!array_key_exists($path, $this->repositories)) {
            $this->repositories[$path] = new Repository($path, $this->git);
        }

        return $this->repositories[$path];
    }
}
