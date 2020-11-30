<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActividadProtocolo
 *
 * @ORM\Table(name="actividad_protocolo", indexes={@ORM\Index(name="fk_actividad", columns={"actividad_id"}), @ORM\Index(name="protocolo_id", columns={"protocolo_id"})})
 * @ORM\Entity
 */
class ActividadProtocolo
{
    /**
     * @var int
     *
     * @ORM\Column(name="actividad_procotolo_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $actividadProcotoloId;

    /**
     * @var \Protocolo
     *
     * @ORM\ManyToOne(targetEntity="Protocolo")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="protocolo_id", referencedColumnName="protocolo_id")
     * })
     */
    private $protocolo;

    /**
     * @var \Actividad
     *
     * @ORM\ManyToOne(targetEntity="Actividad")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="actividad_id", referencedColumnName="actividad_id")
     * })
     */
    private $actividad;
    
    public function __construct($protocolo,$actividad)
    {
      $this->protocolo = $protocolo;
      $this->actividad = $actividad;
    }

    public function getActividadProcotoloId(): ?int
    {
        return $this->actividadProcotoloId;
    }

    public function getProtocolo(): ?Protocolo
    {
        return $this->protocolo;
    }

    public function setProtocolo(?Protocolo $protocolo): self
    {
        $this->protocolo = $protocolo;

        return $this;
    }

    public function getActividad(): ?Actividad
    {
        return $this->actividad;
    }

    public function setActividad(?Actividad $actividad): self
    {
        $this->actividad = $actividad;

        return $this;
    }


}
