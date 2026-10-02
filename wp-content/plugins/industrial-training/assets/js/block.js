( function ( wp ) {
	const { registerBlockType } = wp.blocks;
	const { createElement: el } = wp.element;
	const { __ } = wp.i18n;
	const { useBlockProps } = wp.blockEditor;

	registerBlockType( 'industrial-training/landing', {
		edit() {
			return el(
				'div',
				useBlockProps( {
					style: { padding: '32px', border: '1px dashed #94a3b8', borderRadius: '12px', background: '#f8fafc', textAlign: 'center' },
				} ),
				el( 'strong', null, __( 'Industrial Training landing page', 'industrial-training' ) ),
				el( 'p', null, __( 'The full page renders on the front end. Edit text, tracks and images under Settings → Industrial Training.', 'industrial-training' ) )
			);
		},
		save: () => null,
	} );
} )( window.wp );
