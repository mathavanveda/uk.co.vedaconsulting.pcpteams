<?php

require_once 'pcpteams.civix.php';
require_once 'CRM/Pcpteams/Constant.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function pcpteams_civicrm_config(&$config) {
  _pcpteams_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function pcpteams_civicrm_xmlMenu(&$files) {
  _pcpteams_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function pcpteams_civicrm_install() {
  //create custom group from xml file 
  // Create OptionGroup, OptionValues, RelationshipType, CustomGroup and CustomFields
  $extensionDir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR;
  $customDataXMLFile = $extensionDir  . '/xml/CustomGroupData.xml';
  $import = new CRM_Utils_Migrate_Import( );
  $import->run( $customDataXMLFile );
  
  //Create Contact Subtype
  $params = array('parent_id' => 3, 'is_active' => 1, 'is_reserved' => 0);
  foreach (array(
    CRM_Pcpteams_Constant::C_CONTACT_SUB_TYPE_TEAM
    , CRM_Pcpteams_Constant::C_CONTACTTYPE_IN_MEM
    , CRM_Pcpteams_Constant::C_CONTACTTYPE_IN_CELEB
    , CRM_Pcpteams_Constant::C_CONTACTTYPE_BRANCH
    , CRM_Pcpteams_Constant::C_CONTACTTYPE_PARTNER
    ) as $subTypes) {
    if(!CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_ContactType', $subTypes, 'id', 'name')){
      $params['name']  = $subTypes;
      $params['label'] = str_replace('_', ' ', $subTypes);
      CRM_Contact_BAO_ContactType::add($params);
    }
  }
  
  //set foreignkey
  $sql = "ALTER TABLE `civicrm_value_pcp_custom_set`
  MODIFY `team_pcp_id` int(10) unsigned DEFAULT NULL,
  ADD CONSTRAINT `FK_civicrm_value_pcp_custom_set_team_pcp_id` FOREIGN KEY (`team_pcp_id`) REFERENCES `civicrm_pcp` (`id`) ON DELETE SET NULL";
  CRM_Core_DAO::executeQuery($sql);
  
  //set foreignkey for pcp_a_b and pcp_b_a on delete make null
  $alterSql = "ALTER TABLE `civicrm_value_pcp_relationship_set`
  MODIFY `pcp_a_b` int(10) unsigned DEFAULT NULL, MODIFY `pcp_b_a` int(10) unsigned DEFAULT NULL,
  ADD CONSTRAINT `FK_civicrm_value_pcp_relationship_set_pcp_a_b` FOREIGN KEY (`pcp_a_b`) REFERENCES `civicrm_pcp` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_civicrm_value_pcp_relationship_set_pcp_b_a` FOREIGN KEY (`pcp_b_a`) REFERENCES `civicrm_pcp` (`id`) ON DELETE SET NULL
  ";
  CRM_Core_DAO::executeQuery($alterSql);
  
  //invite team 
  $messageHtmlSampleTeamInviteFile  = $extensionDir . '/message_templates/msg_tpl_invite_members_to_team.tpl';
  $messageHtml      = file_get_contents($messageHtmlSampleTeamInviteFile);
  $message_params['invite_team'] = array(
    'sequential'  => 1,
    'version'     => 3,
    'msg_title'   => CRM_Pcpteams_Constant::C_MSG_TPL_INVITE_TEAM,
    'msg_subject' => (string) '{$inviteeFirstName}  you have been invited to join {$teamName} and support Leukaemia and Lymphoma Research',
    'is_default'  => 1,
    'msg_html'    => $messageHtml,
  );
  
  //join team
  $messageHtmlSampleTeamInviteFile  = $extensionDir . '/message_templates/msg_tpl_join_request_to_team.tpl';
  $messageHtml      = file_get_contents($messageHtmlSampleTeamInviteFile);
  $message_params['join_team'] = array(
    'sequential'  => 1,
    'version'     => 3,
    'msg_title'   => CRM_Pcpteams_Constant::C_MSG_TPL_JOIN_REQUEST,
    'msg_subject' => (string) '{$userFirstName} {$userlastName} has requested to join Team {$teamName} please authorise',
    'is_default'  => 1,
    'msg_html'    => $messageHtml,
  );

  //leave team    
  $messageHtmlSampleTeamInviteFile  = $extensionDir . '/message_templates/msg_tpl_leave_team.tpl';
  $messageHtml      = file_get_contents($messageHtmlSampleTeamInviteFile);
  $message_params['leave_team'] = array(
    'sequential'  => 1,
    'version'     => 3,
    'msg_title'   => CRM_Pcpteams_Constant::C_MSG_TPL_LEAVE_TEAM,
    'msg_subject' => (string) '{$userFirstName} {$userLastName} has decided to leave Team {$teamName}',
    'is_default'  => 1,
    'msg_html'    => $messageHtml,
  );    
  
   //decline join request    
  $messageHtmlSampleTeamInviteFile  = $extensionDir . '/message_templates/msg_tpl_decline_request_to_team.tpl';
  $messageHtml      = file_get_contents($messageHtmlSampleTeamInviteFile);
  $message_params['decline_team'] = array(
    'sequential'  => 1,
    'version'     => 3,
    'msg_title'   => CRM_Pcpteams_Constant::C_MSG_TPL_JOIN_REQ_DECLINE_TEAM,
    'msg_subject' => (string) '{$userFirstName} {$userLastName} your request to join {$teamName} has been turned down',
    'is_default'  => 1,
    'msg_html'    => $messageHtml,
  );
  
   //approve join request    
  $messageHtmlSampleTeamInviteFile  = $extensionDir . '/message_templates/msg_tpl_approve_request_to_team.tpl';
  $messageHtml      = file_get_contents($messageHtmlSampleTeamInviteFile);
  $message_params['decline_team'] = array(
    'sequential'  => 1,
    'version'     => 3,
    'msg_title'   => CRM_Pcpteams_Constant::C_MSG_TPL_JOIN_REQ_APPROVE_TEAM,
    'msg_subject' => (string) '{$userFirstName} {$userLastName} your request to join {$teamName} has been approved',
    'is_default'  => 1,
    'msg_html'    => $messageHtml,
  );
  $ogId = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', CRM_Pcpteams_Constant::C_OG_MSG_TPL_WORKFLOW, 'id', 'name');
  foreach ($message_params as $key => $message_param) {
    $opValue = civicrm_api3('OptionValue', 'getsingle', array(
      'version' => 3, 
      'option_group_id' => $ogId, 
      'name' => $message_param['msg_title']
    ));
    if ($opValue['id']) {
      $message_param['workflow_id'] = $opValue['id'];
    }
    $result = civicrm_api3('MessageTemplate', 'create', $message_param);
  }

  return _pcpteams_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function pcpteams_civicrm_uninstall() {
  //Remove required data added when install extensions
  CRM_Core_DAO::executeQuery("
    DROP TABLE IF EXISTS civicrm_value_pcp_custom_set");
  CRM_Core_DAO::executeQuery("
    DROP TABLE IF EXISTS civicrm_value_pcp_relationship_set");
  CRM_Core_DAO::executeQuery("
    DELETE opv.* 
    FROM civicrm_option_value opv
    INNER JOIN civicrm_option_group og on opv.option_group_id = og.id
    where og.name = 'pcp_tribute'");
  CRM_Core_DAO::executeQuery("
    DELETE FROM civicrm_option_group where name = 'pcp_tribute'");
  CRM_Core_DAO::executeQuery("
    DELETE cf.* 
    FROM civicrm_custom_field cf
    INNER JOIN civicrm_custom_group cg on cf.custom_group_id = cg.id
    where cg.name LIKE '%PCP_%'");
  CRM_Core_DAO::executeQuery("
    DELETE FROM civicrm_custom_group where name LIKE '%PCP_%'");
  CRM_Core_DAO::executeQuery("
    DELETE pb.* 
    FROM civicrm_pcp_block pb
    LEFT JOIN civicrm_pcp pcp on pb.id = pcp.pcp_block_id
    WHERE pcp.id IS NULL");  
  CRM_Core_DAO::executeQuery("
    DELETE uff.*
    FROM civicrm_uf_field uff
    LEFT JOIN civicrm_uf_group ufg on ufg.id = uff.uf_group_id
    WHERE ufg.name = 'PCP_Supporter_Profile'");  
  CRM_Core_DAO::executeQuery("
    DELETE ufj.*
    FROM civicrm_uf_join ufj
    LEFT JOIN civicrm_uf_group ufg on ufg.id = ufj.uf_group_id
    WHERE ufg.name = 'PCP_Supporter_Profile'");  
  CRM_Core_DAO::executeQuery("
    DELETE FROM civicrm_uf_group WHERE name = 'PCP_Supporter_Profile'");
  CRM_Core_DAO::executeQuery("
    DELETE msgt.*
    FROM civicrm_msg_template msgt WHERE msgt.msg_title = 'Sample Team Invite Template'");
  CRM_Core_DAO::executeQuery("
    DELETE opv.* 
    FROM civicrm_option_value opv
    INNER JOIN civicrm_option_group og on opv.option_group_id = og.id
    where og.name = 'msg_tpl_workflow_PCP'");
  CRM_Core_DAO::executeQuery("
    DELETE FROM civicrm_option_group where name = 'msg_tpl_workflow_PCP'");
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_relationship_type where name_a_b like '%PCP Team%'");
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_relationship_type where name_a_b like 'Team Member of'");
  CRM_Core_DAO::executeQuery("DELETE FROM civicrm_relationship_type where name_a_b like 'Team Leader of'");
  return _pcpteams_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function pcpteams_civicrm_enable() {
  return _pcpteams_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function pcpteams_civicrm_disable() {
  return _pcpteams_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function pcpteams_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _pcpteams_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function pcpteams_civicrm_managed(&$entities) {
  return _pcpteams_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function pcpteams_civicrm_caseTypes(&$caseTypes) {
  _pcpteams_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function pcpteams_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _pcpteams_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

// create soft credit for team contact
function pcpteams_civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
  if ($objectName == 'ContributionSoft' && $op == 'create' && $objectRef->pcp_id) {
    // switch to event's campaign id where the pcp is related to while contribution is created where contribution has contribution page's campaign id
    $updateQuery = "
      UPDATE civicrm_contribution cc
      INNER JOIN civicrm_contribution_soft cs ON cs.contribution_id = cc.id
      INNER JOIN civicrm_pcp cp ON cp.id = cs.pcp_id
      INNER JOIN civicrm_event ce ON ce.id = cp.page_id
      SET cc.campaign_id = ce.campaign_id, cc.source = %3
      WHERE cp.page_type = %1 AND cs.id = %2 ";
    
    $queryParams = array( 
      1 => array('event', 'String'),
      2 => array($objectId, 'Int'),
      3 => array('PCP', 'String'),
    );
    CRM_Core_DAO::executeQuery($updateQuery, $queryParams);
    $query      = "SELECT pcp.contact_id, cs.tribute_contact_id, cs.org_id 
      FROM civicrm_value_pcp_custom_set cs
      INNER JOIN civicrm_pcp pcp ON cs.team_pcp_id = pcp.id 
      WHERE cs.entity_id = %1";
    $dao = CRM_Core_DAO::executeQuery($query, array(1 => array($objectRef->pcp_id, 'Integer')) );
    $dao->fetch();
    
    $actParams = array(
      'target_contact_id' =>  $objectRef->contact_id,
      'source_record_id'    => $objectRef->contribution_id,
      'source_contact_id'  => $objectRef->contact_id
      );
    $customDigFund= CRM_Core_BAO_CustomField::getCustomFieldID(CRM_Pcpteams_Constant::C_CF_DIGITAL_FUNDRAISING_PCP_ID, CRM_Pcpteams_Constant::C_CG_DIGITAL_FUNDRAISING);
    if ($customDigFund) {
      $actParams["custom_{$customDigFund}"] = $objectRef->pcp_id;
    }
    CRM_Pcpteams_Utils::createPcpActivity($actParams, CRM_Pcpteams_Constant::C_AT_SOFT_CREDIT);
    
    if ($dao->contact_id) { 
      $newSoft = clone $objectRef;
      $newSoft->contact_id = $dao->contact_id;
      // $newSoft->pcp_personal_note = "Created From Hook";
      unset($newSoft->id);
      $newSoft->save();
      $actParams = array(
        'target_contact_id' =>  $dao->contact_id,
        'source_record_id'    => $objectRef->contribution_id,
        'source_contact_id'  => $dao->contact_id
        ); 
      $customDigFund= CRM_Core_BAO_CustomField::getCustomFieldID(CRM_Pcpteams_Constant::C_CF_DIGITAL_FUNDRAISING_PCP_ID, CRM_Pcpteams_Constant::C_CG_DIGITAL_FUNDRAISING);
      if ($customDigFund) {
        $actParams["custom_{$customDigFund}"] = $objectRef->pcp_id;
      }
      CRM_Pcpteams_Utils::createPcpActivity($actParams, CRM_Pcpteams_Constant::C_AT_SOFT_CREDIT);
    }

    if ($dao->tribute_contact_id) {
      $newSoft = clone $objectRef;
      $newSoft->contact_id = $dao->tribute_contact_id;
      // $newSoft->pcp_personal_note = "Created From Hook";
      unset($newSoft->id);
      $newSoft->save();
      $actParams = array(
        'target_contact_id' =>  $dao->tribute_contact_id,
        'source_record_id'    => $objectRef->contribution_id,
        'source_contact_id'  => $dao->tribute_contact_id
        );    
      $customDigFund= CRM_Core_BAO_CustomField::getCustomFieldID(CRM_Pcpteams_Constant::C_CF_DIGITAL_FUNDRAISING_PCP_ID, CRM_Pcpteams_Constant::C_CG_DIGITAL_FUNDRAISING);
      if ($customDigFund) {
        $actParams["custom_{$customDigFund}"] = $objectRef->pcp_id;
      }
      CRM_Pcpteams_Utils::createPcpActivity($actParams, CRM_Pcpteams_Constant::C_AT_SOFT_CREDIT);
    }
    
    if ($dao->org_id) {
      $newSoft = clone $objectRef;
      $newSoft->contact_id = $dao->org_id;
      // $newSoft->pcp_personal_note = "Created From Hook";
      unset($newSoft->id);
      $newSoft->save();
      $actParams = array(
        'target_contact_id' =>  $dao->org_id,
        'source_record_id'    => $objectRef->contribution_id,
        'source_contact_id'  => $dao->org_id
      );
      $customDigFund= CRM_Core_BAO_CustomField::getCustomFieldID(CRM_Pcpteams_Constant::C_CF_DIGITAL_FUNDRAISING_PCP_ID, CRM_Pcpteams_Constant::C_CG_DIGITAL_FUNDRAISING);
      if ($customDigFund) {
        $actParams["custom_{$customDigFund}"] = $objectRef->pcp_id;
      }
      CRM_Pcpteams_Utils::createPcpActivity($actParams, CRM_Pcpteams_Constant::C_AT_SOFT_CREDIT);
    }

    $messageTplID = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_MessageTemplate', CRM_Pcpteams_Constant::C_MSG_TPL_SOMEONE_DONATED_FOR_YOU, 'id', 'msg_title');
    if ($messageTplID) {
      $query = "SELECT 
        pcp.contact_id       as contactID, 
        contr.total_amount   as donor_total_amount, 
        donor.id             as donor_id,
        donor.first_name     as donor_first_name,
        donor.last_name      as donor_last_name,
        pcp.id               as pcp_id,
        pcp.intro_text       as pcp_intro_text,
        pcp.title            as pcp_title,
        pcp.goal_amount      as pcp_goal_amount,
        screditor.id         as screditor_id,
        screditor.first_name as screditor_first_name,
        screditor.last_name  as screditor_last_name,
        eve.id               as event_id,
        eve.title            as event_title,
        soft.pcp_personal_note  as personal_note
        FROM civicrm_contribution_soft soft
        INNER JOIN civicrm_pcp pcp               ON soft.pcp_id = pcp.id
        INNER JOIN civicrm_contribution contr    ON soft.contribution_id = contr.id
        INNER JOIN civicrm_contact donor         ON contr.contact_id = donor.id
        INNER JOIN civicrm_contact screditor     ON soft.contact_id = screditor.id
        LEFT JOIN civicrm_event eve              ON (eve.id = pcp.page_id AND pcp.page_type = 'event')
        WHERE soft.id = %1";
      $queryParams = array( 
        1 => array($objectRef->id, 'Int'),
      );
      $data = CRM_Core_DAO::executeQuery($query, $queryParams);

      $pcp = $screditor = $donor = $participant = array();
      if ($data->fetch()) {
        $remActObj = new CRM_Utils_ReminderActivityViaJob(10000);

        $pcp['title']               = $data->pcp_title;
        $pcp['intro_text']          = $data->pcp_intro_text;
        $pcp['target']              = $data->pcp_goal_amount;
        $screditor['first_name']    = $data->screditor_first_name;
        $screditor['last_name']     = $data->screditor_last_name;
        $participant['event_id']    = $data->event_id;
        $participant['event_title'] = $data->event_title;
        $donor['total_amount']      = $data->donor_total_amount;
        $donor['first_name']        = $data->donor_first_name;
        $donor['last_name']         = $data->donor_last_name;
        $donor['personal_note']     = $data->personal_note;
        $pcp['raised']              = $remActObj->getDFPRaisedAmount($data->pcp_id);
        if ($node = $remActObj->getDFPNode($data->pcp_id)) {
          $pcp['url'] = CRM_Utils_System::url("node/{$node}", NULL, TRUE);
        }

        $tplParams = array(
          'uk_co_vedaconsulting_pcp'         => $pcp,
          'uk_co_vedaconsulting_screditor'   => $screditor,
          'uk_co_vedaconsulting_donor'       => $donor,
          'uk_co_vedaconsulting_participant' => $participant,
        );
        $contactSubTypes = CRM_Contact_BAO_Contact::getContactTypes($data->contactID);
        if (in_array(CRM_Pcpteams_Constant::C_CONTACT_SUB_TYPE_TEAM, $contactSubTypes)) {
          // if team pcp
          $teamAdminId = CRM_Pcpteams_Utils::getTeamAdmin($data->pcp_id);
          list($displayName, $toEmail) = CRM_Contact_BAO_Contact_Location::getEmailDetails($teamAdminId);
        } else {
          list($displayName, $toEmail) = CRM_Contact_BAO_Contact_Location::getEmailDetails($data->contactID);
        }

        $domainValues = CRM_Core_BAO_Domain::getNameAndEmail();
        $fromName = $domainValues[0];
        $email    = $domainValues[1];

        $templateParams = array(
          'messageTemplateID' => $messageTplID,
          'contactId'         => $data->contactID,
          'tplParams'         => $tplParams,
          'from'              => "$fromName <$email>",
          'toName'            => $displayName,
          'toEmail'           => $toEmail,
          'replyTo'           => $email,
        );
        list($sent) = CRM_Core_BAO_MessageTemplate::sendTemplate($templateParams);
      }
    }
  }

  //FIXME: Causes even registration to fail 
  //if($op == 'create' && $objectName == 'Participant') {
  //  $pcpBlockId = CRM_Pcpteams_Utils::getPcpBlockId($objectRef->event_id);
  //  if($pcpBlockId) {
  //    // Auto create default PCP
  //    CRM_Pcpteams_Utils::getPcpId($objectRef->event_id, 'event', TRUE, $objectRef->contact_id );
  //  }
  //}
  
  if ($objectName == 'PCP' && $op == 'edit') {
    CRM_Pcpteams_Utils::adjustTeamTarget($objectId);
  }
}
    
function pcpteams_civicrm_buildForm($formName, &$form) {
  if($formName == 'CRM_Event_Form_Registration_ThankYou') {
    $template              = CRM_Core_Smarty::singleton( );
    $beginHookFormElements = $template->get_template_vars();
    if($beginHookFormElements['pcpLink']) {
      $pageId = $form->getVar('_eventId');
      $supportURL  = CRM_Utils_System::url('civicrm/pcp/support', "reset=1&pageId={$pageId}&component=event");
      $form->assign('pcpLink', $supportURL);
    }
  }
}

function pcpteams_civicrm_permission(&$permissions) {
  $version = CRM_Utils_System::version();
  if (version_compare($version, '4.6.1') >= 0) {
    $permissions += array(
      'edit own pcpteams pages' => array(
        ts('PCPTeams: edit own pcpteams pages', array('domain' => 'uk.co.vedaconsulting.pcpteams')),
        ts('Allows users to edit sections of pcpteams pages as long as they are owner or admin for the pcp page', array('domain' => 'uk.co.vedaconsulting.pcpteams')),
      ),
    );
  }
  else {
    $permissions += array(
      'edit own pcpteams pages' => ts('PCPTeams: edit own pcpteams pages', array('domain' => 'uk.co.vedaconsulting.pcpteams')),
    );
  }
}

/**
 * Implementation of hook_civicrm_alterAPIPermissions
 * Copying the permissions same as entity = 'Contact'
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterAPIPermissions
 */
function pcpteams_civicrm_alterAPIPermissions($entity, $action, &$params, &$permissions) {
    $permissions['pcpteams'] = array(
    'create' => array(
      'edit own pcpteams pages',
    ),
    // managed by query object
    'get' => array(),
    'getContactlist' => array(
      'edit own pcpteams pages',
      // 'access AJAX API',
    ),
    'getEventList' => array(
    'edit own pcpteams pages',
    // 'access AJAX API',
    ),
  );
}

function pcpteams_civicrm_custom( $op, $groupID, $entityID, &$params ) {
  if ( $op != 'create' && $op != 'edit' ) {
      return;
  }
  $customFields = array();
  if ($groupID  == CRM_Pcpteams_Utils::getPcpCustomSetId()) {
    foreach ($params as $key => $value) {
      $customFields[$value['column_name']] = $value['value'];
    }
    $teamContactId = CRM_Pcpteams_Utils::getcontactIdbyPcpId($entityID);
    if ('Team' == CRM_Pcpteams_Utils::checkPcpType($entityID)) {
      $cfpcpab = CRM_Pcpteams_Utils::getPcpABCustomFieldId();
      $customParams = array(
        "custom_{$cfpcpab}" => $entityID
      );
      CRM_Pcpteams_Utils::reCreateRelationship($teamContactId, $customFields['org_id'], CRM_Pcpteams_Constant::C_CORPORATE_REL_TYPE, $customParams);
    }
  }
  
}
