<?php

namespace App\Entity;

use App\Repository\EntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Wpis: notatka z datą i godziną należąca do nastawu.
 * Czas wpisu to lokalny czas ścienny Europe/Warsaw z dokładnością do minuty.
 */
#[ORM\Entity(repositoryClass: EntryRepository::class)]
class Entry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private Batch $batch;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Assert\NotNull(message: 'Podaj datę i godzinę wpisu.')]
    private ?\DateTimeImmutable $occurredAt = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'Wpisz notatkę.', normalizer: 'trim')]
    private ?string $note = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct(Batch $batch, \DateTimeImmutable $createdAt)
    {
        $this->batch = $batch;
        $this->createdAt = $createdAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBatch(): Batch
    {
        return $this->batch;
    }

    public function getOccurredAt(): ?\DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * Obcina sekundy: czas wpisu ma dokładność do minuty.
     */
    public function setOccurredAt(?\DateTimeImmutable $occurredAt): void
    {
        $this->occurredAt = $occurredAt?->setTime((int) $occurredAt->format('H'), (int) $occurredAt->format('i'));
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): void
    {
        $this->note = $note;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    /**
     * Miękkie usunięcie: wpis znika z aplikacji, ale zostaje w bazie.
     */
    public function markDeleted(\DateTimeImmutable $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }
}
