<?php
/* Copyright (C) 2001-2002	Rodolphe Quiedeville	<rodolphe@quiedeville.org>
 * Copyright (C) 2004-2011	Laurent Destailleur		<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin			<regis.houssin@capnetworks.com>
 * Copyright (C) 2013       Florian Henry		  	<florian.henry@open-concept.pro>
 * Copyright (C) 2014       Alexandre Spangaro		<alexandre.spangaro@gmail.com> 
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
 *	    \file       htdocs/compta/dons/card.php
 *		\ingroup    don
 *		\brief      Page of donation card
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/dons/modules_don.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/dons/class/don.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/paiement/class/paiement.class.php';
if (! empty($conf->projet->enabled)) {
	require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
}

$langs->load("companies");
$langs->load("donations");
$langs->load("bills");

$id=GETPOST('rowid')?GETPOST('rowid','int'):GETPOST('id','int');
$action=GETPOST('action','alpha');
$cancel=GETPOST('cancel');
$amount=GETPOST('amount');

$object = new Don($db);
$donation_date=dol_mktime(12, 0, 0, GETPOST('remonth'), GETPOST('reday'), GETPOST('reyear'));

// Security check
$result = restrictedArea($user, 'don', $id);

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('doncard','globalcard'));


/*
 * Actions
 */

