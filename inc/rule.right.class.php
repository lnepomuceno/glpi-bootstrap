<?php
/*
 * @version $Id$
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2009 by the INDEPNET Development Team.

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
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------
if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

/// Rule class for Rights management
class RightAffectRule extends Rule {

	/**
	 * Constructor
	**/
	function __construct() {
		global $RULES_CRITERIAS;	
	
		parent::__construct(RULE_AFFECT_RIGHTS);
		
		//Dynamically add all the ldap criterias to the current list of rule's criterias
		$this->addLdapCriteriasToArray();
		$this->right="rule_ldap";
		$this->orderby="name";
	}

	function preProcessPreviewResults($output)
	{
		return $output;
	}
	
	function maxActionsCount(){
		// Unlimited
		return 4;
	}
	/**
	 * Display form to add rules
	 * @param $target where to post form
	 * @param $ID entity ID
	 */
	function showAndAddRuleForm($target, $ID) {
		global $LANG, $CFG_GLPI;

		$canedit = haveRight($this->right, "w");

		echo "<form name='ldapaffectation_form' id='ldapaffectation_form' method='post' action=\"$target\">";

		if ($canedit) {

			echo "<div class='center'>";
			echo "<table  class='tab_cadre_fixe'>";
         echo "<tr><th colspan='2'>" .$LANG['rulesengine'][19] . "</th></tr>";
         echo "<tr class='tab_bg_2'>";
         echo "<td>".$LANG['common'][16] . "&nbsp;:&nbsp;";
         autocompletionTextField("name", "glpi_rules_descriptions", "name", "", 33);
         echo "&nbsp;&nbsp;&nbsp;".$LANG['joblist'][6] . "&nbsp;:&nbsp;";
         autocompletionTextField("description", "glpi_rules_descriptions", "description", "", 33);
         echo "&nbsp;&nbsp;&nbsp;".$LANG['rulesengine'][9] . "&nbsp;:&nbsp;";
         $this->dropdownRulesMatch("match", "AND");
         echo "</td><td rowspan='2' class='tab_bg_2 center middle'>";
         echo "<input type=hidden name='sub_type' value=\"" . $this->sub_type . "\">";
         echo "<input type=hidden name='FK_entities' value=\"-1\">";
         echo "<input type=hidden name='affectentity' value=\"" . $ID . "\">";
         echo "<input type='submit' name='add_user_rule' value=\"" . $LANG['buttons'][8] . "\" class='submit'>";
         echo "</td></tr>";

         echo "<tr class='tab_bg_2'>";
         echo "<td class='center'>".$LANG['profiles'][22] . "&nbsp;:&nbsp;";
         dropdownValue("glpi_profiles","FK_profiles");
         echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$LANG['profiles'][28] . "&nbsp;:&nbsp;";
         dropdownYesNo("recursive",0);
			echo "</td></tr>";
			
			echo "</table></div><br>";

		}

		echo "<div class='center'><table class='tab_cadrehov'><tr><th colspan='3'>" . $LANG['entity'][6] . "</th></tr>";

		//Get all rules and actions
		$rules = $this->getRulesByID( $ID, 0, 1);

		if (!empty ($rules)) {

			initNavigateListItems(RULE_TYPE,$LANG['entity'][0]."=".getDropdownName("glpi_entities",$ID),$this->sub_type);

			foreach ($rules as $rule) {
				addToNavigateListItems(RULE_TYPE,$rule->fields["ID"],$this->sub_type);

				echo "<tr class='tab_bg_1'>";

				if ($canedit) {
					echo "<td width='10'>";
					$sel = "";
					if (isset ($_GET["select"]) && $_GET["select"] == "all")
						$sel = "checked";
					echo "<input type='checkbox' name='item[" . $rule->fields["ID"] . "]' value='1' $sel>";
					echo "</td>";
				}

				if ($canedit)
					echo "<td><a href=\"" . $CFG_GLPI["root_doc"] . "/front/rule.right.form.php?ID=" . $rule->fields["ID"] . "&amp;onglet=1\">" . $rule->fields["name"] . "</a></td>";
				else
					echo "<td>" . $rule->fields["name"] . "</td>";

				echo "<td>" . $rule->fields["description"] . "</td>";
				echo "</tr>";
			}
		}
		echo "</table></div>";

		if ($canedit) {
			echo "<table class='tab_glpi' width='80%'>";
			echo "<tr><td><img src=\"" . $CFG_GLPI["root_doc"] . "/pics/arrow-left.png\" alt=''></td><td class='center'><a onclick= \"if ( markCheckboxes('ldapaffectation_form') ) return false;\" href='" . $_SERVER['PHP_SELF'] . "?ID=$ID&amp;select=all'>" . $LANG['buttons'][18] . "</a></td>";

			echo "<td>/</td><td class='center'><a onclick= \"if ( unMarkCheckboxes('ldapaffectation_form') ) return false;\" href='" . $_SERVER['PHP_SELF'] . "?ID=$ID&amp;select=none'>" . $LANG['buttons'][19] . "</a>";
			echo "</td><td align='left' width='80%'>";
			echo "<input type='submit' name='delete_user_rule' value=\"" . $LANG['buttons'][6] . "\" class='submit'>";
			echo "</td>";
			echo "</table>";

		}
		echo "</form>";
	}

	/**
	 * Get all ldap rules criterias from the DB and add them into the RULES_CRITERIAS
	 */
	function addLdapCriteriasToArray()
	{
		global $DB,$RULES_CRITERIAS;

			$sql = "SELECT name,value,sub_type FROM glpi_rules_ldap_parameters WHERE sub_type='".$this->sub_type."'";
			$result = $DB->query($sql);
			while ($datas = $DB->fetch_array($result))
			{
					$RULES_CRITERIAS[$this->sub_type][$datas["value"]]['name']=$datas["name"];
					$RULES_CRITERIAS[$this->sub_type][$datas["value"]]['field']=$datas["value"];
					$RULES_CRITERIAS[$this->sub_type][$datas["value"]]['linkfield']='';
					$RULES_CRITERIAS[$this->sub_type][$datas["value"]]['table']='';
				}
	}

	/**
	 * Filter actions if needed
	*  @param $actions the actions array
	*  @param $new_action indicates if the function is called when adding a new action
	*  or when displaying an already added action
	 * @return the filtered actions array
	 */
	function filterActions($actions){
			$RuleAction = new RuleAction;
			$this->actions = $RuleAction->getRuleActions($this->fields["ID"]);
			foreach($this->actions as $action)
			{
				switch ($action->fields["field"])
				{
					case "_affect_entity_by_dn":
						unset($actions["_affect_entity_by_tag"]);
						unset($actions["FK_entities"]);
						break;
					case "_affect_entity_by_tag":
						unset($actions["_affect_entity_by_dn"]);
						unset($actions["FK_entities"]);
						break;
					case "FK_entities":
						unset($actions["_affect_entity_by_tag"]);
						unset($actions["_affect_entity_by_dn"]);
						break;
				}
			}

		return $actions;
	}

	/**
	* Execute the actions as defined in the rule
	* @param $output the result of the actions
	* @param $params the parameters
	* @return the fields modified
	*/
	function executeActions($output,$params,$regex_results)
	{
		$entity='';
		$right='';
		$recursive = 0;
		$continue = true;
		$output_src = $output;
		
		if (count($this->actions)){
			foreach ($this->actions as $action){
					
				switch ($action->fields["action_type"]){
					case "assign" :
						switch ($action->fields["field"])
						{
							case "FK_entities":
								$entity = $action->fields["value"];
							break;
							case "FK_profiles":
								$right = $action->fields["value"];
							break;
							case "recursive":
								$recursive = $action->fields["value"];
							break;
							case "active":
								$output["active"] = $action->fields["value"];
							break;
						} // switch (field)
						break;
					case "regex_result" :
						switch ($action->fields["field"])
						{
							case "_affect_entity_by_dn":
								$res = getRegexResultById($action->fields["value"],$regex_results);
								if ($res != null) {
									$entity=getEntityIDByDn($res);
								} else {
									//Not entity assigned : action processing must be stopped for this rule
									$continue=false;										
								}
							break;
							case "_affect_entity_by_tag":
								$res = getRegexResultById($action->fields["value"],$regex_results);
								if ($res != null) {
									$entity=getEntityIDByTag($res);
								} else {
									//Not entity assigned : action processing must be stopped for this rule
									$continue=false;
								}
								break;								
						} // switch (field)
						/*
						if ($action->fields["field"] == "FK_entities") $entity = $action->fields["value"]; 
						elseif ($action->fields["field"] == "FK_profiles") $right = $action->fields["value"];
						elseif ($action->fields["field"] == "recursive") $recursive = $action->fields["value"];
						elseif ($action->fields["field"] == "active") $output["active"] = $action->fields["value"];
						*/
					break;
				} // switch (action_type)
			} // foreach (action)
		} // count (actions)

		if ($continue)
		{
			//Nothing to be returned by the function :
			//Store in session the entity and/or right
			if ($entity != '' && $right != '')
				$output["_ldap_rules"]["rules_entities_rights"][]=array($entity,$right,$recursive);
			elseif ($entity != '') 
				$output["_ldap_rules"]["rules_entities"][]=array($entity,$recursive);
			elseif ($right != '') 
				$output["_ldap_rules"]["rules_rights"][]=$right;

			return $output;
		}
		else
			return $output_src;
			
	}


