<?php
namespace site\adminsiteBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use Doctrine\ORM\EntityManager;
use Labo\Bundle\AdminBundle\services\aeData;
// use Doctrine\ORM\Event\PreUpdateEventArgs;
// use Doctrine\ORM\Event\LifecycleEventArgs;

use Labo\Bundle\AdminBundle\services\aeServiceItem;

use site\adminsiteBundle\Entity\pageweb;
use Labo\Bundle\AdminBundle\Entity\baseEntity;

class aeServicePageweb extends aeServiceItem {


    protected $rootPath;            // Dossier root du site
    protected $bundles_list;
    protected $files_bas_list;
    protected $files_ext_list;
    // services
    protected $aeSystemfiles;

    const NAME                  = 'aeServicePageweb';        // nom du service
    const CALL_NAME             = 'aetools.aeServicePageweb'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminsiteBundle\Entity\pageweb';
    const FOLD_BAS_PAGEWEB      = 'basic_pages_web';
    const FOLD_EXT_PAGEWEB      = 'extended_pages_web';

    public function __construct(ContainerInterface $container, EntityManager $EntityManager = null) {
        parent::__construct($container, $EntityManager);
        $this->defineEntity(self::CLASS_ENTITY);
        $this->aeSystemfiles = $this->container->get(aeData::PREFIX_CALL_SERVICE.'aeSystemfiles');
        $this->aeSystemfiles->setRootPath(aeData::SLASH);
        $this->bundles_list = null;
        // récupération de fichiers et check
        $this->initFiles();
        return $this;
    }

    // /**
    //  * Check entity integrity in context
    //  * @param pageweb $entity
    //  * @param string $context ('new', 'PostLoad', 'PrePersist', 'PostPersist', 'PreUpdate', 'PostUpdate', 'PreRemove', 'PostRemove')
    //  * @param $eventArgs = null
    //  * @return aeServicePageweb
    //  */
    // public function checkIntegrity(&$entity, $context = null, $eventArgs = null) {
    //     parent::checkIntegrity($entity, $context, $eventArgs);
    //     // if($entity instanceOf pageweb) {
    //         switch(strtolower($context)) {
    //             case 'new':
    //                 break;
    //             case 'postload':
    //                 break;
    //             case 'prepersist':
    //                 break;
    //             case 'postpersist':
    //                 break;
    //             case 'preupdate':
    //                 break;
    //             case 'postupdate':
    //                 break;
    //             case 'preremove':
    //                 break;
    //             case 'postremove':
    //                 break;
    //             default:
    //                 break;
    //         }
    //     // }
    //     return $this;
    // }


    public function getDefaultPage() {
        // $this->container->get(aeData::PREFIX_CALL_SERVICE.'aeDebug')->startChrono();
        $page = $this->getRepo()->findDefaultPage();
        if(is_array($page)) $page = reset($page);
        $this->completePageweb($page);
        // $this->container->get(aeData::PREFIX_CALL_SERVICE.'aeDebug')->printChrono('Get default pagweb', true);
        return $page;
    }

    public function getPageBySlug($itemSlug) {
        // $this->container->get(aeData::PREFIX_CALL_SERVICE.'aeDebug')->startChrono();
        $page = $this->getRepo()->getPageBySlug($itemSlug);
        if(is_array($page)) $page = reset($page);
        $this->completePageweb($page);
        // $this->container->get(aeData::PREFIX_CALL_SERVICE.'aeDebug')->printChrono('Get "'.$itemSlug.'" pagweb', true);
        return $page;
    }

    protected function initFiles() {
        // initialisation
        $this->files_bas_list = array();
        $this->files_ext_list = array();
        // récupération des bundles
        foreach($this->getBundles() as $bundle) {
            // basic
            $folders = $this->aeSystemfiles->exploreDir($bundle['sitepath'].$bundle['nom'], self::FOLD_BAS_PAGEWEB, "dossiers", true);
            if(count($folders) > 0) {
                foreach($folders as $pw_folder) {
                    $path = $pw_folder['sitepath'].$pw_folder['nom'];
                    $files = $this->aeSystemfiles->exploreDir($path, '\.html\.twig$', true);
                    if(count($files) > 0) foreach ($files as $file) {
                        $this->files_bas_list[$file['sitepath'].$file['nom']] = preg_replace('#\.html\.twig$#i', '', $file['nom']);
                    }
                }
            }
            // extended
            $folders = $this->aeSystemfiles->exploreDir($bundle['sitepath'].$bundle['nom'], self::FOLD_EXT_PAGEWEB, "dossiers", true);
            if(count($folders) > 0) {
                foreach($folders as $pw_folder) {
                    $path = $pw_folder['sitepath'].$pw_folder['nom'];
                    $files = $this->aeSystemfiles->exploreDir($path, '\.html\.twig$', true);
                    if(count($files) > 0) foreach ($files as $file) {
                        $this->files_ext_list[$file['sitepath'].$file['nom']] = preg_replace('#\.html\.twig$#i', '', $file['nom']);
                    }
                }
            }
        }
        return $this;
    }

    protected function getBundles() {
        if($this->bundles_list == null) {
            $this->bundles_list = $this->aeSystemfiles->exploreDir(aeData::SOURCE_FILES, aeData::BUNDLE_EXTENSION.'$', "dossiers");
            foreach($this->bundles_list as $key => $bundle) {
                $this->bundles_list[$key]['bundlename'] = str_replace('/', '', (preg_replace('#^'.aeData::SOURCE_FILES.'#', '', $bundle['sitepath']))).$bundle['nom'];
            }
        }
        return $this->bundles_list;
    }

    public function getModels($extended = false) {
        return (boolean)$extended ? array_merge($this->files_ext_list, $this->files_bas_list) : $this->files_bas_list ;
    }

    public function getPagewebChoices($extended = false) {
        $models = $this->getModels((boolean)$extended);
        return new ChoiceList(array_keys($models), array_values($models));
    }

    public function completePageweb(&$pageweb) {
        if(is_object($pageweb)) return $pageweb;
        if(!is_array($pageweb)) return $pageweb;
        // template
        $path = preg_split('#(src/|Resources/|views/|/)#', $pageweb['pageweb']["modele"]);
        $pageweb['pageweb']["template"] = implode(array_slice($path, 0, -2)).':'.$path[count($path)-2].':'.$path[count($path)-1];
        // model name
        $path = explode("/", $pageweb['pageweb']["modele"]);
        $pageweb['pageweb']["modelename"] = preg_replace("#\.html\.twig$#", '', end($path));
        // intégration id liés
        foreach ($pageweb as $key => $value) if($key != 'pageweb') {
            $pageweb['pageweb']['#'.$key] = $value;
        }
        $pageweb = $pageweb['pageweb'];
        // result
        return $pageweb;
    }
}