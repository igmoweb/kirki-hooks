wp.customize.controlConstructor['repeater'] = wp.customize.Control.extend({
    ready: function() {
        var control = this,

        // The current value set in Control Class (set in Kirki_Customize_Repeater_Control::to_json() function)
            settingValue = this.params.value;

        // The hidden field that keeps the data saved (though we never update it)
        this.settingField = this.container.find('[data-customize-setting-link]').first(),

            // Set the field value for the first time, we'll fill it up later
            this.setValue([], false);

        // The DIV that holds all the rows
        this.repeaterFieldsContainer = control.container.find('.repeater-fields').first();

        // Set number of rows to 0
        this.currentIndex = 0;

        control.container.on('click', 'button.repeater-add', function (e) {
            e.preventDefault();
            control.addRow();
        });

        /**
         * Function that loads the Mustache template
         */
        this.repeaterTemplate = _.memoize(function () {
            var compiled,
            /*
             * Underscore's default ERB-style templates are incompatible with PHP
             * when asp_tags is enabled, so WordPress uses Mustache-inspired templating syntax.
             *
             * @see trac ticket #22344.
             */
                options = {
                    evaluate: /<#([\s\S]+?)#>/g,
                    interpolate: /\{\{\{([\s\S]+?)\}\}\}/g,
                    escape: /\{\{([^\}]+?)\}\}(?!\})/g,
                    variable: 'data'
                };

            return function (data) {
                compiled = _.template(control.container.find('.customize-control-repeater-content').first().html(), null, options);
                return compiled(data);
            };
        });

        // When we load the control, the fields have not been filled up
        // This is the first time that we create all the rows
        if (settingValue.length) {
            for (var i = 0; i < settingValue.length; i++) {
                control.addRow(settingValue[i]);
            }
        }

        this.container
            .on( 'keyup change', '.repeater-fields input', function( e ) {
                control.updateField.call( control, e );
            });
    },



    /**
     * Get the current value of the setting
     *
     * @return Object
     */
    getValue: function() {
        // The setting is saved in JSON
        return JSON.parse( decodeURI( this.setting.get() ) );
    },

    /**
     * Set a new value for the setting
     *
     * @param newValue Object
     * @param refresh If we want to refresh the previewer or not
     */
    setValue: function( newValue, refresh ) {
        this.setting.set( encodeURI( JSON.stringify( newValue ) ) );

        if ( refresh ) {
            // Trigger the change event on the hidden field so
            // previewer refresh the website on Customizer
            this.settingField.trigger('change');
        }
    },

    /**
     * Add a new row to repeater settings based on the structure.
     *
     * @param data (Optional) Object of field => value pairs (undefined if you want to get the default values)
     */
    addRow: function( data ) {
        var control = this,
        i,

        // The template for the new row (defined on Kirki_Customize_Repeater_Control::render_content() )
        template = control.repeaterTemplate(),

        // Get the current setting value
        settingValue = this.getValue(),

        // Saves the new setting data
        newRowSetting = {},

        // Data to pass to the template
        templateData;

        if ( template ) {

            // The control structure is going to define the new fields
            templateData = control.params.fields;

            // But if we have passed data, we'll use the data values instead
            if ( data ) {
                for ( i in data ) {
                    if ( data.hasOwnProperty( i ) && templateData.hasOwnProperty( i ) ) {
                        templateData[i].default = data[i];
                    }
                }
            }

            templateData['index'] = this.currentIndex;

            // Append the template content
            template = template( templateData );
            control.repeaterFieldsContainer.append( template );


            for ( i in templateData ) {
                if ( templateData.hasOwnProperty( i ) ) {
                    newRowSetting[ i ] = templateData[i].default;
                }
            }

            settingValue.push( newRowSetting );
            this.setValue( settingValue, true );

            this.currentIndex++;

        }
    },
    deleteRow: function() {

    },
    updateField: function( e ) {
        var element = jQuery( e.target ),
        control = this,
        currentSettings = this.getValue();

        // Gather data about the field row + ID
        var row = element.data( 'row' );
        var fieldId = element.data( 'field' );

        if ( typeof currentSettings[row][fieldId] == undefined )
            return false;

        // Update the settings
        currentSettings[row][fieldId] = element.val();
        control.setValue( currentSettings, true );
    }
});


/**wp.customize.controlConstructor['kirki-repeater'] = wp.customize.Control.extend({
    ready: function () {
        var control = this;

        var api = wp.customize;
        console.log(wp.customize.value('my-repeater[0]')({subsetting_1: "AAAA", subsetting_2: "BBB"}));

        this.container.on('keyup', 'input',
            function () {
                var input = jQuery(this);
                var row = input.data('repeater-row');
                var field = input.data('repeater-field');

                var value = control.settings[row].get();
                value[field] = input.val();
                control.settings[row].set(value);

                // Update also the params so we can add a new row easier
                control.params.value[row] = value;
            }
        );

        this.container.on('click', 'button', function (e) {
            e.preventDefault();

            control.increaseIndex();
            control.addNewSetting(control.getCurrentIndex());

            var template = wp.template(control.templateSelector);
            if (template && control.container) {
                control.container.html(template(control.params));
            }
        });
    },
    increaseIndex: function () {
        this.params.currentIndex++;
    },
    getCurrentIndex: function () {
        return this.params.currentIndex;
    },
    addNewSetting: function (index) {
        // We add first a setting to the params
        var value;
        var params = this.params;
        params.settings[index] = params.controlID + '[' + index + ']';

        // Add the new value
        params.value[index] = {};
        for (i = 0; i < params.fieldsProperties.length; i++) {
            value = params.fieldsProperties[i].default || "";
            params.value[index][params.fieldsProperties[i].id] = value;
        }
        // Add the new row
        params.rows[index] = params.fieldsProperties;
        console.log(this.params);
    }
});
 */

