<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\WorkoutExerciseRepository")
 */
class WorkoutExercise
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
    private $name;

    /**
     * @ORM\Column(type="text")
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\WorkoutCategories", inversedBy="exercises")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\WorkoutEquipment", inversedBy="exercises")
     */
    private $equipment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\WorkoutMuscle", inversedBy="exercisesPrimary")
     */
    private $musclePrimary;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\WorkoutMuscle", inversedBy="exercisesSecondary")
     */
    private $muscleSecondary;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\ContributionLicense")
     */
    private $license;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\UploadedFile", mappedBy="workoutExercise")
     */
    private $images;

    public function __construct()
    {
        $this->images = new ArrayCollection();
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

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?WorkoutCategories
    {
        return $this->category;
    }

    public function setCategory(?WorkoutCategories $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getEquipment(): ?WorkoutEquipment
    {
        return $this->equipment;
    }

    public function setEquipment(?WorkoutEquipment $equipment): self
    {
        $this->equipment = $equipment;

        return $this;
    }

    public function getMusclePrimary(): ?WorkoutMuscle
    {
        return $this->musclePrimary;
    }

    public function setMusclePrimary(?WorkoutMuscle $musclePrimary): self
    {
        $this->musclePrimary = $musclePrimary;

        return $this;
    }

    public function getMuscleSecondary(): ?WorkoutMuscle
    {
        return $this->muscleSecondary;
    }

    public function setMuscleSecondary(?WorkoutMuscle $muscleSecondary): self
    {
        $this->muscleSecondary = $muscleSecondary;

        return $this;
    }

    public function getLicense(): ?ContributionLicense
    {
        return $this->license;
    }

    public function setLicense(?ContributionLicense $license): self
    {
        $this->license = $license;

        return $this;
    }

    /**
     * @return Collection|UploadedFile[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(UploadedFile $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setWorkoutExercise($this);
        }

        return $this;
    }

    public function removeImage(UploadedFile $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getWorkoutExercise() === $this) {
                $image->setWorkoutExercise(NULL);
            }
        }

        return $this;
    }
}
