<?php

namespace site\adminsiteBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;

use Labo\Bundle\AdminBundle\Entity\facture as baseFacture;

/**
 * facture
 *
 * @ORM\Entity(repositoryClass="site\adminsiteBundle\Entity\factureRepository")
 * @ORM\Table(name="facture", options={"comment":"factures clients"})
 * @UniqueEntity(fields={"id"}, message="Ce numéro de facture existe déjà")
 * @ORM\HasLifecycleCallbacks
 */
class facture extends baseFacture {



}