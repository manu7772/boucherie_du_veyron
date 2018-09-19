<?php
namespace site\adminsiteBundle\services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Labo\Bundle\AdminBundle\services\aeData;
use Labo\Bundle\AdminBundle\services\aeServiceMedia;
use Labo\Bundle\AdminBundle\services\flashMessage;

use site\adminsiteBundle\Entity\image;
use Labo\Bundle\AdminBundle\Entity\media;

use \Exception;

class aeServiceImage extends aeServiceMedia {

    const NAME                  = 'aeServiceImage';				// nom du service
    const CALL_NAME             = 'aetools.aeServiceImage';		// comment appeler le service depuis le controller/container
    const CLASS_ENTITY          = 'site\adminsiteBundle\Entity\image';

	public function __construct(ContainerInterface $container, EntityManager $EntityManager = null) {
	    parent::__construct($container, $EntityManager);
        $this->defineEntity(self::CLASS_ENTITY);
        return $this;
    }



	/**
	 * Check entity integrity in context
	 * @param image $entity
	 * @param string $context ('new', 'PostLoad', 'PrePersist', 'PostPersist', 'PreUpdate', 'PostUpdate', 'PreRemove', 'PostRemove')
	 * @param $eventArgs = null
	 * @return aeServiceImage
	 */
	public function checkIntegrity(&$entity, $context = null, $eventArgs = null) {
		parent::checkIntegrity($entity, $context, $eventArgs);
		// if($entity instanceOf image) {
			// echo('<p>checkIntegrity '.$context.'::'.self::NAME.'/'.json_encode($entity->getNom()).'/'.$this->getEntityShortName($entity).'</p>');
			switch(strtolower($context)) {
				case 'new':
				case 'postload':
					$entity->setCropperInfo($this->container->getParameter('cropperInfo'));
					break;
				case 'prepersist':
					$entity->setCropperInfo($this->container->getParameter('cropperInfo'));
					break;
				case 'postpersist':
					break;
				case 'preupdate':
					$entity->setCropperInfo($this->container->getParameter('cropperInfo'));
					break;
				case 'postupdate':
					break;
				case 'preremove':
					break;
				case 'postremove':
					break;
				case 'preflush':
					// $this->manageImage($entity, $context);
					// $entity->setCropperInfo($this->container->getParameter('cropperInfo'));
					break;
				case 'onflush::updates':
					$this->manageImage($entity, $context);
					break;
				case 'onflush::insertions':
					$this->manageImage($entity, $context);
					break;
				case 'onflush::deletions':
					break;
				default:
					break;
			}
		// }
		return $this;
	}


	////////////////////////////////////////////////////////////////////////////////////////////////////////
	// IMAGE MANAGER
	////////////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Detach image from entity
	 * CALLED BY aeServiceBaseEntity::checkIntegrity() !!
	 * @param object &$entity
	 */
	protected function detachCropperImage(&$entity) {
		$associations = $this->getAssociationNames($entity, self::CLASS_ENTITY);
		// echo('<pre>');var_dump($associations);die('</pre>');
		foreach($associations as $key => $association) {
			$set = $this->getMethodOfSetting($association, $entity);
			$get = $this->getMethodOfGetting($association, $entity);
			$image = $entity->$get();
			if(is_object($image)) {
				$infoForPersist = $image->getInfoForPersist();
				if($infoForPersist !== null) {
					// infoForPersist found
					if((boolean)$infoForPersist['removeImage']) $entity->$set(null);
				}
			}
		}
	}

