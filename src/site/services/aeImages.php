<?php
namespace site\services;

use site\services\aeReponse;

class aeImages {

    /**
    * thumb_image
    * Crée et enregistre un tumbnail de l'image
    * @param image $image
    * @param integer $Xsize = null
    * @param integer $Ysize = null
    * @param string $mode = "no"
    * @return image
    */
    public function thumb_image($image, $Xsize = null, $Ysize = null, $mode = "no") {
        // $mode =
        // cut      : remplit le format avec l'image et la coupe si besoin
        // in       : inclut l'image pour qu'elle soit entièrerement visible
        // deform   : déforme l'image pour qu'elle soit exactement à la taille
        // no       : ne modifie pas la taille de l'image
        // calcul…
        set_time_limit(120);

        // if Raw data (string)
        if(is_string($image)) $image = imagecreatefromstring($image);

        $x = imagesx($image);
        $y = imagesy($image);
        $ratio = $x / $y;

        if($Xsize == null && $Ysize == null) {
            $Xsize = $x;
            $Ysize = $y;
        }
        if($Xsize == null) $Xsize = $Ysize * $ratio;
        if($Ysize == null) $Ysize = $Xsize / $ratio;

        $Dratio = $Xsize / $Ysize;

        // echo('<p>BEGIN : Size X : '.$Xsize.' px</p>');
        // echo('<p>BEGIN : Size X : '.$Ysize.' px</p>');

        if(($x != $Xsize) || ($y != $Ysize)) {
            switch($mode) {
                case('deform') :
                    $nx = $Xsize;
                    $ny = $Ysize;
                    $posx = $posy = 0;
                break;
                case('cut') :
                    if($ratio > $Dratio) {
                        $posx = ($x - ($y * $Dratio)) / 2;
                        $posy = 0;
                        $x = $y * $Dratio;
                    } else {
                        $posx = 0;
                        $posy = ($y - ($x / $Dratio)) / 2;
                        $y = $x / $Dratio;
                    }
                    $nx = $Xsize;
                    $ny = $Ysize;
                break;
                case('in') :
                    if($x > $Xsize || $y > $Xsize) {
                        if($x > $y) {
                            $nx = $Xsize;
                            $ny = $y/($x/$Xsize);
                        } else {
                            $nx = $x/($y/$Xsize);
                            $ny = $Xsize;
                        }
                    } else {
                        $nx = $x;
                        $ny = $y;
                    }
                    $posx = $posy = 0;
                break;
                default: // "no" et autres…
                    $posx = $posy = 0;
                    $nx = $x;
                    $ny = $y;
                break;
            }
            $Rimage = imagecreatetruecolor($nx, $ny);
            imagealphablending($Rimage, false);
            imagesavealpha($Rimage, true);
            imagecopyresampled($Rimage, $image, 0, 0, $posx, $posy, $nx, $ny, $x, $y);
        } else {
            $Rimage = imagecreatetruecolor($x, $y);
            imagealphablending($Rimage, false);
            imagesavealpha($Rimage, true);
            imagecopy($Rimage, $image, 0, 0, 0, 0, $x, $y);
        }
        imagedestroy($image);

        // $x = imagesx($Rimage);
        // $y = imagesy($Rimage);
        // echo('<p>END : Size X : '.$x.' px</p>');
        // echo('<p>END : Size X : '.$y.' px</p>');

        return $Rimage;
    }

    public function getCropped($image, $w, $h, $x, $y, $width, $height, $rotate = 0) {
        set_time_limit(120);
        ini_set('memory_limit', '512M');
        $reponse = new aeReponse();
        // echo('<p>- w : '.$w.'<br>'); 
        // echo('- h : '.$h.'<br>');
        // echo('- x : '.$x.'<br>');
        // echo('- y : '.$y.'<br>');
        // echo('- width : '.$width.'<br>');
        // echo('- height : '.$height.'<br>');
        // echo('- rotate : '.$rotate.'°</p>');
        if(is_string($image)) $image = imagecreatefromstring($image);
        if($rotate != 0) imagerotate($image, $rotate, 0, 0);
        $Rimage = imagecreatetruecolor($w, $h);
        imagealphablending($Rimage, false);
        imagesavealpha($Rimage, true);
        $reponse->setResult(imagecopyresampled($Rimage, $image, 0, 0, $x, $y, $w, $h, $width, $height));
        if($reponse->getResult() == true) {
            // OK
            $message = 'Génération de l\'image réussie. ';
            if($width < $x || $height < $y) $message .= 'Attention, l\'image a été agrandie. Sa résolution ne sera suffisante pour une qualité d\'affichage optimale.';
            $reponse->setMessage($message);
            $reponse->setData($Rimage);
        } else {
            // ERROR
            $reponse->setMessage('Une erreur s\'est produite pendant la génération. Veuillez recommencer l\'opération.');
        }
        return $reponse;
    }

}









