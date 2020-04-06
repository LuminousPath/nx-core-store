<?php
/**
 * This file is part of NxFIFTEEN Fitness Core.
 *
 * @link      https://nxfifteen.me.uk/projects/nx-health/store
 * @link      https://nxfifteen.me.uk/projects/nx-health/
 * @link      https://git.nxfifteen.rocks/nx-health/store
 * @author    Stuart McCulloch Anderson <stuart@nxfifteen.me.uk>
 * @copyright Copyright (c) 2020. Stuart McCulloch Anderson <stuart@nxfifteen.me.uk>
 * @license   https://nxfifteen.me.uk/api/license/mit/license.html MIT
 */
/** @noinspection DuplicatedCode */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PatientMembershipRepository")
 */
class PatientMembership
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tear;

    /**
     * @ORM\Column(type="datetime")
     */
    private $since;

    /**
     * @ORM\Column(type="boolean")
     */
    private $active;

    /**
     * @ORM\Column(type="boolean")
     */
    private $lifetime;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Patient", inversedBy="membership", cascade={"persist", "remove"})
     */
    private $patient;

    /**
     * @ORM\Column(type="datetime")
     */
    private $lastPaid;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getTear(): ?string
    {
        return $this->tear;
    }

    /**
     * @param string $tear
     *
     * @return $this
     */
    public function setTear(string $tear): self
    {
        $this->tear = $tear;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getSince(): ?\DateTimeInterface
    {
        return $this->since;
    }

    /**
     * @param \DateTimeInterface $since
     *
     * @return $this
     */
    public function setSince(\DateTimeInterface $since): self
    {
        $this->since = $since;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getActive(): ?bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     *
     * @return $this
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getLifetime(): ?bool
    {
        return $this->lifetime;
    }

    /**
     * @param bool $lifetime
     *
     * @return $this
     */
    public function setLifetime(bool $lifetime): self
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    /**
     * @return Patient|null
     */
    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    /**
     * @param Patient|null $patient
     *
     * @return $this
     */
    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastPaid(): ?\DateTimeInterface
    {
        return $this->lastPaid;
    }

    /**
     * @param \DateTimeInterface $lastPaid
     *
     * @return $this
     */
    public function setLastPaid(\DateTimeInterface $lastPaid): self
    {
        $this->lastPaid = $lastPaid;

        return $this;
    }
}
