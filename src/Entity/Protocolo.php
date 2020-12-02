<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Protocolo
 *
 * @ORM\Table(name="protocolo", indexes={@ORM\Index(name="protocolo_proyecto", columns={"proyecto_id"}), @ORM\Index(name="responsable_id", columns={"responsable_id"})})
 * @ORM\Entity
 */
class Protocolo
{
    /**
     * @var int
     *
     * @ORM\Column(name="protocolo_id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $protocoloId;

    /**
     * @var string
     *
     * @ORM\Column(name="nombre", type="string", length=100, nullable=false)
     */
    private $nombre;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="fecha_inicio", type="datetime", nullable=true)
     */
    private $fechaInicio;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="fecha_fin", type="datetime", nullable=true)
     */
    private $fechaFin;

    /**
     * @var int
     *
     * @ORM\Column(name="orden", type="integer", nullable=false)
     */
    private $orden;

    /**
     * @var string
     *
     * @ORM\Column(name="es_local", type="string", length=1, nullable=false)
     */
    private $esLocal;

    /**
     * @var int|null
     *
     * @ORM\Column(name="puntaje", type="integer", nullable=true)
     */
    private $puntaje;

    /**
     * @var \User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="responsable_id", referencedColumnName="id")
     * })
     */
    private $responsable;

    /**
     * @var \Proyecto
     *
     * @ORM\ManyToOne(targetEntity="Proyecto")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="proyecto_id", referencedColumnName="proyecto_id")
     * })
     */
    private $proyecto;

    private $ejecutable;

    public function __construct($nombre,$local)
    {
      $this->nombre = $nombre;
      $this->esLocal = $local;
    }

    public function getProtocoloId(): ?int
    {
        return $this->protocoloId;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getFechaInicio(): ?\DateTimeInterface
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio(?\DateTimeInterface $fechaInicio): self
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    public function getOrden(): ?int
    {
        return $this->orden;
    }

    public function setOrden(int $orden): self
    {
        $this->orden = $orden;

        return $this;
    }

    public function getEsLocal(): ?string
    {
        return $this->esLocal;
    }

    public function setEsLocal(string $esLocal): self
    {
        $this->esLocal = $esLocal;

        return $this;
    }

    public function getPuntaje(): ?int
    {
        return $this->puntaje;
    }

    public function setPuntaje(?int $puntaje): self
    {
        $this->puntaje = $puntaje;

        return $this;
    }

    public function getResponsable(): ?User
    {
        return $this->responsable;
    }

    public function setResponsable(?User $responsable): self
    {
        $this->responsable = $responsable;

        return $this;
    }

    public function getProyecto(): ?Proyecto
    {
        return $this->proyecto;
    }

    public function setProyecto(?Proyecto $proyecto): self
    {
        $this->proyecto = $proyecto;

        return $this;
    }
    public function getEjecutable(): ?string
    {
        return $this->ejecutable;
    }

    public function setEjecutable(?string $ejecutable): self
    {
        $this->ejecutable = $ejecutable;
        return $this;
    }




}
