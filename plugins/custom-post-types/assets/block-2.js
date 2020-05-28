( function( blocks, element ) {
    var el = element.createElement;

    var blockStyle = {
        backgroundColor: '#00a6ff',
        color: '#fff',
        padding: '20px',
    };

    var taxs = cpt_block_2.taxs;

    var select_taxs = [];

    Object.keys(taxs).forEach(function(key) {
        select_taxs.push( el('option', {value: key }, taxs[key]) );
    });

    var used_by = cpt_block_2.used_by;

    blocks.registerBlockType( 'custom-post-types/custom-tax', {
        title: cpt_block_2.name,
        icon: {
            foreground: '#00a6ff',
            src: 'index-card',
        },
        category: 'layout',
        keywords: cpt_block_2.keywords,
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
                        cpt_block_2.select
                    ),
                    el(
                        'select',
                        { 
                            id: 'cpt-block-select-' + props.clientId,
                            onChange: updateType,
                            value: props.attributes.type,
                        },
                        el('option', {value: 'none' }, ' - '),
                        select_taxs
                    ));

            } else {

                return el(
                    'div',
                    {
                        className: 'cpt-block-notice',
                    },
                    cpt_block_2.not_used,
                );

            }

        },
        save: function(props) {
            return el(
                'span',
                { className: 'wrap_custom_taxs_' + props.attributes.type },
                '[custom-tax id="'+ props.attributes.type +'"]'
            );
        },


    } );
}(
    window.wp.blocks,
    window.wp.element,
) );