/**
 * Return all rules from database
 * @param $ID of rules
 * @param $withcriterias import rules criterias too
 * @param $withactions import rules actions too
 */
function getRulesByID($ID, $withcriterias, $withactions) {
	global $DB;
	$ldap_affect_user_rules = array ();
	// MOYO : quoi donc que ca fout la ca ?
	// MOYO : ca correspond pas deja à un cas particulier de ca : getRuleWithCriteriasAndActions ?


	//Get all the rules whose sub_type is $sub_type and entity is $ID
	$sql="SELECT * 
		FROM `glpi_rules_actions` as gra, glpi_rules_descriptions as grd  
		WHERE gra.FK_rules=grd.ID AND gra.field='FK_entities' 
			AND grd.sub_type='".$this->sub_type."' AND gra.value='".$ID."'";
	
	$result = $DB->query($sql);
	while ($rule = $DB->fetch_array($result)) {
		$affect_rule = new Rule;
		$affect_rule->getRuleWithCriteriasAndActions($rule["ID"], 0, 1);
		$ldap_affect_user_rules[] = $affect_rule;
	}

	return $ldap_affect_user_rules;
}

	function getTitleCriteria($target) {
		global $LANG,$CFG_GLPI;
		echo "<div class='center'>"; 
		echo "<table class='tab_cadrehov'>";
		echo "<tr  class='tab_bg_2'>";
		echo "<td width='100%'>";
		echo "<a href=\"".$CFG_GLPI["root_doc"]."/front/ldap.parameters.php\">".$LANG['Menu'][26]." ".$LANG['ruleldap'][1]."</a>";
		echo "</td></tr>";
		echo "</table></div><br>";

	}

	function getTitleRule($target) {
	}

	function getTitle()
	{
		global $LANG;
		return $LANG['entity'][6];
	}
}

