// --------------------------------------------------------------------------------
// Create an user profile dialog by extending Ext.Window.
//  This is used for CRUD operations on a curator.
// --------------------------------------------------------------------------------
// Extend Ext.Window and set up defaults (these can be overriden by passing in
// options)
Ext.apply(Ext.form.VTypes, {
    password: function(val, field) {
        if ( field.firstPasswordField ) {
            var pwd = Ext.getCmp(field.firstPasswordField);
            return ( val === pwd.getValue() );
        }
        return true;
    },
    passwordText: 'Passwords do not match'
});
// --------------------------------------------------------------------------------
// The curator JsonStore is used to load curator names from the REST service
// --------------------------------------------------------------------------------
REDfly.store.userProfile = new Ext.data.JsonStore({
    // Ext.data.JsonStore configs
    autoLoad: false,
    autoSave: false,
    proxy: new Ext.data.HttpProxy({
        api: {
            read: {
                method: 'GET',
                url: REDfly.config.apiUrl + '/jsonstore/curator/load' 
            },
            update: {
                method: 'POST',
                url: REDfly.config.apiUrl + '/jsonstore/curator/save'
            }
        },
        listeners: {
            exception: function(proxy, type, action, options, response, arg) {
                // Handle an invalid response from the server (such as a syntax
                // error in the backend code)
                if ( type === "response" ) {
                    Ext.Msg.show({
                        buttons: Ext.Msg.OK,
                        icon: Ext.MessageBox.ERROR,
                        msg: "Status: " + response.status + "<br>\n" +
                            "Error: " + response.responseText,
                        title: 'INVALID RESPONSE FROM SERVER on ' + action
                    });
                }
            }
        }
    }),
    // Embedded Ext.data.JsonReader configs
    fields: [
        'email',
        'first_name',
        'last_name',
        { name: 'password', allowBlank: true },
        'role',
        'state',
        'user_id',
        'username'
    ],
    idProperty: 'user_id',
    root: 'results',
    successProperty: 'success',
    totalProperty: 'num',
    writer:  new Ext.data.JsonWriter({
        encode: true,
         // write all fields, not just those that changed
        writeAllFields: true
    })
});
REDfly.dialog.userProfile = Ext.extend(Ext.Window, {
    // Window configs
    border: false,
    closeAction: 'hide',
    height: 300,
    layout: 'fit',
    width: 500,
    // REDfly configs
    form: null,
    isOpen: false,
    record: null,
    store: REDfly.store.userProfile,
    userId: null,
    initComponent: function() {
        // Turn on validation errors beside the field globally
        Ext.form.Field.prototype.msgTarget = 'side';
        var formPanel = new Ext.FormPanel({
            // FormPanel configs
            buttonAlign: 'center',
            buttons: [{
            /*    formBind: true,
                handler: function() {
                    this.form.getForm().updateRecord(this.record);
                    if ( this.form.getForm().isValid() ) this.store.save();
                    if ( this.closeAction === 'hide' ) {
                        this.hide();
                    } else {
                        this.close();
                    }
                },
                scope: this,
                text: 'Save'
            },{ */
                handler: function(b, e) {
                    if ( this.closeAction === 'hide' ) {
                        this.hide();
                    } else {
                        this.close();
                    }
                },
                scope: this,
                text: 'Close'
            }],
            defaultType: 'textfield',
            frame: true,
            id: 'user_profile_form',
            items: [
                {
                    allowBlank: false,
                    disabled: true,
                    id: 'username',
                    fieldLabel: 'Username',
                    name: 'username'
                },{
                    allowBlank: false,
                    disabled: true,
                    id: 'first_name',
                    fieldLabel: 'First Name',
                    name: 'first_name'
                },{
                    allowBlank: false,
                    disabled: true,
                    id: 'last_name',
                    fieldLabel: 'Last Name',
                    name: 'last_name'
                },{
                    allowBlank: false,
                    disabled: true,
                    id: 'email',
                    fieldLabel: 'E-Mail',
                    name: 'email'
                },{
                    allowBlank: true,
                    disabled: true,
                    fieldLabel: 'Password',
                    id: 'password',
                    inputType: 'password',
                    name: 'password'
                },{
                    allowBlank: true,
                    disabled: true,
                    fieldLabel: 'Confirm Password',
                    firstPasswordField: 'password',
                    id: 'confirm_password',
                    inputType: 'password',
                    name: 'confirm_password',
                    vtype: 'password'
                },{
                    disabled: true,
                    fieldLabel: 'Active',
                    id: 'is_active',
                    name: 'is_active',
                    xtype: 'checkbox'
                },{
                    disabled: true,
                    fieldLabel: 'Role',
                    id: 'role',
                    mode: 'local',
                    name: 'role',
                    store: [
                        ['curator', 'Curator'],
                        ['admin', 'Admin']
                    ],
                    triggerAction: 'all',
                    xtype: 'combo'
                }
            ],
            labelWidth: 125,
            method: 'POST',
            monitorValid: true,
            padding: 5
        });
        // Apply any configuration to the current window
        Ext.apply(this, {
            form: formPanel,
            items: formPanel
        });
        REDfly.dialog.userProfile.superclass.initComponent.apply(this, arguments);
        // REDfly.dialog.userProfile.superclass.constructor.call(this, options);
        // Since AJAX calls are asynchronous, add a listener to the store 
        // so the data in the window will be populated on load.
        var recordLoadCb = function(store, recordList, opt) {
            // Load data into the form
            if ( recordList.length !== 1 ) return true;
            var record = recordList[0];
            record.set('is_active',
                ((record.get('state') === "active")
                    ? true
                    : false)
            );
            this.form.getForm().loadRecord(record);
            this.record = record;
            // Only any administrator can change someone's role
            if ( record.get('role') !== 'admin') {
                this.form.getForm().findField('is_active').disable();
                this.form.getForm().findField('role').disable();
            }
        };
        this.store.on(
            'load',
            recordLoadCb,
            this
        );
        // Load the store when the window is shown (if a user id is set)
        var onShowCb = function(window)
        {
            if ( window.userId ) {
                window.store.load({
                    params: { id: this.userId }
                });
            }
        };
        this.on(
            'beforeshow',
            onShowCb,
            this
        );
    }
});