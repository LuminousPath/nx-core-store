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
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="DeviceRemote", columns={"remote_id","tracking_device_id"})})
 *
 * @ORM\Entity(repositoryClass="App\Repository\FitStepsIntraDayRepository")
 */
class FitStepsIntraDay
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $DateTime;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $RemoteId;

    /**
     * @ORM\Column(type="integer")
     */
    private $value;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Patient")
     * @ORM\JoinColumn(nullable=false)
     */
    private $patient;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\TrackingDevice")
     * @ORM\JoinColumn(nullable=false)
     */
    private $trackingDevice;

    /**
     * @ORM\Column(type="integer")
     */
    private $hour;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $duration;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->DateTime;
    }

    /**
     * @param \DateTimeInterface $DateTime
     *
     * @return $this
     */
    public function setDateTime(\DateTimeInterface $DateTime): self
    {
        $this->DateTime = $DateTime;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRemoteId(): ?string
    {
        return $this->RemoteId;
    }

    /**
     * @param string|null $RemoteId
     *
     * @return $this
     */
    public function setRemoteId(?string $RemoteId): self
    {
        $this->RemoteId = $RemoteId;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getValue(): ?int
    {
        return $this->value;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setValue(int $value): self
    {
        $this->value = $value;

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
     * @return TrackingDevice|null
     */
    public function getTrackingDevice(): ?TrackingDevice
    {
        return $this->trackingDevice;
    }

    /**
     * @param TrackingDevice|null $trackingDevice
     *
     * @return $this
     */
    public function setTrackingDevice(?TrackingDevice $trackingDevice): self
    {
        $this->trackingDevice = $trackingDevice;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getHour(): ?int
    {
        return $this->hour;
    }

    /**
     * @param int $hour
     *
     * @return $this
     */
    public function setHour(int $hour): self
    {
        $this->hour = $hour;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getDuration(): ?int
    {
        return $this->duration;
    }

    /**
     * @param int|null $duration
     *
     * @return $this
     */
    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }
}
