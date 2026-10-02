( () => {
	const i18n = window.itpAdmin || {};
	const list = document.querySelector( '[data-itp-tracks]' );
	const tpl = document.getElementById( 'itp-track-template' );

	document.addEventListener( 'click', ( e ) => {
		const btn = e.target.closest( 'button' );
		if ( ! btn ) return;

		// Media picker.
		const box = btn.closest( '[data-itp-image]' );
		if ( box && btn.hasAttribute( 'data-itp-choose' ) ) {
			const frame = wp.media( { title: i18n.choose, button: { text: i18n.use }, library: { type: 'image' }, multiple: false } );
			frame.on( 'select', () => {
				const a = frame.state().get( 'selection' ).first().toJSON();
				const src = ( a.sizes && ( a.sizes.thumbnail || a.sizes.medium ) || a ).url;
				box.querySelector( 'input' ).value = a.id;
				const img = document.createElement( 'img' );
				img.src = src;
				img.alt = a.alt || '';
				box.querySelector( '.itp-image-preview' ).replaceChildren( img );
			} );
			frame.open();
			return;
		}
		if ( box && btn.hasAttribute( 'data-itp-clear' ) ) {
			box.querySelector( 'input' ).value = '';
			const em = document.createElement( 'em' );
			em.textContent = '—';
			box.querySelector( '.itp-image-preview' ).replaceChildren( em );
			return;
		}

		// Tracks repeater.
		const row = btn.closest( '[data-itp-row]' );
		if ( btn.hasAttribute( 'data-itp-add' ) && list && tpl ) {
			const html = tpl.innerHTML.replaceAll( '__i__', 'n' + Date.now() );
			list.insertAdjacentHTML( 'beforeend', html );
			list.lastElementChild.querySelector( 'input[type="text"]' )?.focus();
		} else if ( row && btn.hasAttribute( 'data-itp-remove' ) ) {
			if ( window.confirm( i18n.remove ) ) row.remove();
		} else if ( row && btn.hasAttribute( 'data-itp-up' ) && row.previousElementSibling ) {
			row.previousElementSibling.before( row );
			btn.focus();
		} else if ( row && btn.hasAttribute( 'data-itp-down' ) && row.nextElementSibling ) {
			row.nextElementSibling.after( row );
			btn.focus();
		}
	} );

	// Keep the row heading in sync with the track name.
	list?.addEventListener( 'input', ( e ) => {
		if ( e.target.name && e.target.name.endsWith( '[name]' ) ) {
			e.target.closest( '[data-itp-row]' ).querySelector( '.itp-track-title' ).textContent = e.target.value;
		}
	} );
} )();