/// Rule collection class for Rights management
class RightRuleCollection extends RuleCollection {

	/// Array containing results : entity + right
	var $rules_entity_rights = array();
	/// Array containing results : only entity 
	var $rules_entity = array();
	/// Array containing results : only right
	var $rules_rights = array();

	/**
	 * Constructor
	**/
	function __construct() {
		global $DB;
		$this->sub_type = RULE_AFFECT_RIGHTS;
		$this->rule_class_name = 'RightAffectRule';
		$this->stop_on_first_match=false;
		$this->right="rule_ldap";
		$this->orderby="name";
	}

	function getTitle() {
		global $LANG;
		return $LANG['rulesengine'][19];
	}


	function cleanTestOutputCriterias($output){
		if (isset($output["_rule_process"]))
			unset($output["_rule_process"]);
			
		return $output;			
	}

	function showTestResults($rule,$output,$global_result){
		global $LANG,$RULES_ACTIONS;

		echo "<tr><th colspan='4'>" . $LANG['rulesengine'][81] . "</th></tr>";
		echo "<tr  class='tab_bg_2'>";
		echo "<td class='tab_bg_2' colspan='4' align='center'>".$LANG['rulesengine'][41]." : <strong> ".getYesNo($global_result)."</strong></td>";

		if (isset($output["_ldap_rules"]["rules_entities"]))
		{
			echo "<tr  class='tab_bg_2'>";
			echo "<td class='tab_bg_2' colspan='4' align='center'>".$LANG['rulesengine'][111]."</td>";

			foreach ($output["_ldap_rules"]["rules_entities"] as $val)
			{
				$this->displayActionByName("entity",$val[0]);
				if (isset($val[1]))
					$this->displayActionByName("recursive",$val[1]);
			}
		}

		if (isset($output["_ldap_rules"]["rules_rights"]))
		{
			echo "<tr  class='tab_bg_2'>";
			echo "<td class='tab_bg_2' colspan='4' align='center'>".$LANG['rulesengine'][110]."</td>";

			foreach ($output["_ldap_rules"]["rules_rights"] as $val)
				$this->displayActionByName("profile",$val[0]);
		}

		if (isset($output["_ldap_rules"]["rules_entities_rights"]))
		{
			echo "<tr  class='tab_bg_2'>";
			echo "<td class='tab_bg_2' colspan='4' align='center'>".$LANG['rulesengine'][112]."</td>";

			foreach ($output["_ldap_rules"]["rules_entities_rights"] as $val)
			{
				$this->displayActionByName("entity",$val[0]);
				if (isset($val[1]))
					$this->displayActionByName("profile",$val[1]);
				if (isset($val[2]))
					$this->displayActionByName("recursive",$val[2]);
			}
		}
		
		if (isset($output["_ldap_rules"]))
			unset($output["_ldap_rules"]);
			
		foreach ($output as $criteria => $value)
		{
			echo "<tr  class='tab_bg_2'>";
			echo "<td class='tab_bg_2' align='center'>";
			echo $RULES_ACTIONS[$this->sub_type][$criteria]["name"];
			echo "</td>";
			echo "<td class='tab_bg_2' align='center'>";
			echo $rule->getActionValue($criteria,$value);
			echo "</td>";
			echo "</tr>";

		}
		echo "</tr>";
	}
	/**
	* Display action using its name
	* @param $name action name
	* @param $value default value
	*/
	function displayActionByName($name,$value){
		global $LANG;
		echo "<tr>"; 
		switch ($name){
			case "entity":
			 	echo  "<td class='tab_bg_2' align='center'>".$LANG['entity'][0]." </td>\n"; 
			 	echo  "<td class='tab_bg_2' align='center'>";                                                                         
			 	echo  getDropdownName("glpi_entities",$value);  
			 	echo  "</td>"; 
			break;
			case "profile":
			 	echo  "<td class='tab_bg_2' align='center'>".$LANG['Menu'][35]." </td>\n"; 
			 	echo  "<td class='tab_bg_2' align='center'>";                                                                         
			 	echo  getDropdownName("glpi_profiles",$value);  
			 	echo  "</td>"; 
			break;			
			case "recursive":
			 	echo "<td class='tab_bg_2' align='center'>".$LANG['profiles'][28]." </td>\n";
			 	echo  "<td class='tab_bg_2' align='center'>";                                                                         
			 	echo ((!$value)?$LANG['choice'][0]:$LANG['choice'][1]); 
			 	echo  "</td>"; 
			break;			
		}
		echo  "</tr>"; 
	}
	/**
	 * Get all the fields needed to perform the rule
	 */
	function getFieldsToLookFor()
	{
		global $DB;
		$params = array();
		$sql = "SELECT DISTINCT value 
			FROM glpi_rules_descriptions, glpi_rules_criterias, glpi_rules_ldap_parameters 
			WHERE glpi_rules_descriptions.sub_type='".$this->sub_type."' 
				AND glpi_rules_criterias.FK_rules=glpi_rules_descriptions.ID 
				AND glpi_rules_criterias.criteria=glpi_rules_ldap_parameters.value";
		
		$result = $DB->query($sql);
		while ($param = $DB->fetch_array($result))
		{
			//Dn is alwsays retreived from ldap : don't need to ask for it !
			if ($param["value"] != "dn")
				$params[]=strtolower($param["value"]);
		}
		return $params;
	}
	
