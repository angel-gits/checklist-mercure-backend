<?php

namespace App\Entity;

use DateTime;
use JsonSerializable;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\TaskRepository;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 */
class Task implements JsonSerializable
{
    const NOT_IMPORTANT = 0;
    const IMPORTANT = 1;
    const VERY_IMPORTANT = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @ORM\Column(type="boolean")
     */
    private $done;

    public function __construct(string $name, DateTime $date, int $status, ?string $description)
    {
        $this->name = $name;
        $this->description = $description;
        $this->date = $date;
        $this->status = $status;
        $this->done = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?DateTime
    {
        return $this->date;
    }

    public function setDate(DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDone(): ?bool
    {
        return $this->done;
    }

    public function setDone(bool $done): self
    {
        $this->done = $done;

        return $this;
    }

    public function jsonSerialize()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'date' => $this->date->format('d-m-Y'),
            'status' => $this->status,
            'done' => $this->done
        );
    }
}
