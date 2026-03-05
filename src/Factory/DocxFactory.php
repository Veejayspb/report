<?php

namespace Veejay\Report\Factory;

use Veejay\Report\Docx;

class DocxFactory
{
    /**
     * Создать объект с шаблоном на основе кол-ва дней в текущем месяце.
     * В разных шаблонах кол-во ячеек соответствует кол-ву дней в месяце.
     * @param int $days
     * @return Docx
     */
    public function createByDays(int $days): Docx
    {
        $days = in_array($days, [28, 29, 30, 31]) ? $days : 31;
        $fileName = sprintf('%d_days.docx', $days);
        $pathParts = [dirname(__DIR__), 'Template', $fileName];
        $path = implode(DIRECTORY_SEPARATOR, $pathParts);
        return new Docx($path);
    }
}
