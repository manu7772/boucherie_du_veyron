<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;

use site\adminBundle\Entity\article;
use site\UserBundle\Entity\User;
use site\adminBundle\Entity\panier;

use site\adminBundle\services\aeReponse;

class aePanier {

    protected $container;
    protected $_em;
    protected $_repo;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->_em = $this->container->get('doctrine')->getManager();
        $this->_repo = $this->_em->getRepository('site\adminBundle\Entity\panier');
    }

    /**
     * messageNonConnecte
     * @return string
     */
    protected function messageNonConnecte() {
        $login = '<a href="'.$this->router->generate('fos_user_security_login').'"><button type="button" class="btn btn-warning">LOGIN</button></a>';
        $regis = '<a href="'.$this->router->generate('fos_user_registration_register').'"><button type="button" class="btn btn-danger">Créer mon compte</button></a>';
        return "Vous devez vous connecter à votre compte pour acheter en ligne.<br />Si vous n'en avez pas, vous pouvez créer un compte facilement.<br /><br />".$login."&nbsp;".$regis;
    }

    /**
     * Check panier after change (edit…)
     * @param panier $panier
     * 
     */
    public function checkAfterChange($panier) {
        $this->container->get('aetools.aeEntity')->checkInversedLinks($panier, false);
    }

    /**
     * ajouteArticle
     * @param article $article
     * @param User $user
     * @param integer $quantite
     * @return aeReponse
     */
    public function ajouteArticle(article $article, $user = null, $quantite = 1) {
        // if($user === null) $user = $this->container->getUser();
        if(is_object($user)) {
            $art = $this->_repo->getOneArticleOfUser($article->getId(), $user->getId());
            if($art === null) {
                // article non présent dans le panier
                $art = new panier();
                $art->setUser($user);
                $art->setArticle($article);
                $art->setQuantite($quantite);
                $this->_em->persist($art);
                $r = new aeReponse(true, null, "L'article ".$article->getNom()." a été ajouté au panier.");
            } else {
                // article déjà présent au moins 1 fois
                $art->ajouteQuantite($quantite);
                $r = new aeReponse(true, null, null);
            }
            $this->_em->flush();
            return $r;
            // return new aeReponse(false, null, "L'article n'a pu être ajouté.");
        } else {
            // generate
            return new aeReponse(false, null, $this->messageNonConnecte());
        }
    }

    /**
     * reduitArticle
     * @param article $article
     * @param User $user
     * @param integer $quantite
     * @return aeReponse
     */
    public function reduitArticle(article $article, $user = null, $quantite = 1) {
        // if($user === null) $user = $this->container->getUser();
        if(is_object($user)) {
            $art = $this->_repo->getOneArticleOfUser($article->getId(), $user->getId());
            if($art === null) {
                // article non présent dans le panier
                return new aeReponse(false, null, "L'article ".$article->getNom()." n'existe pas dans le panier.");
            } else {
                // article déjà présent au moins 1 fois
                $art->retireQuantite($quantite);
                if($art->getQuantite() < 1) {
                    // plus d'articles…
                    $this->_em->remove($art);
                    $r = new aeReponse(true, null, "L'article ".$article->getNom()." a été supprimé du panier.");
                } else {
                    // reste encore un ou des articles…
                    $r = new aeReponse(true, null, null);
                }
            }
            $this->_em->flush();
            return $r;
            // return new aeReponse(false, null, "L'article n'a pu être ajouté.");
        } else {
            // generate
            return new aeReponse(false, null, $this->messageNonConnecte());
        }
    }

    /**
     * SupprimeArticle
     * @param article $article
     * @param User $user
     * @return aeReponse
     */
    public function SupprimeArticle(article $article, $user = null) {
        // if($user === null) $user = $this->container->getUser();
        if(is_object($user)) {
            $art = $this->_repo->getOneArticleOfUser($article->getId(), $user->getId());
            if($art === null) {
                // article non présent dans le panier
                return new aeReponse(false, null, "L'article ".$article->getNom()." n'existe pas dans le panier.");
            } else {
                // article présent
                $this->_em->remove($art);
                $this->_em->flush();
            }
            return new aeReponse(true, null, "L'article ".$article->getNom()." a été supprimé du panier.");
            // return new aeReponse(false, null, "L'article n'a pu être ajouté.");
        } else {
            // generate
            return new aeReponse(false, null, $this->messageNonConnecte());
        }
    }

    /**
     * Vide le panier de l'utilisateur $user (courant si non précisé)
     * @param User $user
     * @return aeReponse
     */
    public function videPanier($user = null) {
        // if($user === null) $user = $this->container->getUser();
        if(is_object($user)) {
            $art = $this->_repo->getUserArticles($user->getId());
            if(count($art) < 1) {
                // Le panier est déjà vide
                $r = new aeReponse(true, null, "Le panier est déjà vide.");
            } else {
                // Le panier contient au moins 1 article
                foreach($art as $artsupp) $this->_em->remove($artsupp);
                $this->_em->flush();
                $r = new aeReponse(true, $art, "Le panier a été vidé.");
            }
            return $r;
            // return new aeReponse(false, null, "L'article n'a pu être ajouté.");
        } else {
            // generate
            return new aeReponse(false, null, $this->messageNonConnecte());
        }
    }

    /**
     * getArticlesOfUser
     * @param User $user
     * @return array of article
     */
    public function getArticlesOfUser($user = null) {
        // if($user === null) $user = $this->container->getUser();
        if(is_object($user)) {
            // if($user === null) $user = $this->container->getUser();
            return $this->_repo->getUserArticles($user->getId());
        } else return null;
    }

    /**
     * getInfosPanier
     * Renvoie le nombre d'articles dans un array :
     * -> "bytype" = nombre d'articles différents
     * -> "total" = nombre total d'articles
     * @param User $user
     * @return array
     */
    public function getInfosPanier($user = null) {
        // if($user === null) $user = $this->container->getUser();
        if(is_object($user)) {
            // if($user === null) $user = $this->container->getUser();
            $articles = $this->_repo->getUserPanier($user->getId());
            $infopanier["total"] = 0;
            $infopanier["prixtotal"] = 0;
            foreach($articles as $art) {
                $infopanier["total"] += $art->getQuantite();
                $infopanier["prixtotal"] += $art->getPrixtotal();
            }
            $infopanier["bytype"] = count($articles);
            return $infopanier;
        } else return null;
    }


}