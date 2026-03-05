<?php
declare(strict_types=1);

namespace Veejay\Report;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use ZipArchive;

class Docx
{
    /**
     * Путь до файла DOCX с шаблоном.
     * @var string
     */
    protected string $input;

    /**
     * @param string $input
     */
    public function __construct(string $input)
    {
        $this->input = $input;
    }

    /**
     * Заполнить DOCX шаблон данными и положить по указанному адресу.
     * @param string $output - путь до готового DOCX документа.
     * @param array $placeholders - массив плейсхолдеров для замены в шаблоне
     * @return void
     */
    public function run(string $output, array $placeholders): void
    {
        $tempDir = sys_get_temp_dir() . '/docx_' . uniqid();
        mkdir($tempDir);

        // Распаковка
        $this->unzip($tempDir);

        // Замена в document.xml
        $docPath = "$tempDir/word/document.xml";
        $this->replace($docPath, $placeholders);

        // ПЕРЕСБОРКА с соблюдением порядка
        $files = $this->getFiles($tempDir);

        // Создаём новый архив
        $this->zip($tempDir, $output, $files);

        // Удаляем временную директорию
        $this->removeDir($tempDir);
    }

    /**
     * Удалить указанную директорию.
     * @param string $dir
     */
    protected function removeDir(string $dir): void
    {
        foreach (scandir($dir) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? $this->removeDir($path) : unlink($path);
        }

        rmdir($dir);
    }

    /**
     * Распаковать архив.
     * @param string $tempDir
     * @return void
     */
    private function unzip(string $tempDir): void
    {
        $zip = new ZipArchive;

        if (!$zip->open($this->input)) {
            throw new RuntimeException('Не удалось открыть DOCX');
        }

        if (!$zip->extractTo($tempDir)) {
            throw new RuntimeException('Не удалось извлечь содержимое архива');
        }

        $zip->close();
    }

    /**
     * Заменить плейсхолдеры в указанном файле.
     * @param string $file
     * @param array $placeholders
     * @return void
     */
    private function replace(string $file, array $placeholders): void
    {
        $placeholders = array_combine(
            array_map(fn($key) => '{' . $key . '}', array_keys($placeholders)),
            $placeholders
        );

        $content = file_get_contents($file);
        $content = str_replace(array_keys($placeholders), array_values($placeholders), $content);
        file_put_contents($file, $content);
    }

    /**
     * Вернуть коллекцию файлов для сборки архива.
     * @param string $tempDir
     * @return array
     */
    private function getFiles(string $tempDir): array
    {
        $files = [];
        $recursiveDirectoryIterator = new RecursiveDirectoryIterator($tempDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($recursiveDirectoryIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($iterator as $file) {
            $localPath = substr($file->getPathname(), strlen($tempDir) + 1);

            // Убираем обратные слеши на Windows
            $localPath = str_replace('\\', '/', $localPath);

            if (!$file->isDir()) {
                $files[] = $localPath;
            }
        }

        // Сортируем: [Content_Types].xml — первым, остальное — по алфавиту
        usort($files, function ($a, $b) {
            if ($a === '[Content_Types].xml') return -1;
            if ($b === '[Content_Types].xml') return 1;
            return strcmp($a, $b);
        });

        return $files;
    }

    /**
     * Собрать архив из коллекции файлов.
     * @param string $tempDir
     * @param string $output
     * @param array $files
     * @return void
     */
    private function zip(string $tempDir, string $output, array $files): void
    {
        $zip = new ZipArchive;

        if (!$zip->open($output, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new RuntimeException('Не удалось создать DOCX');
        }

        foreach ($files as $file) {
            $zip->addFile("$tempDir/$file", $file);
        }

        $zip->close();
    }
}
