<?php
/* Copyright (C) 2005-2012	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin		<regis.houssin@capnetworks.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/core/modules/import/modules_import.php
 *	\ingroup    export
 *	\brief      File of parent class for import file readers
 */
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

/**
 *	Parent class for import file readers
 */
class ModeleImports
{
    public $db;
    public $datatoimport;

    public $error='';

    public $id;           // Id of driver
    public $label;        // Label of driver
    public $extension;    // Extension of files imported by driver
    public $version;      // Version of driver

    public $label_lib;    // Label of external lib used by driver
    public $version_lib;  // Version of external lib used by driver

    // Array of all drivers
    public $_driverlabel=array();
    public $_driverdesc=array();
    public $_driverversion=array();

    public $_liblabel=array();
    public $_libversion=array();

    /**
     *  Constructor
     */
    public function __construct()
    {
    }

    /**
     *  Charge en memoire et renvoie la liste des modeles actifs
     *
     *  @param	DoliDB	$db     			Database handler
     *  @param  string	$maxfilenamelength  Max length of value to show
     *  @return	array						List of templates
     */
    public function liste_modeles($db,$maxfilenamelength=0)
    {
        dol_syslog(get_class($this)."::liste_modeles");

        $dir=DOL_DOCUMENT_ROOT."/core/modules/import/";
        $handle=opendir($dir);

        // Recherche des fichiers drivers imports disponibles
        $var=True;
        $i=0;
        if (is_resource($handle)) {
            while (($file = readdir($handle))!==false) {
                if (preg_match("/^import_(.*)\.modules\.php/i",$file,$reg)) {
                    $moduleid=$reg[1];

                    // Chargement de la classe
                    $file = $dir."/import_".$moduleid.".modules.php";
                    $classname = "Import".ucfirst($moduleid);

                    require_once $file;
                    $module = new $classname($db,'');

                    // Picto
                    $this->picto[$module->id]=$module->picto;
                    // Driver properties
                    $this->_driverlabel[$module->id]=$module->getDriverLabel('');
                    $this->_driverdesc[$module->id]=$module->getDriverDesc('');
                    $this->_driverversion[$module->id]=$module->getDriverVersion('');
                    // If use an external lib
                    $this->_liblabel[$module->id]=$module->getLibLabel('');
                    $this->_libversion[$module->id]=$module->getLibVersion('');

                    $i++;
                }
            }
        }

        return array_keys($this->_driverlabel);
    }

    /**
     *  Return picto of import driver
     *
     *	@param	string	$key	Key
     *	@return	string
     */
    public function getPictoForKey($key)
    {
        return $this->picto[$key];
    }

    /**
     *  Renvoi libelle d'un driver import
     *
     *	@param	string	$key	Key
     *	@return	string
     */
    public function getDriverLabelForKey($key)
    {
        return $this->_driverlabel[$key];
    }

    /**
     *  Renvoi la description d'un driver import
     *
     *	@param	string	$key	Key
     *	@return	string
     */
    public function getDriverDescForKey($key)
    {
        return $this->_driverdesc[$key];
    }

    /**
     *  Renvoi version d'un driver import
     *
     *	@param	string	$key	Key
     *	@return	string
     */
    public function getDriverVersionForKey($key)
    {
        return $this->_driverversion[$key];
    }

    /**
     *  Renvoi libelle de librairie externe du driver
     *
     *	@param	string	$key	Key
     *	@return	string
     */
    public function getLibLabelForKey($key)
    {
        return $this->_liblabel[$key];
    }

    /**
     *  Renvoi version de librairie externe du driver
     *
     *	@param	string	$key	Key
     *	@return	string
     */
    public function getLibVersionForKey($key)
    {
        return $this->_libversion[$key];
    }

}
