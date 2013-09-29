setInterval('refreshContent()', 120000);

function refreshContent()
{
	document.getElementById('loading').style.display = 'inline';
	
	$('#matches').load('cache/cache_current.html', function() { document.getElementById('loading').style.display = 'none'; });
	$('#elim_matches').load('cache/cache_current_elim.html', function() { document.getElementById('loading').style.display = 'none'; });
	$('#rankings').load('cache/cache_current_rankings.html', function() { document.getElementById('loading').style.display = 'none'; });

	$('#last_update').load('date.php');
}