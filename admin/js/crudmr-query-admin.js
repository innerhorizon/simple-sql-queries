(function(jQuery) {
    jQuery.fn.repeatable_fields = function(custom_settings) {
        var default_settings = {
            wrapper: '.wrapper',
            container: '.container',
            row: '.row',
            add: '.add',
            remove: '.remove',
            move: '.move',
            template: '.template',
            is_sortable: true,
            before_add: null,
            after_add: after_add,
            before_remove: null,
            after_remove: null,
            sortable_options: null,
            row_count_placeholder: '{{row-count-placeholder}}',
        }

        var settings = jQuery.extend({}, default_settings, custom_settings);

        // Initialize all repeatable field wrappers
        initialize(this);

        function initialize(parent) {
            jQuery(settings.wrapper, parent).each(function(index, element) {
                var wrapper = this;

                var container = jQuery(wrapper).children(settings.container);

                // Disable all form elements inside the row template
                jQuery(container).children(settings.template).hide().find(':input').each(function() {
                    jQuery(this).prop('disabled', true);
                });

                var row_count = jQuery(container).children(settings.row).filter(function() {
                    return !jQuery(this).hasClass(settings.template.replace('.', ''));
                }).length;

                jQuery(container).attr('data-rf-row-count', row_count);

                jQuery(wrapper).on('click', settings.add, function(event) {
                    event.stopImmediatePropagation();

                    var row_template = jQuery(jQuery(container).children(settings.template).clone().removeClass(settings.template.replace('.', ''))[0].outerHTML);

                    // Enable all form elements inside the row template
                    jQuery(row_template).find(':input').each(function() {
                        jQuery(this).prop('disabled', false);
                    });

                    if(typeof settings.before_add === 'function') {
                        settings.before_add(container);
                    }

                    var new_row = jQuery(row_template).show().appendTo(container);

                    if(typeof settings.after_add === 'function') {
                        settings.after_add(container, new_row, after_add);
                    }

                    // The new row might have it's own repeatable field wrappers so initialize them too
                    initialize(new_row);
                });

                jQuery(wrapper).on('click', settings.remove, function(event) {
                    event.stopImmediatePropagation();

                    var row = jQuery(this).parents(settings.row).first();

                    if(typeof settings.before_remove === 'function') {
                        settings.before_remove(container, row);
                    }

                    row.remove();

                    if(typeof settings.after_remove === 'function') {
                        settings.after_remove(container);
                    }
                });

                if(settings.is_sortable === true && typeof jQuery.ui !== 'undefined' && typeof jQuery.ui.sortable !== 'undefined') {
                    var sortable_options = settings.sortable_options !== null ? settings.sortable_options : {};

                    sortable_options.handle = settings.move;

                    jQuery(wrapper).find(settings.container).sortable(sortable_options);
                }
            });
        }

        function after_add(container, new_row) {
            var row_count = jQuery(container).attr('data-rf-row-count');

            row_count++;

            jQuery('*', new_row).each(function() {
                jQuery.each(this.attributes, function(index, element) {
                    this.value = this.value.replace(settings.row_count_placeholder, row_count - 1);
                });
            });

            jQuery(container).attr('data-rf-row-count', row_count);
        }
    }
})(jQuery);

jQuery(function() {
    jQuery('.repeat').each(function() {
        jQuery(this).repeatable_fields();
    });
});

function tableSelect(tableSelector, rowIndex) {
    if ('selection-table' == tableSelector) {
        setDisplay(tableSelector, 999, 'cm-show', 'cm-hidden');

        var tableNameIndex = document.getElementById('selection-table').selectedIndex;
        var tableName = document.getElementById('selection-table').options;

        var displayTableName = tableName[tableNameIndex].text;

        var displayBlock = document.getElementById(displayTableName);
        displayBlock.className = 'cm-show';

    } else if ('joining-table' == tableSelector) {
        setDisplay(tableSelector, rowIndex, 'cm-show', 'cm-hidden');

        var tableNameIndex = document.getElementById('joining-table[' + rowIndex + ']').selectedIndex;
        var tableName = document.getElementById('joining-table[' + rowIndex + ']').options;

        var displayTableName = tableName[tableNameIndex].text;

        var displayBlock = document.getElementById(displayTableName + '[' + rowIndex + ']');
        displayBlock.className = 'cm-show';
    }

}


function setDisplay(tableSelector, rowIndex, className, newClass) {
    if ('selection-table' == tableSelector) {
        var items = document.getElementById('selection-table-div').getElementsByTagName('select');
        for (var i=0; i < items.length; i++) {
            if (items[i].id.indexOf('selection-table') == -1) {
                items[i].className = newClass;
            }
        }
    } else if ('joining-table' == tableSelector) {
        var items = document.getElementById('joining-table-div').getElementsByTagName('select');
        for (var i=0; i < items.length; i++) {
            if ((items[i].id.indexOf('joining-table') == -1) && (items[i].id.indexOf('[' + rowIndex + ']')  != -1)) {
                items[i].className = newClass;
            }
        }
    }
}


// who knew testing to see if something was empty would be such a deal!
/* Thanks to https://www.sitepoint.com/testing-for-empty-values/ for the code! */
function empty(data)
{
    if (typeof(data) == 'number' || typeof(data) == 'boolean') {
        return false;
    }
    if (typeof(data) == 'undefined' || data === null) {
        return true;
    }
    if (typeof(data.length) != 'undefined') {
        return data.length == 0;
    }
    var count = 0;
    for (var i in data) {
        if (data.hasOwnProperty(i)) {
            count ++;
        }
    }
    return count == 0;
}