( function( blocks, element ) {
    var el = element.createElement;

    var blockStyle = {
        backgroundColor: '#00a6ff',
        color: '#fff',
        padding: '20px',
    };

    var fields = cpt_block.fields;

    var select_fields = [];

    Object.keys(fields).forEach(function(key) {
        select_fields.push( el('option', {value: key }, fields[key]) );
    });

    var used_by = cpt_block.used_by;

    blocks.registerBlockType( 'custom-post-types/custom-field', {
        title: cpt_block.name,
        icon: {
            foreground: '#00a6ff',
            src: 'index-card',
        },
        category: 'layout',
        keywords: cpt_block.keywords,
        attributes: {
            type: { type: 'string', default: 'none' },
        },


        edit: function(props) {


            function updateType( event ) {
                props.setAttributes( { type: event.target.value } );
            }

            if(used_by !== ''){

                return el(
                    'div',
                    {
                        className: 'cpt-block-select'
                    },
                    el(
                        'label',
                        {
                            for: 'cpt-block-select-' + props.clientId
                        },
                        cpt_block.select
                    ),
                    el(
                        'select',
                        { 
                            id: 'cpt-block-select-' + props.clientId,
                            onChange: updateType,
                            value: props.attributes.type,
                        },
                        el('option', {value: 'none' }, ' - '),
                        select_fields
                    ));

            } else {

                return el(
                    'div',
                    {
                        className: 'cpt-block-notice',
                    },
                    cpt_block.not_used,
                );

            }

        },
        save: function(props) {
            return el(
                'span',
                { className: 'wrap_custom_field_' + props.attributes.type },
                '[custom-field id="'+ props.attributes.type +'"]'
            );
        },


    } );
}(
    window.wp.blocks,
    window.wp.element,
) );