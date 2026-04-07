<?php
declare(strict_types=1);

namespace Veejay\Report;

use DateTime;
use Psr\Container\ContainerInterface;
use Veejay\Report\Component\Config;
use Veejay\Report\Factory\DocxFactory;
use Veejay\Report\Git\Commit;
use Veejay\Report\Git\RepositoriesCollection;

class Application
{
    protected const DOW = [
        'Воскресение',
        'Понедельник',
        'Вторник',
        'Среда',
        'Четверг',
        'Пятница',
        'Суббота',
    ];

    /**
     * Объект-конфигуратор.
     * @var Config
     */
    protected Config $config;

    /**
     * DI контейнер.
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @param Config $config
     * @param ContainerInterface $container
     */
    public function __construct(Config $config, ContainerInterface $container)
    {
        $this->config = $config;
        $this->container = $container;
    }

    /**
     * Запуск приложения.
     * @return void
     */
    public function run(): void
    {
        $year = $this->config->getYear();
        $month = $this->config->getMonth();
        $days = $this->getDaysInMonth($year, $month);

        $placeholders = [
            'date_from' => (new DateTime("$year-$month-01"))->format('d.m.Y'),
            'date_to' => (new DateTime("$year-$month-$days"))->format('d.m.Y'),
        ];

        for ($d = 1; $d <= 31; $d++) {
            if ($days < $d) {
                $head = $body = '';
            } else {
                $dateTime = new DateTime("$year-$month-$d");

                $head = $this->getHead($dateTime);
                $body = $this->getBody($dateTime);
            }

            $placeholders["{$d}_head"] = $head;
            $placeholders["{$d}_body"] = $body;
        }

        $output = sprintf(
            '%s/report/report_%s-%s.docx',
            dirname(__DIR__),
            $year,
            str_pad((string)$month, 2, '0', STR_PAD_LEFT)
        );
        $docx = (new DocxFactory)->createByDays($days);
        $docx->run($output, $this->config->placeholders + $placeholders);
    }

    /**
     * Сгенерировать заголовок за указанную дату.
     * @param DateTime $dateTime
     * @return string
     */
    protected function getHead(DateTime $dateTime): string
    {
        $ymd = $dateTime->format('d.m.Y'); // Year Month Day
        $dowIndex = (int)$dateTime->format('w');
        $dow = self::DOW[$dowIndex]; // Day Of the Week
        $date = "$ymd - $dow";
        $label = $this->getLabel($dateTime);

        if (!is_null($label)) {
            $date .= " ($label)";
        }

        return $date;
    }

    /**
     * Сгенерировать описание за указанную дату.
     * @param DateTime $dateTime
     * @return string
     */
    protected function getBody(DateTime $dateTime): string
    {
        $items = [];
        $collection = $this->container->get(RepositoriesCollection::class); /* @var RepositoriesCollection $collection */

        foreach ($this->config->repositories as $name => $path) {
            $repository = $collection->getRepository($path);
            $commits = $repository->getCommitsByDate($dateTime);

            $commits = array_filter($commits, function (Commit $commit) {
                // TODO: фильтровать Merge коммиты
                return in_array($commit->email, $this->config->emails);
            });

            foreach ($commits as $commit) {
                $items[] = "($name) $commit->description";
            }
        }

        return implode(PHP_EOL, $items); // TODO: VKdisk не воспринимает эти переносы
    }

    /**
     * Вернуть ярлык для указанной даты.
     * @param DateTime $dateTime
     * @return string|null
     */
    protected function getLabel(DateTime $dateTime): ?string
    {
        $isVacation = in_array($dateTime->format('Y-m-d'), $this->config->vacation);
        $isHoliday = in_array($dateTime->format('Y-m-d'), $this->config->holidays);
        $isWorkday = in_array($dateTime->format('Y-m-d'), $this->config->workdays);

        if ($isVacation) {
            return 'отпуск';
        }

        if ($isHoliday) {
            return 'праздник';
        }

        if ($isWorkday) {
            return 'рабочий';
        }

        return null;
    }

    /**
     * Количество дней в указанном месяце.
     * @param int $year
     * @param int $month
     * @return int
     */
    protected function getDaysInMonth(int $year, int $month): int
    {
        $date = new DateTime("$year-$month-01");
        return (int)$date->format('t');
    }
}
