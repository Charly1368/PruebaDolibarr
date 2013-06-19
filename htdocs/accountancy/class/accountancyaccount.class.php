<?php
/* Copyright (C) 2006-2009 Laurent Destailleur   <eldy@users.sourceforge.net>
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
 */

/**
 *	\file       htdocs/accountancy/class/accountancyaccount.class.php
 * 	\ingroup    accounting
 * 	\brief      Fichier de la classe des comptes comptables
 */

/**
 * \class 		AccountancyAccount
 * \brief 		Classe permettant la gestion des comptes
 */
class AccountancyAccount
{
    public $db;
    public $error;

    public $rowid;
    public $fk_pcg_version;
    public $pcg_type;
    public $pcg_subtype;
    public $label;
    public $account_number;
    public $account_parent;

    /**
     *  Constructor
     *
     *  @param		DoliDB		$db		Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     *    Insert account into database
     *
     *    @param  	User	$user 	User making add
     *    @return	int				<0 if KO, Id line added if OK
     */
    public function create($user)
    {
        $now=dol_now();

        $sql = "INSERT INTO ".MAIN_DB_PREFIX."accountingaccount";
        $sql.= " (date_creation, fk_user_author, numero,intitule)";
        $sql.= " VALUES (".$this->db->idate($now).",".$user->id.",'".$this->numero."','".$this->intitule."')";

        $resql = $this->db->query($sql);
        if ($resql) {
            $id = $this->db->last_insert_id(MAIN_DB_PREFIX."accountingaccount");

            if ($id > 0) {
                $this->id = $id;
                $result = $this->id;
            } else {
                $result = -2;
                $this->error="AccountancyAccount::Create Erreur $result";
                dol_syslog($this->error, LOG_ERR);
            }
        } else {
            $result = -1;
            $this->error="AccountancyAccount::Create Erreur $result";
            dol_syslog($this->error, LOG_ERR);
        }

        return $result;
    }

}
