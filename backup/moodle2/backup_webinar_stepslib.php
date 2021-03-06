<?php

  //------------------------------------------------------------------
  // This is the "graphical" structure of the Facet-to-face module:
  //
  //          webinar                  webinar_sessions
  //         (CL, pk->id)-------------(CL, pk->id, fk->webinar)
  //                                          |  |  |  |
  //                                          |  |  |  |
  //            webinar_signups------------+  |  |  |
  //        (UL, pk->id, fk->sessionid)          |  |  |
  //                     |                       |  |  |
  //         webinar_signups_status           |  |  |
  //         (UL, pk->id, fk->signupid)          |  |  |
  //                                             |  |  |
  //                                             |  |  |
  //         webinar_session_roles------------+  |  |
  //        (UL, pk->id, fk->sessionid)             |  |
  //                                                |  |
  //                                                |  |
  //     webinar_session_field                   |  |
  //          (SL, pk->id)  |                       |  |
  //                        |                       |  |
  //             webinar_session_data------------+  |
  //    (CL, pk->id, fk->sessionid, fk->fieldid)       |
  //                                                   |
  //                                    webinar_sessions_dates
  //                                    (CL, pk->id, fk->session)
  //
  // Meaning: pk->primary key field of the table
  //          fk->foreign key to link with parent
  //          SL->system level info
  //          CL->course level info
  //          UL->user level info
  //
//------------------------------------------------------------------

class backup_webinar_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
        // Define each element separated
        $webinar = new backup_nested_element('webinar', array('id'), array(
            'course', 'name', 'description', 'agenda', 'sitexmlapiurl', 'adminpassword', 'adminemail'));
        $sessions = new backup_nested_element('sessions');
        $session = new backup_nested_element('session', array('id'), array(
            'webinar', 'capacity', 'presenter', 'scoid', 'urlpath', 'timecreated', 'timemodified'));
        $signups = new backup_nested_element('signups');
        $signup = new backup_nested_element('signup', array('id'), array(
            'sessionid', 'userid', 'mailedreminder', 'discountcode', 'notificationtype'));
        $signups_status = new backup_nested_element('signups_status');
        $signup_status = new backup_nested_element('signup_status', array('id'), array(
            'signupid', 'statuscode', 'superceded', 'grade', 'note', 'advice', 'createdby', 'timecreated'));
        $session_roles = new backup_nested_element('session_roles');
        $session_role = new backup_nested_element('session_role', array('id'), array(
            'sessionid', 'roleid', 'userid'));
        $session_data = new backup_nested_element('session_data');
        //May need to replace first item 'data' with better value
        $session_data_element = new backup_nested_element('data', array('id'), array(
            'fieldid', 'sessionid', 'data'));
        //$session_field = new backup_nested_element('session_field');
        //May need to replace first item 'field' with better value
        /*$session_field_element = new backup_nested_element('field', array('id'), array(
            'name', 'shortname', 'type', 'possiblevalues', 'required', 'defaultvalue', 'isfilter', 'showinsummary'));*/
        $sessions_dates = new backup_nested_element('sessions_dates');
        $sessions_date = new backup_nested_element('sessions_date', array('id'), array(
            'sessionid', 'timestart', 'timefinish'));
        // Build the tree
        $webinar->add_child($sessions);
        $sessions->add_child($session);
        $session->add_child($signups);
        $signups->add_child($signup);
        $signup->add_child($signups_status);
        $signups_status->add_child($signup_status);
        $session->add_child($session_roles);
        $session_roles->add_child($session_role);
        $session->add_child($session_data);
        $session_data->add_child($session_data_element);
        /*$session->add_child($session_field);
        $session_field->add_child($session_field_element);*/
        $session->add_child($sessions_dates);
        $sessions_dates->add_child($sessions_date);
        // Define sources
        $webinar->set_source_table('webinar', array('id' => backup::VAR_ACTIVITYID));
        $session->set_source_table('webinar_sessions', array('webinar' => backup::VAR_PARENTID));
        $sessions_date->set_source_table('webinar_sessions_dates', array('sessionid' => backup::VAR_PARENTID));
        if ($userinfo) {
            $signup->set_source_table('webinar_signups', array('sessionid' => backup::VAR_PARENTID));
            $signup_status->set_source_table('webinar_signups_status', array('signupid' => backup::VAR_PARENTID));
            $session_role->set_source_table('webinar_session_roles', array('sessionid' => backup::VAR_PARENTID));
            $session_data_element->set_source_table('webinar_session_data', array('sessionid' => backup::VAR_PARENTID));
        }
        // Define id annotations
        $signup->annotate_ids('user', 'userid');
        $session_role->annotate_ids('role', 'roleid');
        $session_role->annotate_ids('user', 'userid');
        $session_data_element->annotate_ids('webinar_session_field', 'fieldid');
        // Define file annotations
        // None for F2F
        // Return the root element (webinar), wrapped into standard activity structure
        return $this->prepare_activity_structure($webinar);
    }
}