<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(uniqueConstraints={@ORM\UniqueConstraint(name="DeviceRemote", columns={"remote_id","tracking_device_id"})})
 *
 * @ORM\Entity(repositoryClass="App\Repository\FitCaloriesDailySummaryRepository")
 */
class FitCaloriesDailySummary
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
     * @ORM\Column(type="float")
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
     * @ORM\ManyToOne(targetEntity="App\Entity\PatientGoals")
     * @ORM\JoinColumn(nullable=false)
     */
    private $patientGoal;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateTime(): ?\DateTimeInterface
    {
        return $this->DateTime;
    }

    public function setDateTime(\DateTimeInterface $DateTime): self
    {
        $this->DateTime = $DateTime;

        return $this;
    }

    public function getRemoteId(): ?string
    {
        return $this->RemoteId;
    }

    public function setRemoteId(?string $RemoteId): self
    {
        $this->RemoteId = $RemoteId;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setValue(float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getPatient(): ?Patient
    {
        return $this->patient;
    }

    public function setPatient(?Patient $patient): self
    {
        $this->patient = $patient;

        return $this;
    }

    public function getTrackingDevice(): ?TrackingDevice
    {
        return $this->trackingDevice;
    }

    public function setTrackingDevice(?TrackingDevice $trackingDevice): self
    {
        $this->trackingDevice = $trackingDevice;

        return $this;
    }

    public function getGoal(): ?PatientGoals
    {
        return $this->patientGoal;
    }

    public function setGoal(?PatientGoals $patientGoal): self
    {
        $this->patientGoal = $patientGoal;

        return $this;
    }

    public function getPatientGoal(): ?PatientGoals
    {
        return $this->patientGoal;
    }

    public function setPatientGoal(?PatientGoals $patientGoal): self
    {
        $this->patientGoal = $patientGoal;

        return $this;
    }
}
