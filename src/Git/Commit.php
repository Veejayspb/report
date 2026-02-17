<?php
declare(strict_types=1);

namespace Veejay\Report\Git;

class Commit
{
    /**
     * Хэш.
     * @var string
     */
    public string $hash;

    /**
     * Временная метка.
     * @var int
     */
    public int $timestamp;

    /**
     * Почта автора.
     * @var string
     */
    public string $email;

    /**
     * Описание.
     * @var string
     */
    public string $description;

    /**
     * @param string $hash
     * @param int $timestamp
     * @param string $email
     * @param string $description
     */
    public function __construct(string $hash, int $timestamp, string $email, string $description)
    {
        $this->hash = $hash;
        $this->timestamp = $timestamp;
        $this->email = $email;
        $this->description = $description;
    }
}
