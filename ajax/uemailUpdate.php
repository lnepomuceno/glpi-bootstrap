<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
* @brief
*/

$AJAX_INCLUDE = 1;
if (strpos($_SERVER['PHP_SELF'],"uemailUpdate.php")) {
   include ('../inc/includes.php');
   header("Content-Type: text/html; charset=UTF-8");
   Html::header_nocache();
}

Session::checkLoginUser();

if ((isset($_POST['field']) && ($_POST["value"] > 0))
    || (isset($_POST['allow_email']) && $_POST['allow_email'])) {

   $default_email = "";
   $emails        = array();
   if (isset($_POST['typefield']) && ($_POST['typefield'] == 'supplier')) {
      $supplier = new Supplier();
      if ($supplier->getFromDB($_POST["value"])) {
      $default_email = $supplier->fields['email'];
      }
   } else {
      $user          = new User();
      if ($user->getFromDB($_POST["value"])) {
         $default_email = $user->getDefaultEmail();
         $emails        = $user->getAllEmails();
      }
   }

   echo __('Email followup').'&nbsp;';

   $default_notif = true;
   if (isset($_POST['use_notification'])) {
      $default_notif = $_POST['use_notification'];
   }

   if (isset($_POST['alternative_email']) && !empty($_POST['alternative_email'])
       && empty($default_email)) {
      $default_email = $_POST['alternative_email'];
   }

   $rand = Dropdown::showYesNo($_POST['field'].'[use_notification]', $default_notif);

   $email_string = '';
   

   echo '<div class="input-group">';
   echo '<div class="input-group-addon">';
   echo '<span class="glyphicon glyphicon-envelope" aria-hidden="true"></span>';
   echo '</div>';
   // Only one email
   if ((count($emails) == 1)
       && !empty($default_email)
       && NotificationMail::isUserAddressValid($default_email)) {
      $email_string =  $default_email;
      // Clean alternative email
      echo "<input type='hidden' class='form-control' name='".$_POST['field']."[alternative_email]'
             value=''>";

   } else if (count($emails) > 1) {
      // Several emails : select in the list
      $emailtab = array();
      foreach ($emails as $new_email) {
         if ($new_email != $default_email) {
            $emailtab[$new_email] = $new_email;
         } else {
            $emailtab[''] = $new_email;
         }
      }
      $email_string = Dropdown::showFromArray($_POST['field']."[alternative_email]", $emailtab,
                                              array('value'   => '',
                                                    'display' => false));
   } else {
      $email_string = "<input type='text' class='form-control' name='".$_POST['field']."[alternative_email]'
                        value='$default_email'>";
   }

  
   echo $email_string;
   
   echo '</div>';
}

Ajax::commonDropdownUpdateItem($_POST);
?>