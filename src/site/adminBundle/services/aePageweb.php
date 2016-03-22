<?php
namespace site\adminBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceList;
use site\adminBundle\services\aeItem;

use site\adminBundle\Entity\pageweb;
use site\adminBundle\Entity\baseEntity;

class aePageweb extends aeItem {

    const FOLD_PAGEWEB = 'pages_web';

    protected $rootPath;            // Dossier root du site
    protected $bundles_list;
    protected $files_list;

    public function __construct(ContainerInterface $container) {
        parent::__construct($container);
        $this->defineEntity('site\adminBundle\Entity\pageweb');
        $this->rootPath = __DIR__.self::GO_TO_ROOT;
        $this->setRootPath("/");
        // récupération de fichiers et check
        $this->initFiles();
    }

    /**
     * Check entity after change (edit…)
     * @param baseEntity $entity
     * @return aePageweb
     */
    public function checkAfterChange(baseEntity &$entity) {
        parent::checkAfterChange($entity);
        return $this;
    }

    /**
     * Persist and flush a pageweb
     * @param baseEntity $entity
     * @return aeReponse
     */
    // public function save(baseEntity &$entity, $flush = true) {
    //  return parent::save($entity, $flush);
    // }

    // public function getRepository() {
    //     return $this->getRepo();
    // }

    public function getDefaultPage() {
        return $this->getRepo()->findOneByDefault(1);
    }

    protected function initFiles() {
        // initialisation
        $this->bundles_list = array();
        $this->files_list = array();
        // récupération des bundles
        $bundles = $this->getBundles();
        foreach ($bundles as $bundle) {
            $folders[$bundle['sitepath'].$bundle['nom']] = $this->exploreDir($bundle['sitepath'].$bundle['nom'], self::FOLD_PAGEWEB, "dossiers", true);
            foreach ($folders as $key => $folder) if(count($folder) > 0) {
                foreach ($folder as $pw_folder) {
                    $path = $pw_folder['sitepath'].$pw_folder['nom'];
                    $files = $this->exploreDir($path, '\.html\.twig$', true);
                    if(count($files) > 0) foreach ($files as $file) {
                        $name = preg_replace('#\.html\.twig$#i', '', $file['nom']);
                        $this->files_list[$file['sitepath'].$file['nom']] = $name;
                    }
                }
            }
        }
        // echo('<pre>');
        // echo('<h2>bundles</h2>');
        // var_dump($bundles);
        // echo('<h2>folders</h2>');
        // var_dump($folders);
        // echo('<h2>this->files_list</h2>');
        // var_dump($this->files_list);
        // die('</pre>');
        return $this->files_list;
    }

    protected function getBundles() {
        $bundles = $this->exploreDir(self::SOURCE_FILES, self::BUNDLE_EXTENSION.'$', "dossiers");
        foreach ($bundles as $key => $bundle) {
            $bundles[$key]['bundlename'] = str_replace('/', '', (preg_replace('#^'.self::SOURCE_FILES.'#', '', $bundle['sitepath']))).$bundle['nom'];
        }
        return $bundles;
    }

    public function getModels() {
        return $this->files_list;
    }

    public function getPagewebChoices() {
        $modelsListKeys = array_keys($this->files_list);
        $modelsList = array_values($this->files_list);
        return new ChoiceList($modelsListKeys, $modelsList);
    }

}