<?php
// ================================================================================
// If an REDfly entity is able to be saved based on user input then it must
// implement this interface.
// ================================================================================
interface iEditable
{
    // --------------------------------------------------------------------------------
    // Roles for authenticated users
    // --------------------------------------------------------------------------------
    const ROLE_admin = "admin";
    const ROLE_curator = "curator";
    // --------------------------------------------------------------------------------
    // Entity states
    // --------------------------------------------------------------------------------
    // The entity is awaiting approval
    const STATE_approval = "approval";
    // The entity is approved and will become the current version at the release
    const STATE_approved = "approved";
    // The entity has been archived
    const STATE_archived = "archived";
    // The entity is the current version
    const STATE_current = "current";
    // The entity has been deleted
    const STATE_deleted = "deleted";
    // The entity is an edited version (this can be a new version, a modification of
    // a previous version, or a modification of an existing version)
    const STATE_editing = "editing";
    // --------------------------------------------------------------------------------
    // Actions we can take on an entity
    // --------------------------------------------------------------------------------
    // Save the entity (either a new entity or a modification to an existing one)
    const ACTION_save = "save";
    // Save the entity and move on to create a new blank entity. This is
    // equivalent to saving for the REST API, the UI handles moving on to create a
    // new one.
    const ACTION_saveNew = "save_new";
    // Save the entity and move on to create a new entity based on this one. This
    // is equivalent to saving for the REST API, the UI handles moving on to
    // create a new one.
    const ACTION_saveNewBasedOn = "save_new_based_on";
    // Mark the entity for deletion
    const ACTION_markForDeletion = "mark_for_deletion";
    // Submit the entity for administrator approval
    const ACTION_submitForApproval = "submit_for_approval";
    // Approval by the administrator
    const ACTION_approve = "approve";
    // --------------------------------------------------------------------------------
    // Save details for a single TFBS entry or approve a TFBS.  The ExtJs store
    // expects the following structure when receiving records and when saving
    // sends JSON data in $postData["results"].
    // array("success" => ["true" | "false"],
    //       "message" => <optional message>,
    //       "num"     => <number of results>,
    //       "results" => <array of result records/objects>);
    // If an entity is being approved, the baseParams for the store will also send
    // redfly_id_list which contains a JSON encoded list of the redfly ids that
    // were examined for approval.
    // @param $arguments Arguments passed in the query string
    // @param $postData Arguments passed in the POST
    // --------------------------------------------------------------------------------
    public function saveAction(array $arguments, array $postData = null);
}
