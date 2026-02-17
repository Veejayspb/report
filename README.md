Отчёты
======

Генератор отчетов о проделанной работе на основе активности в указанных GIT репозиториях.

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

Установка
---------

```sh
cd path/to/dir
git clone git@github.com:Veejayspb/report.git
composer install
```
Последняя команда автоматически создаст директорию **report** для готовых отчетов и конфигурационный файл **config.php** из **config.php.dist**.
Далее - заполняем **config.php**, следуя инструкциям внутри. Никакие другие файлы править не требуется.

Запуск
------

```sh
php index.php
```
Готовый отчет с именем вида **report_0000-00.docx** появится в директории **report**.
