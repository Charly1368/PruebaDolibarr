<?php
/* Copyright (C) 2012 Charles-François BENKE <charles.fr@benke.fr>
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 *	\file       htdocs/core/boxes/box_task.php
 *	\ingroup    Projet
 *	\brief      Module to Task activity of the current year
 *	\version	$Id: box_task.php,v 1.1 2012/09/11 Charles-François BENKE
 */

include_once(DOL_DOCUMENT_ROOT."/core/boxes/modules_boxes.php");
require_once(DOL_DOCUMENT_ROOT."/core/lib/date.lib.php");

class box_task extends ModeleBoxes {

	var $boxcode="projet";
	var $boximg="object_projecttask";
	var $boxlabel;
	//var $depends = array("projet");
	var $db;
	var $param;

	var $info_box_head = array();
	var $info_box_contents = array();

	/**
	 *      \brief      Constructeur de la classe
	 */
	function box_task()
	{
		global $langs;
		$langs->load("boxes");
		$langs->load("projects");
		$this->boxlabel="Tasks";
	}

	/**
	 *      \brief      Charge les donnees en memoire pour affichage ulterieur
	 *      \param      $max        Nombre maximum d'enregistrements a charger
	 */
	function loadBox($max=5)
	{
		global $conf, $user, $langs, $db;
		
		$this->max=$max;
		
		$totalMnt = 0;
		$totalnb = 0;
		$totalDuree=0;
		include_once(DOL_DOCUMENT_ROOT."/projet/class/task.class.php");
		$taskstatic=new Task($db);
		
		
		$textHead = $langs->trans("Tasks")."&nbsp;".date("Y");
		$this->info_box_head = array('text' => $textHead, 'limit'=> dol_strlen($textHead));

		// list the summary of the orders
		if ($user->rights->projet->lire)
		{
			
			$sql = "SELECT pt.fk_statut, count(pt.rowid) as nb, sum(pt.total_ht) as Mnttot, sum(pt.duration_planned) as Dureetot";
			$sql.= " FROM ".MAIN_DB_PREFIX."projet_task as pt";
			$sql.= " WHERE DATE_FORMAT(pt.datec,'%Y') = ".date("Y")." ";
			$sql.= " GROUP BY pt.fk_statut ";
			$sql.= " ORDER BY pt.fk_statut DESC";
			$sql.= $db->plimit($max, 0);

			$result = $db->query($sql);

			if ($result)
			{
				$num = $db->num_rows($result);
				$i = 0;
				while ($i < $num)
				{
					$this->info_box_contents[$i][0] = array('td' => 'align="left" width="16"','logo' => 'object_projecttask');

					$objp = $db->fetch_object($result);
					$this->info_box_contents[$i][1] = array('td' => 'align="left"',
					'text' =>$langs->trans("Task")."&nbsp;".$taskstatic->LibStatut($objp->fk_statut,0)
					);

					$this->info_box_contents[$i][2] = array('td' => 'align="right"',
					'text' => $objp->nb."&nbsp;".$langs->trans("Tasks"),
					'url' => DOL_URL_ROOT."/projet/tasks/index.php?leftmenu=projects&viewstatut=".$objp->fk_statut
					);
					$totalnb += $objp->nb;
					$this->info_box_contents[$i][3] = array('td' => 'align="right"', 'text' => ConvertSecondToTime($objp->Dureetot,'all',25200,5));
					$totalDuree += $objp->Dureetot;
					$this->info_box_contents[$i][4] = array('td' => 'align="right"', 'text' => number_format($objp->Mnttot, 0, ',', ' ')."&nbsp;".$langs->trans("Currency".$conf->currency));
					$totalMnt += $objp->Mnttot;
					
					$this->info_box_contents[$i][5] = array('td' => 'align="right" width="18"', 'text' => $taskstatic->LibStatut($objp->fk_statut,3));

					$i++;
				}
			}
		}


		// Add the sum to the bottom of the boxes
		$this->info_box_contents[$i][0] = array('tr' => 'class="liste_total"', 'td' => 'colspan=2 align="left" ', 'text' => $langs->trans("Total")."&nbsp;".$textHead);
		$this->info_box_contents[$i][1] = array('td' => 'align="right" ', 'text' => number_format($totalnb, 0, ',', ' ')."&nbsp;".$langs->trans("Tasks"));
		$this->info_box_contents[$i][2] = array('td' => 'align="right" ', 'text' => ConvertSecondToTime($totalDuree,'all',25200,5));
		$this->info_box_contents[$i][3] = array('td' => 'align="right" ', 'text' => number_format($totalMnt, 0, ',', ' ')."&nbsp;".$langs->trans("Currency".$conf->currency));
		$this->info_box_contents[$i][4] = array('td' => 'colspan=2', 'text' => "");	
		
	}

	function showBox($head = null, $contents = null)
	{
		parent::showBox($this->info_box_head, $this->info_box_contents);
	}
}
?>