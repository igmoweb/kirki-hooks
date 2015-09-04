wp.customize.controlConstructor['kirki-repeater'] = wp.customize.Control.extend({
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

