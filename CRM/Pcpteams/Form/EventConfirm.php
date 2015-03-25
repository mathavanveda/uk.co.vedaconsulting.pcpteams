<?php

/**
 * Search Pcp Team Class
 * Civi 4.5
 * Extends Core Form Controller.
 */
class CRM_Pcpteams_Form_EventConfirm extends CRM_Pcpteams_Form_Workflow {

  function preProcess() {
    parent::preProcess();
    CRM_Utils_System::setTitle(ts('Confirm If you have a place'));
  }

  function buildQuickForm() {
    $this->_pageId = $this->controller->get('pageId');
    $eventDetails = CRM_Pcpteams_Utils::getEventDetailsbyEventId($this->_pageId);
    
    $this->addElement('checkbox', 'is_have_place', ts('Yes I have a Place'));
    $this->addButtons(array(
      array(
        'type' => 'next',
        'name' => ts('Continue'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('eventDetails', $eventDetails);
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  function postProcess() {
    $values = $this->exportValues();
    if ($values['is_have_place'] == 1) {
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/pcp/support', "code=cpftq&qfKey={$this->controller->_key}"));
    } 
    else {
      $eventId = $this->get('component_page_id');
      CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm/event/register', "reset=1&id=$eventId"));
    }
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  function getRenderableElementNames() {
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }
}
