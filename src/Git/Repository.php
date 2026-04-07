<?php
declare(strict_types=1);

namespace Veejay\Report\Git;

use DateTime;

class Repository
{
    protected const SEPARATOR = "\x1F";

    /**
     * Путь до директории с git репозиторием.
     * @var string
     */
    protected string $path;

    /**
     * Экземпляр объекта Git.
     * @var Git
     */
    protected Git $git;

    /**
     * Список коммитов.
     * @var array
     */
    protected array $commits;

    /**
     * @param string $path
     * @param Git $git
     */
    public function __construct(string $path, Git $git)
    {
        $this->path = $path;
        $this->git = $git;
    }

    /**
     * Список коммитов во всех ветках.
     * @return Commit[]
     */
    public function getCommits(): array
    {
        if (isset($this->commits)) {
            return $this->commits;
        }

        $format = implode(self::SEPARATOR, ['%h', '%at', '%ae', '%s']);
        $command = sprintf('"%s" --no-pager log --all --format="%s"', $this->git->getPath(), $format);
        $lines = $this->exec($command);
        $commits = [];

        foreach ($lines as $line) {
            $parts = explode(self::SEPARATOR, $line);
            $commits[] = new Commit($parts[0], (int)$parts[1], $parts[2], $parts[3]);
        }

        return $this->commits = array_reverse($commits);
    }

    /**
     * Отфильтровать коммиты.
     * @param callable $filter
     * @return Commit[]
     */
    public function getCommitsFilter(callable $filter): array
    {
        $commits = $this->getCommits();
        return array_filter($commits, $filter);
    }

    /**
     * Выбрать все коммиты за указанную дату.
     * @param DateTime $dateTime
     * @return Commit[]
     */
    public function getCommitsByDate(DateTime $dateTime): array
    {
        return $this->getCommitsFilter(function (Commit $commit) use ($dateTime) {
            $dt = (new DateTime)->setTimestamp($commit->timestamp);
            $format = 'Y-m-d';
            return $dt->format($format) == $dateTime->format($format);
        });
    }

    /**
     * Является ли указанный коммит MERGE.
     * @param string $hash
     * @return bool
     */
    public function isMergeCommit(string $hash): bool
    {
        $command = sprintf('"%s" cat-file -p %s', $this->git->getPath(), $hash);
        $lines = $this->exec($command);

        $parent = 0;
        foreach ($lines as $line) {
            if (str_starts_with($line, 'parent')) {
                $parent++;
            }
        }

        return 1 < $parent;
    }

    /**
     * Список названий веток.
     * @return array
     */
    public function getBranches(): array
    {
        $command = sprintf('"%s" branch --format=%s 2>&1', $this->git->getPath(), '%(refname:short)');
        return $this->exec($command);
    }

    /**
     * Существует ли ветка с указанным названием.
     * @param string $name
     * @return bool
     */
    public function hasBranch(string $name): bool
    {
        $branches = $this->getBranches();
        return in_array($name, $branches);
    }

    /**
     * Выполнить команду в рабочей директории и разбить ответ построчно.
     * @param string $command
     * @return array
     */
    protected function exec(string $command): array
    {
        $output = $this->execRaw($command);
        $parts = explode("\n", $output);
        array_pop($parts); // Последняя строка всегда пустая
        return $parts;
    }

    /**
     * Выполнить команду в рабочей директории с git репозиторием и вернуть необработанный ответ.
     * @param string $command
     * @return string
     */
    protected function execRaw(string $command): string
    {
        $command = "cd $this->path && " . $command;
        return shell_exec($command);
    }
}
