<?php
declare(strict_types=1);

namespace Veejay\Report\Component;

class Config
{
    /**
     * Даты праздников.
     * @var array
     */
    public array $holidays = [];

    /**
     * Даты рабочих СБ и ВС.
     * @var array
     */
    public array $workdays = [];

    /**
     * Даты отпусков.
     * @var array
     */
    public array $vacation = [];

    /**
     * Список репозиториев, из которых требуется извлекать коммиты.
     * ключ - название
     * значение - путь до репозитория
     * @var array
     */
    public array $repositories = [];

    /**
     * E-mail'ы, от имени которых публиковались коммиты.
     * @var array
     * @todo: если пусто, то брать все коммиты.
     */
    public array $emails = [];

    /**
     * Список фиксированных плейсхолдеров.
     * @var array
     */
    public array $placeholders = [];

    /**
     * Путь до git.exe, либо название переменной окружения.
     * @var string
     */
    public string $gitExe = 'git';

    /**
     * Год, за который собирается отчет.
     * @var int
     */
    protected int $year;

    /**
     * Месяц, за который собирается отчет.
     * @var int
     */
    protected int $month;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        foreach ($config as $property => $value) {
            if (property_exists($this, $property)) {
                $this->$property = $value;
            }
        }
    }

    /**
     * Вернуть год, за который собирается отчет.
     * @return int
     */
    public function getYear(): int
    {
        return $this->year ?? (int)date('Y');
    }

    /**
     * Вернуть месяц, за который собирается отчет.
     * @return int
     */
    public function getMonth(): int
    {
        return $this->month ?? 1;
    }
}