	/**
	 * image manager
	 * @param object &$entity
	 */
	protected function manageImage(&$entity) {
		// $entity->updateDate();
		if(null == $entity->getUploadFile()) {
			// pass by infoForPersist
			$info = $entity->getInfoForPersist();
			// echo('<pre><h2>infoForPersist image '.json_encode($entity->getNom()).' :</h2>');var_dump($info);echo('</pre>');
			if(isset($info['dataType'])) {
				switch($info['dataType']) {
					case 'cropper':
						// cropper form type
						// rawfile
						if($info['removeImage'] !== true) {
							if(isset($info['rawfiles']['actual'])) {
								$rawfile = $this->getRepo('Labo\Bundle\AdminBundle\Entity\rawfile')->find((integer)$info['rawfiles']['actual'], true);
								if(is_object($rawfile)) {
									$oldrawfile = $entity->getRawfile();
									if($rawfile !== $oldrawfile) {
										if($oldrawfile != null) $this->container->get(aeData::PREFIX_CALL_SERVICE.'aeServiceStatut')->setDeleted($oldrawfile);
										$this->container->get(aeData::PREFIX_CALL_SERVICE.'aeServiceStatut')->setWebmaster($rawfile);
										$entity->setRawfile($rawfile);
									}
								} else {
									throw new Exception('Rawfile with id #'.$info['rawfiles']['actual'].' does not exist.', 1);
								}
							}
							// getData if has rawfile
							if(isset($info['getData']) && is_object($entity->getRawfile())) {
								$oldset = $entity->getCroppingInfo();
								$entity->setCroppingInfo($info['getData']);
								// if(!$entity->setCroppingInfo($info['getData'])) {
									// new croppingInfo -> so manage new image (or new crop on same image)
									if(isset($info['ratioIndex'])) $entity->setRatioIndex($info['ratioIndex']);
										else $entity->setRatioIndex(0);
									if($info['file']['size'] != null) $entity->setFileSize($info['file']['size']);
										else $entity->setFileSize($entity->getRawfile()->getFileSize());
									if($info['file']['type'] != null) {
										$entity->setFormat($info['file']['type']);
										$entity->setMediaType($entity->getTypeOf($info['file']['type']));
									}
									// image file has changed ?
									if($info['file']['name'] != null) {
										$entity->setOriginalnom($info['file']['name']);
										$ext = explode('.', $info['file']['name']);
										$ext = end($ext);
										$authorizedFormatsByImage = $entity->getAuthorizedFormatsByType(image::CLASS_IMAGE);
										if(!in_array($ext, $authorizedFormatsByImage)) $entity->setExtension($entity->getExtByMime($info['file']['type']));
											else $entity->setExtension($ext);
									}
									if(isset($entity->getCropperInfo()['formats'][$entity->getOwnerEntity()][$entity->getOwnerField()][$entity->getRatioIndex()])) {
										$format = $entity->getCropperInfo()['formats'][$entity->getOwnerEntity()][$entity->getOwnerField()][$entity->getRatioIndex()];
									} else {
										$format = $entity->getCropperInfo()['formats']['default'][$entity->getRatioIndex()];
									}
									// so get new cropped image from rawfile
									$aeReponse = $entity->getRawfile()->getCropped($format[0], $format[1], $info);
									$image = $aeReponse->getData();
									if(is_array($image)) {
										if(isset($image['width'])) $entity->setWidth($image['width']);
										if(isset($image['height'])) $entity->setHeight($image['height']);
										if(isset($image['image'])) {
											$this->container->get(aeData::PREFIX_CALL_SERVICE.'aeImagesTools')->setAsString($image['image']);
											$entity->setBinaryFile($image['image']);
										}
									}
									$messageType = $aeReponse->getResult() ? flashMessage::MESSAGES_WARNING : flashMessage::MESSAGES_ERROR;
									if((string)$aeReponse->getMessage() != '')
										$this->container->get('flash_messages')->send(array(
											'title'		=> 'Génération image',
											'type'		=> $messageType,
											'text'		=> '(sadmin) '.$aeReponse->getMessage(),
											'grant'		=> 'ROLE_SUPER_ADMIN',
										));
									if($entity->getNom() == null) $entity->setNom($entity->getOriginalnom());
									$entity->defineNom();
								// } else {
								// 	$this->container->get('flash_messages')->send(array(
								// 		'title'		=> 'Image manager',
								// 		'type'		=> flashMessage::MESSAGES_WARNING,
								// 		'text'		=> '(sadmin) No changes in croppingInfo. No action on image. Oldset : '.json_encode($oldset).' / Newset : '.json_encode($info['getData']).'.',
								// 		'grant'		=> 'ROLE_SUPER_ADMIN',
								// 	));
								// 	$this->getEm()->detach($entity);
								// }
								// confirm stockage
								$entity->setStockage($entity->getStockageList()[0]);
							} else {
								// throw new Exception('Information "getData" does not exist.', 1);
							}
						} else {
							$this->getEm()->remove($entity);
						}
						break;
					default:
						// not supported form type
						throw new Exception('This kind of form "'.$info['dataType'].'" is not supported (…not yet).', 1);
						break;
				}
			} else {
				// not cropper form
				// throw new Exception('No upload file and no infoForPersist information.', 1);
				$this->container->get('flash_messages')->send(array(
					'title'		=> 'Image manager',
					'type'		=> flashMessage::MESSAGES_WARNING,
					'text'		=> '(sadmin) no infoForPersist information : '.json_encode($info),
					'grant'		=> 'ROLE_SUPER_ADMIN',
				));
			}
		} else {
			// uplaod file
			$entity->setInfoForPersist(null);
			$stream = fopen($entity->getUploadFile()->getRealPath(),'rb');
			$entity->setBinaryFile(stream_get_contents($stream));
			fclose($stream);
			$entity->setFileSize(filesize($entity->getUploadFile()->getRealPath()));
			$entity->setOriginalnom($entity->getUploadFile()->getClientOriginalName());
			$entity->setExtension($entity->getUploadFile_extension());
			$entity->setFormat($entity->getUploadFile_typemime());
			$entity->setStockage($entity->getStockageList()[1]);
			if($entity->getNom() == null) $entity->setNom($entity->getOriginalnom());
			$entity->defineNom();
		}

	}





}