<?php
/**
 * Mappa reale (Leaflet + OpenStreetMap) per Diari e Capitoli.
 *
 * Le coordinate stanno nei postmeta `_tb_lat` / `_tb_lng` dei Capitoli
 * (compilati dallo strumento "Geocodifica" del mu-plugin travelblogs-geo.php
 * o a mano nella metabox del Capitolo). Se nessun capitolo ha coordinate
 * la funzione non stampa nulla e restituisce false: il template mostra
 * allora il banner decorativo di prima.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tb_chapter_geo( $post_id ) {
	$lat = get_post_meta( $post_id, '_tb_lat', true );
	$lng = get_post_meta( $post_id, '_tb_lng', true );
	if ( '' === $lat || '' === $lng || ! is_numeric( $lat ) || ! is_numeric( $lng ) ) {
		return null;
	}
	return array( (float) $lat, (float) $lng );
}

/**
 * @param WP_Post[] $capitoli   Capitoli del viaggio, in ordine.
 * @param int       $current_id Capitolo da evidenziare (0 = nessuno).
 * @param bool      $inline     true = riquadro arrotondato più basso (pagina Diario).
 * @return bool true se la mappa è stata stampata.
 */
function tb_trip_map( $capitoli, $current_id = 0, $inline = false ) {
	static $n = 0;

	$points = array();
	foreach ( $capitoli as $i => $cap ) {
		$geo = tb_chapter_geo( $cap->ID );
		if ( ! $geo ) {
			continue;
		}
		$points[] = array(
			'n'       => $i + 1,
			'lat'     => $geo[0],
			'lng'     => $geo[1],
			'title'   => get_the_title( $cap ),
			'url'     => get_permalink( $cap ),
			'date'    => get_the_date( '', $cap ),
			'current' => ( (int) $cap->ID === (int) $current_id ),
		);
	}
	if ( empty( $points ) ) {
		return false;
	}

	$n++;
	$id = 'tb-map-' . $n;

	wp_enqueue_style( 'leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css', array(), '1.9.4' );
	wp_enqueue_script( 'leaflet', 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js', array(), '1.9.4', true );

	$js = '(function(){var pts=' . wp_json_encode( $points ) . ',el=document.getElementById(' . wp_json_encode( $id ) . ');if(!el||!window.L)return;'
		. 'var map=L.map(el,{scrollWheelZoom:false});'
		. 'L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png",{maxZoom:18,attribution:"&copy; OpenStreetMap contributors"}).addTo(map);'
		. 'var ll=[];pts.forEach(function(p){var c=[p.lat,p.lng];ll.push(c);'
		. 'var icon=L.divIcon({className:"",html:"<div class=\"tb-lpin"+(p.current?" is-current":"")+"\"><span>"+p.n+"</span></div>",iconSize:[30,30],iconAnchor:[15,30],popupAnchor:[0,-30]});'
		. 'var m=L.marker(c,{icon:icon}).addTo(map);'
		. 'var h=document.createElement("div");var a=document.createElement("a");a.href=p.url;a.textContent=p.title;a.style.fontWeight="700";'
		. 'var d=document.createElement("div");d.textContent=p.date;d.style.cssText="font-size:12px;color:#7A7D84;margin-top:3px";h.appendChild(a);h.appendChild(d);m.bindPopup(h);'
		. 'if(p.current){m.openPopup();}});'
		. 'if(ll.length>1){L.polyline(ll,{color:"#D6272E",weight:2,dashArray:"4 6",opacity:.85}).addTo(map);map.fitBounds(ll,{padding:[50,50],maxZoom:10});}'
		. 'else{map.setView(ll[0],8);}})();';
	wp_add_inline_script( 'leaflet', $js );

	echo '<div id="' . esc_attr( $id ) . '" class="tb-gmap tb-map-live' . ( $inline ? ' tb-gmap--inline' : '' ) . '" role="region" aria-label="' . esc_attr__( 'Mappa del viaggio', 'travelblogs' ) . '"></div>';
	return true;
}
