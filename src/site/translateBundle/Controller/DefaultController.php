<?php

namespace site\translateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller {

    const MOTIF_FORM = '93736254F_form_data_';

    /**
     *
     */
    public function showtradAction() {
        $data = array();
        // tools
        $aeTrans = $this->get('aetools.translate');

        // langues
        $data['langues']['list'] = $aeTrans->getLanguages();
        $data['langues']['bundles'] = $aeTrans->getLanguagesByBundles();
        $data['langues']['default'] = $aeTrans->getDefaultLocale();

        // fichiers
        $data['transfiles'] = $aeTrans->getFilesInBundles();

        return $this->render('sitetranslateBundle:Default:index.html.twig', $data);
    }


    /**
     *
     */
    public function mergetradAction($language, $bundle, $domain, $current = null) {
        $aeTrans = $this->get('aetools.translate');
        $languages = $aeTrans->getLanguages();
        if(is_string($current)) $current2 = array($current);
        if($current == null) $current2 = $languages;
        foreach($current2 as $lang) if(in_array($lang, $languages)) {
            $insert = $aeTrans->insertSchemaType($bundle, $domain, $lang);
        }
        // message
        $msg = $this->get('aetools.aeMessages');
        if($insert !== false)
            $msg->addFlashMessage('traduction.merge.done', array('traduction.merge.with', "%language%" => $language), 'info');
        else
            $msg->addFlashMessage('error.name', 'error.operation', 'error', 'exceptions');
        // redirection
        if($current == null) {
            return $this->redirect($this->generateUrl('sitetranslate_homepage'));
        } else {
            return $this->redirect($this->generateUrl('edit_traduction', array("bundle" => $bundle, "domain" => $domain, "language" => $current)));
        }
    }


    /**
     *
     */
    public function editradAction($bundle, $domain, $language) {
        $data = array();
        $aeTrans = $this->get('aetools.translate');
        $data['bundle'] = $bundle;
        $data['domain'] = $domain;
        $data['language'] = $language;
        // motif pour form
        $data['motif_form'] = self::MOTIF_FORM;
        // langues
        $data['langues']['list'] = $aeTrans->getLanguages();
        if(!in_array($language, $data['langues']['list'])) {
            // langage inconnu
            return $this->redirect($this->generateUrl('sitetranslate_homepage'));
        }
        $data['langues']['bundles'] = $aeTrans->getLanguagesByBundles();
        $data['langues']['default'] = $aeTrans->getDefaultLocale();
        // checke chaque langue
        foreach ($data['langues']['list'] as $key => $value) {
            $data['check'][$value] = $aeTrans->check_yaml($bundle, $domain, $value);
            $data['otherfiles'][$value] = $aeTrans->getSingleArray($bundle, $domain, $value);
        }
        // fichier parsé
        if($data['check'][$language] == false) {
            // synchronise le fichier
        }
        // $data['fileinfo'] = $aeTrans->getFile($bundle, $domain, $language);
        $data['filedata'] = $aeTrans->getSingleArray($bundle, $domain, $language);
        $data['fullpath'] = $aeTrans->getFullPathBundle($bundle);
        // echo('<pre>');
        // var_dump($data['filedata']);
        // echo('</pre>');
        return $this->render('sitetranslateBundle:actions:edit.html.twig', $data);
    }

    /**
     *
     */
    public function changeAction() {
        $aeTrans = $this->get('aetools.translate');

        $all = $this->getRequest()->request->all();
        $bundle = $all['bundle'];
        $domain = $all['domain'];
        $language = $all['language'];

        $file = array();
        foreach ($all as $key => $value) if(preg_match('#^'.self::MOTIF_FORM.'#', $key)) {
            $item = preg_replace('#^'.self::MOTIF_FORM.'#', '', $key);
            $file[$item] = $value;
        }
        $result = $aeTrans->saveSingleArray($bundle, $domain, $language, $file);
        // $newvalue = $request->request->get('newvalue');
        // $result = $aeTrans->changeValue($bundle, $domain, $language, $field, $newvalue);
        if($result == false) {
            $msg = $this->get('aetools.aeMessages');
            $msg->addFlashMessage('Modification', 'Erreur lors de l\'enregistrement.', 'error');
        }
        // echo('<pre>ALL<br>');
        // var_dump($all);
        // echo('</pre>');
        // return new Response('ok');
        return $this->redirect($this->generateUrl('edit_traduction', array("bundle" => $bundle, "domain" => $domain, "language" => $language)));
    }

}