		/**
	 * Get the attributes needed for processing the rules
	 * @param $input input datas
	 * @param $params extra parameters given
	 * @return an array of attributes
	 */
	function prepareInputDataForProcess($input,$params){
		global $RULES_CRITERIAS;
		
		$rule_parameters = array();
		
		//LDAP type method
		if ($params["type"] == "LDAP")
		{
			//Get all the field to retrieve to be able to process rule matching
			$rule_fields = $this->getFieldsToLookFor();
				
			//Get all the datas we need from ldap to process the rules
			$sz = @ ldap_read($params["connection"], $params["userdn"], "objectClass=*", $rule_fields);
			$rule_input = ldap_get_entries($params["connection"], $sz);
	
			if (count($rule_input))
			{
	
				if (isset($input)) 
					$groups = $input;
				else
					$groups = array();
					
					$rule_input = $rule_input[0];
	
					//Get all the ldap fields
					$fields = $this->getFieldsForQuery();
					
					foreach ($fields as $field)
					{
							switch(strtoupper($field))
							{
								case "LDAP_SERVER":
									$rule_parameters["LDAP_SERVER"] = $params["ldap_server"];
									break;
								case "GROUPS" :
										foreach ($groups as $group)
											$rule_parameters["GROUPS"][] = $group;
								break;
								default :
									if (isset($rule_input[$field]))
									{
										if (!is_array($rule_input[$field]))
											$rule_parameters[$field] = $rule_input[$field];
											else
											{
													for ($i=0;$i < count($rule_input[$field]) -1;$i++)
														$rule_parameters[$field][] = $rule_input[$field][$i];
													break;
											}	
									}
							}
					}
					
					return $rule_parameters;
			}
			else return $rule_input;
		}
		//IMAP/POP login method
		else
		{
			$rule_parameters["MAIL_SERVER"] = $params["mail_server"];
			$rule_parameters["MAIL_EMAIL"] = $params["email"];
			return $rule_parameters;
		}
	}
	
		/**
	 * Get the list of fields to be retreived to process rules
	 */
	function getFieldsForQuery()
	{
		global $RULES_CRITERIAS;

		$fields = array();
		foreach ($RULES_CRITERIAS[$this->sub_type] as $criteria){
				if (isset($criteria['virtual']) && $criteria['virtual'])
					$fields[]=$criteria['id'];
				else	
				$fields[]=$criteria['field'];	
		}
		
		return $fields;		  
	}
}
?>
