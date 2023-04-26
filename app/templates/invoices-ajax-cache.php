<script>
	$j(function(){
		var tn = 'invoices';

		/* data for selected record, or defaults if none is selected */
		var data = {
			client: { id: '<?php echo $rdata['client']; ?>', value: '<?php echo $rdata['client']; ?>', text: '<?php echo $jdata['client']; ?>' },
			client_contact: '<?php echo $jdata['client_contact']; ?>',
			client_address: '<?php echo $jdata['client_address']; ?>',
			client_phone: '<?php echo $jdata['client_phone']; ?>',
			client_email: '<?php echo $jdata['client_email']; ?>',
			client_website: '<?php echo $jdata['client_website']; ?>',
			client_comments: '<?php echo $jdata['client_comments']; ?>'
		};

		/* initialize or continue using AppGini.cache for the current table */
		AppGini.cache = AppGini.cache || {};
		AppGini.cache[tn] = AppGini.cache[tn] || AppGini.ajaxCache();
		var cache = AppGini.cache[tn];

		/* saved value for client */
		cache.addCheck(function(u, d){
			if(u != 'ajax_combo.php') return false;
			if(d.t == tn && d.f == 'client' && d.id == data.client.id)
				return { results: [ data.client ], more: false, elapsed: 0.01 };
			return false;
		});

		/* saved value for client autofills */
		cache.addCheck(function(u, d){
			if(u != tn + '_autofill.php') return false;

			for(var rnd in d) if(rnd.match(/^rnd/)) break;

			if(d.mfk == 'client' && d.id == data.client.id){
				$j('#client_contact' + d[rnd]).html(data.client_contact);
				$j('#client_address' + d[rnd]).html(data.client_address);
				$j('#client_phone' + d[rnd]).html(data.client_phone);
				$j('#client_email' + d[rnd]).html(data.client_email);
				$j('#client_website' + d[rnd]).html(data.client_website);
				$j('#client_comments' + d[rnd]).html(data.client_comments);
				return true;
			}

			return false;
		});

		cache.start();
	});
</script>

