<?php
namespace site\services;

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
        return $Rimage;
    }

}