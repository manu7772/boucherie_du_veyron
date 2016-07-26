<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use site\adminBundle\services\aeItem;

use site\adminBundle\Entity\pageweb;
use site\adminBundle\Entity\baseEntity;

class aePageweb extends aeItem {


    protected $rootPath;            // Dossier root du site
    protected $bundles_list;
    protected $files_bas_list;
    protected $files_ext_list;

    const NAME                  = 'aePageweb';        // nom du service
    const CALL_NAME             = 'aetools.aePageweb'; // comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminBundle\Entity\pageweb';
    const FOLD_BAS_PAGEWEB      = 'basic_pages_web';
    const FOLD_EXT_PAGEWEB      = 'extended_pages_web';

    public function __construct(ContainerInterface $container = null, $em = null) {
        parent::__construct($container, $em);
        $this->defineEntity(self::CLASS_ENTITY);
        $this->rootPath = __DIR__.self::GO_TO_ROOT;
        $this->setRootPath("/");
        $this->bundles_list = null;
        // récupération de fichiers et check
        $this->initFiles();
        return $this;
    }

    public function getNom() {
        return self::NAME;
    }

    public function callName() {
        return self::CALL_NAME;
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aeArticle
     */
    public function checkAfterChange(&$entity, $butEntities = []) {
        parent::checkAfterChange($entity, $butEntities);
        return $this;
    }

    public function getDefaultPage() {
        return $this->getRepo()->findOneByDefault(1);
    }

    protected function initFiles() {
        // initialisation
        $this->files_bas_list = array();
        $this->files_ext_list = array();
        // récupération des bundles
        foreach($this->getBundles() as $bundle) {
            // basic
            $folders = $this->exploreDir($bundle['sitepath'].$bundle['nom'], self::FOLD_BAS_PAGEWEB, "dossiers", true);
            if(count($folders) > 0) {
                foreach($folders as $pw_folder) {
                    $path = $pw_folder['sitepath'].$pw_folder['nom'];
                    $files = $this->exploreDir($path, '\.html\.twig$', true);
                    if(count($files) > 0) foreach ($files as $file) {
                        $this->files_bas_list[$file['sitepath'].$file['nom']] = preg_replace('#\.html\.twig$#i', '', $file['nom']);
                    }
                }
            }
            // extended
            $folders = $this->exploreDir($bundle['sitepath'].$bundle['nom'], self::FOLD_EXT_PAGEWEB, "dossiers", true);
            if(count($folders) > 0) {
                foreach($folders as $pw_folder) {
                    $path = $pw_folder['sitepath'].$pw_folder['nom'];
                    $files = $this->exploreDir($path, '\.html\.twig$', true);
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
            $this->bundles_list = $this->exploreDir(self::SOURCE_FILES, self::BUNDLE_EXTENSION.'$', "dossiers");
            foreach($this->bundles_list as $key => $bundle) {
                $this->bundles_list[$key]['bundlename'] = str_replace('/', '', (preg_replace('#^'.self::SOURCE_FILES.'#', '', $bundle['sitepath']))).$bundle['nom'];
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

}