if ($action == 'update')
{
	if (! empty($cancel))
	{
		header("Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}

	$error=0;

    if (empty($donation_date))
    {
	    setEventMessage($langs->trans("ErrorFieldRequired",$langs->trans("Date")), 'errors');
        $action = "create";
        $error++;
    }

	if (empty($amount))
	{
		$setEventMessage($langs->trans("ErrorFieldRequired",$langs->trans("Amount")), 'errors');
		$action = "create";
		$error++;
	}

	if (! $error)
	{
		$object->firstname		= GETPOST("firstname");
		$object->lastname		= GETPOST("lastname");
		$object->societe		= GETPOST("societe");
		$object->address		= GETPOST("address");
		$object->amount			= price2num(GETPOST("amount"));
		$object->town			= GETPOST("town");
        $object->zip			= GETPOST("zipcode");
        $object->country		= GETPOST("country");
		$object->email			= GETPOST("email");
		$object->date			= $donation_date;
		$object->note			= GETPOST("note");
		$object->public			= GETPOST("public");
		$object->fk_project		= GETPOST("projectid");
		$object->note_private	= GETPOST("note_private");
		$object->note_public	= GETPOST("note_public");
		$object->modepaiementid = GETPOST("modepaiement");

		if ($object->update($user) > 0)
		{
			header("Location: ".$_SERVER['PHP_SELF']."?id=".$object->id);
			exit;
		}
	}
}

if ($action == 'add')
{
	if (! empty($cancel))
	{
		header("Location: index.php");
		exit;
	}

	$error=0;

    if (empty($donation_date))
    {
	    setEventMessage($langs->trans("ErrorFieldRequired",$langs->trans("Date")), 'errors');
        $action = "create";
        $error++;
    }

	if (empty($amount))
	{
		setEventMessage($langs->trans("ErrorFieldRequired",$langs->trans("Amount")), 'errors');
		$action = "create";
		$error++;
	}

	if (! $error)
	{
		$object->firstname		= GETPOST("firstname");
		$object->lastname		= GETPOST("lastname");
		$object->societe		= GETPOST("societe");
		$object->address		= GETPOST("address");
		$object->amount			= price2num(GETPOST("amount"));
		$object->town			= GETPOST("town");
        $object->zip			= GETPOST("zipcode");
        $object->town			= GETPOST("town");
        $object->country		= GETPOST("country");
		$object->email			= GETPOST("email");
		$object->date			= $donation_date;
		$object->note_private	= GETPOST("note_private");
		$object->note_public	= GETPOST("note_public");
		$object->public			= GETPOST("public");
		$object->fk_project		= GETPOST("projectid");
		$object->modepaiementid	= GETPOST("modepaiement");

		if ($object->create($user) > 0)
		{
			header("Location: index.php");
			exit;
		}
	}
}

if ($action == 'delete')
{
	$object->delete($id);
	header("Location: list.php");
	exit;
}
if ($action == 'commentaire')
{
	$object->fetch($id);
	$object->update_note(GETPOST("commentaire"));
}
if ($action == 'valid_promesse')
{
	if ($object->valid_promesse($id, $user->id) >= 0)
	{
		header("Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}
    else {
	    setEventMessage($object->error, 'errors');
    }
}
if ($action == 'set_cancel')
{
    if ($object->set_cancel($id) >= 0)
    {
        header("Location: ".$_SERVER['PHP_SELF']."?id=".$id);
        exit;
    }
    else {
	    setEventMessage($object->error, 'errors');
    }
}
if ($action == 'set_paid')
{
	if ($object->set_paye($id, $modepaiement) >= 0)
	{
		header("Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}
    else {
	    setEventMessage($object->error, 'errors');
    }
}
if ($action == 'set_encaisse')
{
	if ($object->set_encaisse($id) >= 0)
	{
        header("Location: ".$_SERVER['PHP_SELF']."?id=".$id);
		exit;
	}
    else {
	    setEventMessage($object->error, 'errors');
    }
}

/*
 * Build doc
 */
if ($action == 'builddoc')
{
	$object = new Don($db);
	$object->fetch($id);

	// Save last template used to generate document
	if (GETPOST('model')) $object->setDocModel($user, GETPOST('model','alpha'));

	// Define output language
	$outputlangs = $langs;
	$newlang='';
	if ($conf->global->MAIN_MULTILANGS && empty($newlang) && ! empty($_REQUEST['lang_id'])) $newlang=$_REQUEST['lang_id'];
	if ($conf->global->MAIN_MULTILANGS && empty($newlang)) $newlang=$object->client->default_lang;
	if (! empty($newlang))
	{
		$outputlangs = new Translate("",$conf);
		$outputlangs->setDefaultLang($newlang);
	}
	$result=don_create($db, $object->id, '', $object->modelpdf, $outputlangs);
	if ($result <= 0)
	{
		dol_print_error($db,$result);
		exit;
	}
}


/*
 * View
 */

llxHeader('',$langs->trans("Donations"),'EN:Module_Donations|FR:Module_Dons|ES:M&oacute;dulo_Donaciones');

$form=new Form($db);
$formfile = new FormFile($db);
$formcompany = new FormCompany($db);


/*
 * Action create
 */
if ($action == 'create')
{
	print_fiche_titre($langs->trans("AddDonation"));

	print '<form name="add" action="card.php" method="post">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<table class="border" width="100%">';

	print '<input type="hidden" name="action" value="add">';

    $nbrows=11;
    if (! empty($conf->projet->enabled)) $nbrows++;

    // Date
	print '<tr><td class="fieldrequired">'.$langs->trans("Date").'</td><td>';
	$form->select_date($donation_date?$donation_date:-1,'','','','',"add",1,1);
	print '</td>';

    print '<td rowspan="'.$nbrows.'" valign="top">'.$langs->trans("Comments").' :<br>';
    print "<textarea name=\"note_private\" wrap=\"soft\" cols=\"40\" rows=\"15\">".GETPOST("note_private")."</textarea></td>";
    print "</tr>";

    // Amount
    print "<tr>".'<td class="fieldrequired">'.$langs->trans("Amount").'</td><td><input type="text" name="amount" value="'.GETPOST("amount").'" size="10"> '.$langs->trans("Currency".$conf->currency).'</td></tr>';

	print '<tr><td class="fieldrequired">'.$langs->trans("PublicDonation")."</td><td>";
	print $form->selectyesno("public",isset($_POST["public"])?$_POST["public"]:1,1);
	print "</td></tr>\n";

	print "<tr>".'<td>'.$langs->trans("Company").'</td><td><input type="text" name="societe" value="'.GETPOST("societe").'" size="40"></td></tr>';
	print "<tr>".'<td>'.$langs->trans("Firstname").'</td><td><input type="text" name="firstname" value="'.GETPOST("firstname").'" size="40"></td></tr>';
	print "<tr>".'<td>'.$langs->trans("Lastname").'</td><td><input type="text" name="lastname" value="'.GETPOST("lastname").'" size="40"></td></tr>';
	print "<tr>".'<td>'.$langs->trans("Address").'</td><td>';
	print '<textarea name="address" wrap="soft" cols="40" rows="3">'.GETPOST("address").'</textarea></td></tr>';

    // Zip / Town
    print '<tr><td>'.$langs->trans("Zip").' / '.$langs->trans("Town").'</td><td>';
	print $formcompany->select_ziptown((isset($_POST["zipcode"])?$_POST["zipcode"]:$don->zip),'zipcode',array('town','selectcountry_id','state_id'),6);
    print ' ';
    print $formcompany->select_ziptown((isset($_POST["town"])?$_POST["town"]:$don->town),'town',array('zipcode','selectcountry_id','state_id'));
    print '</tr>';

	print "<tr>".'<td>'.$langs->trans("Country").'</td><td><input type="text" name="country" value="'.GETPOST("country").'" size="40"></td></tr>';
	print "<tr>".'<td>'.$langs->trans("EMail").'</td><td><input type="text" name="email" value="'.GETPOST("email").'" size="40"></td></tr>';

    print "<tr><td>".$langs->trans("PaymentMode")."</td><td>\n";
    $form->select_types_paiements('', 'modepaiement', 'CRDT', 0, 1);
    print "</td></tr>\n";

	if (! empty($conf->projet->enabled))
    {
    	
    	$formproject=new FormProjets($db);
    	
        // Si module projet actif
        print "<tr><td>".$langs->trans("Project")."</td><td>";
        $formproject->select_projects('',GETPOST("projectid"),"projectid");
        print "</td></tr>\n";
    }

    // Other attributes
    $parameters=array('colspan' => ' colspan="1"');
    $reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook

	print "</table>\n";
	print '<br><center><input type="submit" class="button" name="save" value="'.$langs->trans("Save").'"> &nbsp; &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'"></center>';
	print "</form>\n";
}

/*
 * Action edit
 */
if (! empty($id) && $action == 'edit')
{
	$object->fetch($id);

	$h=0;
	$head[$h][0] = $_SERVER['PHP_SELF']."?id=".$object->id;
	$head[$h][1] = $langs->trans("Card");
	$hselected=$h;
	$h++;

	dol_fiche_head($head, $hselected, $langs->trans("Donation"), 0, 'generic');

	print '<form name="update" action="card.php" method="post">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<table class="border" width="100%">';

	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="rowid" value="'.$object->id.'">';

	// Ref
	print "<tr>".'<td>'.$langs->trans("Ref").'</td><td colspan="2">';
	print $object->getNameUrl();
	print '</td>';
	print '</tr>';

    $nbrows=12;
    if (! empty($conf->projet->enabled)) $nbrows++;

    // Date
	print "<tr>".'<td width="25%" class="fieldrequired">'.$langs->trans("Date").'</td><td>';
	$form->select_date($object->date,'','','','',"update");
	print '</td>';

    print '<td rowspan="'.$nbrows.'" valign="top">'.$langs->trans("Comments").' :<br>';
    print "<textarea name=\"note_private\" wrap=\"soft\" cols=\"40\" rows=\"15\">".$object->note_private."</textarea></td>";
    print "</tr>";

	// Amount
    print "<tr>".'<td class="fieldrequired">'.$langs->trans("Amount").'</td><td><input type="text" name="amount" size="10" value="'.$object->amount.'"> '.$langs->trans("Currency".$conf->currency).'</td></tr>';

	print '<tr><td class="fieldrequired">'.$langs->trans("PublicDonation")."</td><td>";
	print $form->selectyesno("public",1,1);
	print "</td>";
	print "</tr>\n";

	$langs->load("companies");
	print "<tr>".'<td>'.$langs->trans("Company").'</td><td><input type="text" name="societe" size="40" value="'.$object->societe.'"></td></tr>';
	print "<tr>".'<td>'.$langs->trans("Firstname").'</td><td><input type="text" name="firstname" size="40" value="'.$object->firstname.'"></td></tr>';
	print "<tr>".'<td>'.$langs->trans("Lastname").'</td><td><input type="text" name="lastname" size="40" value="'.$object->lastname.'"></td></tr>';
	print "<tr>".'<td>'.$langs->trans("Address").'</td><td>';
	print '<textarea name="address" wrap="soft" cols="40" rows="'.ROWS_3.'">'.$object->address.'</textarea></td></tr>';

    // Zip / Town
    print '<tr><td>'.$langs->trans("Zip").' / '.$langs->trans("Town").'</td><td>';
    print $formcompany->select_ziptown((isset($_POST["zipcode"])?$_POST["zipcode"]:$object->zip),'zipcode',array('town','selectcountry_id','state_id'),6);
    print ' ';
    print $formcompany->select_ziptown((isset($_POST["town"])?$_POST["town"]:$object->town),'town',array('zipcode','selectcountry_id','state_id'));
    print '</tr>';

	print "<tr>".'<td>'.$langs->trans("Country").'</td><td><input type="text" name="country" size="40" value="'.$object->country.'"></td></tr>';
	print "<tr>".'<td>'.$langs->trans("EMail").'</td><td><input type="text" name="email" size="40" value="'.$object->email.'"></td></tr>';

    print "<tr><td>".$langs->trans("PaymentMode")."</td><td>\n";

    if ($object->modepaiementid) $selected = $object->modepaiementid;
    else $selected = '';

    $form->select_types_paiements($selected, 'modepaiement', 'CRDT', 0, 1);
    print "</td></tr>\n";

	print "<tr>".'<td>'.$langs->trans("Status").'</td><td>'.$object->getLibStatut(4).'</td></tr>';

    // Project
    if (! empty($conf->projet->enabled))
    {
    	$formproject=new FormProjets($db);
    	
        $langs->load('projects');
        print '<tr><td>'.$langs->trans('Project').'</td><td>';
        $formproject->select_projects(-1, (isset($_POST["projectid"])?$_POST["projectid"]:$object->fk_project), 'projectid');
        print '</td></tr>';
    }

    // Other attributes
    $parameters=array('colspan' => ' colspan="1"');
    $reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook

	print "</table>\n";

	print '<br><center><input type="submit" class="button" name="save" value="'.$langs->trans("Save").'"> &nbsp; &nbsp; <input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'"></center>';

	print "</form>\n";

	print "</div>\n";
}

/*
 * Show
 */
if (! empty($id) && $action != 'edit')
{
	$result=$object->fetch($id);

	$h=0;
	$head[$h][0] = $_SERVER['PHP_SELF']."?id=".$object->id;
	$head[$h][1] = $langs->trans("Card");
	$hselected=$h;
	$h++;

	dol_fiche_head($head, $hselected, $langs->trans("Donation"), 0, 'generic');

	print '<form action="' . $_SERVER["PHP_SELF"] . '" method="POST">' . "\n";
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<table class="border" width="100%">';

	$linkback = '<a href="'.DOL_URL_ROOT.'/compta/dons/list.php'.(! empty($socid)?'?socid='.$socid:'').'">'.$langs->trans("BackToList").'</a>';

    $nbrows=12;
    if (! empty($conf->projet->enabled)) $nbrows++;

	// Ref
	print "<tr>".'<td>'.$langs->trans("Ref").'</td><td colspan="2">';
	print $form->showrefnav($object, 'rowid', $linkback, 1, 'rowid', 'ref', '');
	print '</td>';
	print '</tr>';

	// Date
	print '<tr><td width="25%">'.$langs->trans("Date").'</td><td>';
	print dol_print_date($object->date,"day");
	print "</td>";

    print '<td rowspan="'.$nbrows.'" valign="top" width="50%">'.$langs->trans("Comments").' :<br>';
	print nl2br($object->note_private).'</td></tr>';

    print "<tr>".'<td>'.$langs->trans("Amount").'</td><td>'.price($object->amount,0,$langs,0,0,-1,$conf->currency).'</td></tr>';

	print "<tr><td>".$langs->trans("PublicDonation")."</td><td>";
	print yn($object->public);
	print "</td></tr>\n";

	print "<tr>".'<td>'.$langs->trans("Company").'</td><td>'.$object->societe.'</td></tr>';
	print "<tr>".'<td>'.$langs->trans("Firstname").'</td><td>'.$object->firstname.'</td></tr>';
	print "<tr>".'<td>'.$langs->trans("Lastname").'</td><td>'.$object->lastname.'</td></tr>';
	print "<tr>".'<td>'.$langs->trans("Address").'</td><td>'.dol_nl2br($object->address).'</td></tr>';

	// Zip / Town
	print "<tr>".'<td>'.$langs->trans("Zip").' / '.$langs->trans("Town").'</td><td>'.$object->zip.($object->zip && $object->town?' / ':'').$object->town.'</td></tr>';

	// Country
	print "<tr>".'<td>'.$langs->trans("Country").'</td><td>'.$object->country.'</td></tr>';

	// EMail
	print "<tr>".'<td>'.$langs->trans("EMail").'</td><td>'.dol_print_email($object->email).'</td></tr>';

	// Payment mode
	print "<tr><td>".$langs->trans("PaymentMode")."</td><td>";
	print $form->form_modes_reglement(null, $object->modepaiementid,'none');
	print "</td></tr>\n";

	print "<tr>".'<td>'.$langs->trans("Status").'</td><td>'.$object->getLibStatut(4).'</td></tr>';

    // Project
    if (! empty($conf->projet->enabled))
    {
        print "<tr>".'<td>'.$langs->trans("Project").'</td><td>'.$object->projet.'</td></tr>';
    }

    // Other attributes
    $parameters=array('colspan' => ' colspan="1"');
    $reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action);    // Note that $action and $object may have been modified by hook

	print "</table>\n";
	print "</form>\n";

	print "</div>";

	// TODO Gerer action emettre paiement
	$resteapayer = 0;


	/**
	 * Barre d'actions
	 */
	print '<div class="tabsAction">';

	print '<div class="inline-block divButAction"><a class="butAction" href="card.php?action=edit&rowid='.$object->id.'">'.$langs->trans('Modify').'</a></div>';

	if ($object->statut == 0)
	{
		print '<div class="inline-block divButAction"><a class="butAction" href="card.php?rowid='.$object->id.'&action=valid_promesse">'.$langs->trans("ValidPromess").'</a></div>';
	}

    if (($object->statut == 0 || $object->statut == 1) && $resteapayer == 0 && $object->paye == 0)
    {
        print '<div class="inline-block divButAction"><a class="butAction" href="card.php?rowid='.$object->id.'&action=set_cancel">'.$langs->trans("ClassifyCanceled")."</a></div>";
    }

	// TODO Gerer action emettre paiement
	if ($object->statut == 1 && $resteapayer > 0)
	{
		print '<div class="inline-block divButAction"><a class="butAction" href="paiement.php?rowid='.$object->id.'&action=create">'.$langs->trans("DoPayment")."</a></div>";
	}

	if ($object->statut == 1 && $resteapayer == 0 && $object->paye == 0)
	{
		print '<div class="inline-block divButAction"><a class="butAction" href="card.php?rowid='.$object->id.'&action=set_paid">'.$langs->trans("ClassifyPaid")."</a></div>";
	}

	if ($user->rights->don->supprimer)
	{
		print '<div class="inline-block divButAction"><a class="butActionDelete" href="card.php?rowid='.$object->id.'&action=delete">'.$langs->trans("Delete")."</a></div>";
	}
	else
	{
		print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">'.$langs->trans("Delete")."</a></div>";
	}

	print "</div>";


	print '<table width="100%"><tr><td width="50%" valign="top">';

	/*
	 * Generate documents
	 */
	$filename=dol_sanitizeFileName($object->id);
	$filedir=$conf->don->dir_output . '/' . get_exdir($filename,2);
	$urlsource=$_SERVER['PHP_SELF'].'?rowid='.$object->id;
	//            $genallowed=($fac->statut == 1 && ($fac->paye == 0 || $user->admin) && $user->rights->facture->creer);
	//            $delallowed=$user->rights->facture->supprimer;
	$genallowed=1;
	$delallowed=0;

	$var=true;

	print '<br>';
	$formfile->show_documents('donation',$filename,$filedir,$urlsource,$genallowed,$delallowed);

	print '</td><td>&nbsp;</td>';

	print '</tr></table>';

}


llxFooter();
$db->close();
