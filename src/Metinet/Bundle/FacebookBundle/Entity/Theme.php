<?php

    namespace Metinet\Bundle\FacebookBundle\Entity;

    use Doctrine\ORM\Mapping as ORM;
    use Symfony\Component\Validator\Constraints as Assert;
    use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Theme
 *
 * @ORM\Table(name="theme")
 * @ORM\Entity(repositoryClass="Metinet\Bundle\FacebookBundle\Entity\Repository\ThemeRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Theme
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="picture", type="string", length=255)
     * @Assert\File(maxSize="6000000")
     */
    private $picture;

    /**
     * @var string
     *
     * @ORM\Column(name="short_desc", type="string", length=255, nullable=true)
     */
    private $shortDesc;

    /**
     * @var string
     *
     * @ORM\Column(name="long_desc", type="text", nullable=true)
     */
    private $longDesc;

    /**
     * @ORM\OneToMany(targetEntity="Quizz", mappedBy="theme", cascade={"remove", "persist"})
     */
    protected $quizzes;

    protected $path;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->quizzes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Theme
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set picture
     *
     * @param string $picture
     * @return Theme
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture
     *
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * Set shortDesc
     *
     * @param string $shortDesc
     * @return Theme
     */
    public function setShortDesc($shortDesc)
    {
        $this->shortDesc = $shortDesc;

        return $this;
    }

    /**
     * Get shortDesc
     *
     * @return string
     */
    public function getShortDesc()
    {
        return $this->shortDesc;
    }

    /**
     * Set longDesc
     *
     * @param string $longDesc
     * @return Theme
     */
    public function setLongDesc($longDesc)
    {
        $this->longDesc = $longDesc;

        return $this;
    }

    /**
     * Get longDesc
     *
     * @return string
     */
    public function getLongDesc()
    {
        return $this->longDesc;
    }

     /**
     * Set path
     *
     * @param string $path
     * @return Theme
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Add quizzes
     *
     * @param \Metinet\Bundle\FacebookBundle\Entity\Quizz $quizzes
     * @return Theme
     */
    public function addQuizze(\Metinet\Bundle\FacebookBundle\Entity\Quizz $quizzes)
    {
        $this->quizzes[] = $quizzes;

        return $this;
    }

    /**
     * Remove quizzes
     *
     * @param \Metinet\Bundle\FacebookBundle\Entity\Quizz $quizzes
     */
    public function removeQuizze(\Metinet\Bundle\FacebookBundle\Entity\Quizz $quizzes)
    {
        $this->quizzes->removeElement($quizzes);
    }

    /**
     * Get quizzes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getQuizzes()
    {
        return $this->quizzes;
    }
    
    
    public function getAbsolutePath()
    {
        return null === $this->path ? null : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path ? null : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // le chemin absolu du répertoire où les documents uploadés doivent être sauvegardés
        return __DIR__.'/../Resources/public/images/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        // on se débarrasse de « __DIR__ » afin de ne pas avoir de problème lorsqu'on affiche
        // le document/image dans la vue.
        return 'uploads/theme/img';
    }
    
 /**
     * @ORM\PrePersist()
     */
    public function preUpload()
    {
        if (null !== $this->picture) {
            // faites ce que vous voulez pour générer un nom unique
            $this->path = "bundles/metinetfacebook/images/uploads/theme/img/".''.sha1(uniqid(mt_rand(), true)).'.'.$this->picture->guessExtension();
            return $this->path ;
        }
    }

    /**
     * @ORM\PostPersist()
     */
    public function upload()
    {
        if (null === $this->picture) {
            return;
        }

        // s'il y a une erreur lors du déplacement du fichier, une exception
        // va automatiquement être lancée par la méthode move(). Cela va empêcher
        // proprement l'entité d'être persistée dans la base de données si
        // erreur il y a
        $this->picture->move($this->getUploadRootDir(), $this->path);            
            
        unset($this->picture);
        
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        if ($file = $this->getAbsolutePath()) {
            unset($file);
        }
    }
    
    public function __toString() {
    return $this->title;
}
    

}