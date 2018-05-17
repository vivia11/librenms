<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 Vivia Nguyen-Tran <vivia@ualberta.ca>
 *
 * Heavily based off of new_alert_rule.inc.php
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

use LibreNMS\Authentication\Auth;
use LibreNMS\Config;

if (Auth::user()->hasGlobalAdmin()) {
?>
<!--Modal for adding or updating an alert contact -->
    <div class="modal fade" id="edit-alert-contact" tabindex="-1" role="dialog"
         aria-labelledby="Edit-contact" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="Edit-contact">Alert Contacts :: <a href="https://docs.librenms.org/Alerting/">Docs <i class="fa fa-book fa-1x"></i></a> </h5>
                </div>
                <div class="modal-body">
                    <form method="post" role="form" id="contacts" class="form-horizontal contacts-form">
                        <input type="hidden" name="contact_id" id="contact_id" value="">
                        <input type="hidden" name="type" id="type" value="alert-contacts">
                        <div class='form-group' title="The description of this alert contact.">
                            <label for='name' class='col-sm-3 col-md-2 control-label'>Contact name: </label>
                            <div class='col-sm-9 col-md-10'>
                                <input type='text' id='name' name='name' class='form-control validation' maxlength='200' required>
                            </div>
                        </div>
                        <div class="form-group" title="The type of transport for this contact.">
                            <label for='transport-choice' class='col-sm-3 col-md-2 control-label'>Transport type: </label>
                            <div class="col-sm-3">
                                <select name='transport-choice' id='transport-choice' class='form-control'>
                                    <option value="mail-form" selected>Mail</option>
                                    <option value="ciscospark-form">Cisco Spark</option>
                                    <!--Insert more transport type options here has support is added. Value should be: [transport_name]-form -->
                                </select>
                            </div>
                        </div>
                    </form>
<?php
$transports = Config::get('alert.transports');
// Dynamically create transport forms
foreach (array_keys($transports) as $transport) {
    $class = 'LibreNMS\\Alert\\Transport\\'.ucfirst($transport);
    
    if (!method_exists($class, 'configTemplate')) {
        // Skip to next transport since support has not been added
        continue;
    }
    
    echo '<form method="post" role="form" id="'.strtolower($transport).'-form" class="form-horizontal transport">';
    echo '<input type="hidden" name="transport-type" id="transport-type" value="'.strtolower($transport).'">';
   
    $tmp = call_user_func($class.'::configTemplate');

    foreach ($tmp as $item) {
        echo '<div class="form-group" title="'.$item['descr'].'">';
        echo '<label for="'.$item['name'].'" class="col-sm-3 col-md-2 control-label">'.$item['title'].': </label>';
        echo '<div class="col-sm-9 col-md-10">';
        echo '<input type="'.$item['type'].'" id="'.$item['name'].'" name="'.$item['name'].'" class="form-control" ';
        if ($item['required']) {
            echo 'required>';
        } else {
            echo '>';
        }
        echo '</div>';
        echo '</div>';
    }
    echo '</form>';
}
?>
                    <div class="col-sm-12 text-center">
                        <button type="button" class="btn btn-success" id="btn-save" name="save-contact">
                        Save Contact
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Modal end for adding or updating an alert contact-->

<!--Modal for deleting an alert contact -->
    <div class="modal fade" id="delete-alert-contact" tabindex="-1" role="dialog"
         aria-labelledby="Delete" aria-hidden="true">
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h5 class="modal-title" id="Delete">Confirm Contact Delete</h5>
                </div>
                <div class="modal-body">
                    <p>If you would like to remove this alert contact then please click Delete.</p>
                </div>
                <div class="modal-footer">
                    <form role="form" class="remove_contact_form">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger danger" id="remove-alert-contact" data-target="remove-alert-contact">Delete</button>
                        <input type="hidden" name="contact_id" id="delete_contact_id" value="">
                        <input type="hidden" name="confirm" id="confirm" value="yes">
                    </form>
                </div>
            </div>
        </div>
    </div>
<!--Modal end for deleting an alert contact -->

    <script>
        // Scripts related to editing/updating alert contact

        // Display different form on selection 
        $("#transport-choice").change(function (){
            $(".transport").hide();
            $("#" + $(this).val()).show().find("input:text").val("");
         
        });

        $("#edit-alert-contact").on("show.bs.modal", function(e) {
            // Get contact id of clicked element
            var contact_id = $(e.relatedTarget).data("contact_id");
            $("#contact_id").val(contact_id);
            if(contact_id > 0) {
                $.ajax({
                    type: "POST",
                    url: "ajax_form.php",
                    data: { type: "show-alert-contact", contact_id: contact_id },
                    success: function (data) {
                        loadContact(data); 
                    },
                    error: function () {
                        toastr.error("Failed to process alert contact");
                    }
                });
            
            } else {
            // Resetting to default
                $("#name").val("");
                $("#transport-choice").val("mail-form");
                $(".transport").hide();
                $("#" + $("#transport-choice").val()).show().find("input:text").val("");
            }
        });

        function loadContact(contact) {
            $("#name").val(contact.name);
            $("#transport-choice").val(contact.type+"-form");

            $(".transport").hide();
            $("#" + $("#transport-choice").val()).show().find("input:text").val("");
            
            // Populate the field values
            contact.details.forEach(function(config) {
                $("#" + config.name).val(config.value);
            });

        }

        // Save alert contact
        $("#btn-save").on("click", function (e) {
            e.preventDefault();

            //Combine form data (general and contact specific)
            data = $("form.contacts-form").serializeArray();
            data = data.concat($("#" + $("#transport-choice").val()).serializeArray());
            if (data !== null) {
                //post data to ajax form
                $.ajax({
                    type: "POST",
                    url: "ajax_form.php",
                    data: data,
                    dataType: "json",
                    success: function (data) {
                        if (data.status == 'ok') {
                            toastr.success(data.message);
                            setTimeout(function (){
                                $("#edit-alert-contacts").modal("hide");
                                window.location.reload();
                            }, 500);
                        } else {
                            toastr.error(data.message);
                        }
                    },
                    error: function () {
                        toastr.error("Failed to process alert contact");
                    }
                });
            }
        });

        // Scripts related to deleting an alert contact

        // Populate contact id value
        $("#delete-alert-contact").on("show.bs.modal", function(event) {
            contact_id = $(event.relatedTarget).data("contact_id");
            $("#delete_contact_id").val(contact_id);
        });

        // Delete the alert contact
        $("#remove-alert-contact").click('', function(event) {
            event.preventDefault();
            var contact_id = $("#delete_contact_id").val();
            $.ajax({
                type: "POST",
                url: "ajax_form.php",
                data: { type: "delete-alert-contact", contact_id: contact_id },
                dataType: "json",
                success: function(data) {
                    if (data.status == 'ok') {
                        toastr.success(data.message);
                        setTimeout(function () {
                            $("#delete-alert-contact").modal("hide");
                            window.location.reload();
                        }, 500)
                    } else {
                        $("#message").html("<div class='alert alert-info'>"+data.message+"</div>");
                        $("#delete-alert-contact").modal("hide");
                    }
                },
                error: function() {
                    $("#message").html("<div class='alert alert-info'>The alert contact could not be deleted.</div>");
                    $("#delete-alert-contact").modal("hide");
                }
            });
        });

    </script>

    <?php
}
